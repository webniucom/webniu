<?php

namespace plugin\webniu\app\web\controller;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use plugin\webniu\app\common\Util;
use process\Monitor;
use support\exception\BusinessException;
use support\Log;
use support\Request;
use support\Response;
use plugin\webniu\app\model\Plugin;
use support\Redis;
use ZIPARCHIVE;
use function array_diff;
use function ini_get;
use function scandir;
use const DIRECTORY_SEPARATOR;
use const PATH_SEPARATOR;

class PluginController extends Crud
{
    /**
     * 不需要鉴权的方法
     * @var string[]
     */
    protected $noNeedAuth = ['schema', 'captcha'];

    /**
     * @var User
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new Plugin;
    }

    /**
     * @param Request $request
     * @return string
     * @throws GuzzleException
     */
    public function index(Request $request)
    {
        
        return raw_view('plugin/index');
    }

    /**
     * 查询
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function select(Request $request): Response
    {
        $data       = [];
        $installedtype = $request->get('installedtype','installed');
        [$where, $format, $limit, $field, $order] = $this->selectInput($request); 
        $query      = $this->doSelect($where, $field, $order);
        $paginator  = $query->paginate($limit);
        $items      = $paginator->items();
        switch ($installedtype) {
            case 'localapps': 
                $localitems         = [];
                $plugin_names       = array_diff(scandir(base_path() . '/plugin/'), array('.', '..'));
                $existing_plugins   = array_column($items, 'plugin_identifier');
                foreach ($plugin_names as $plugin_name) {
                    if (!in_array($plugin_name, $existing_plugins)) {
                        $plugin_info    = $this->getPluginApp($plugin_name);
                        $plugin_info['installed']       = false;
                        $plugin_info['installedtype']   = 'localapps';
                        $localitems[]   = $plugin_info;
                    }
                }
                $total  = count($localitems);
                $data   = $localitems;
                break;
            case 'waitapps':
                $total  = 0;
                $data   = []; 
                break;
            case 'updateset': 
                $total              = $paginator->total();
                $data['items']      = $items[0]; 
                $data['rewrite']    = $this->getPluginRewrite($items[0]['plugin_identifier']); 
                break;
            default:
                
                $total  = $paginator->total();
                $data   = $items;
            break;
        }
        return call_user_func([$this, 'formatNormal'], $data, $total);
    }
 

    /**
     * 更新安装应用
     * @param Request $request
     * @return Response
     * @throws BusinessException|Throwable
     */
    public function update(Request $request): Response
    {
        if ($request->method() === 'POST') {
            $post       = $request->post();
            if (!empty($post['laytab']) && $post['laytab'] == 'route') {
                $value  = $request->post('value',false); 
                $name   = $post['plugin_identifier'];
                if($value && isset($name)){
                    $content= '';
                    foreach($value as $k=>$v){
                        $v_value    = $v['value'];
                        $v_name     = $v['name'];
                        $v_action   = $v['action'];
                        $content    = $content.PHP_EOL.<<<EOF
                        '$v_value'   => [
                            $v_name::class,
                            '$v_action'
                        ],
                        EOF;
                    };
                }else{
                    $content    = '';
                };
$config_content = <<<EOF
<?php
return [
    $content
];
EOF; 
                    $labelpath  = base_path().'/plugin/'.$name.'/app/support/'; 
                    if(!is_dir($labelpath)) {
                        if (!is_dir($labelpath)) {
                            mkdir($labelpath, 0777, true); 
                        } 
                    } 
                    Util::pauseFileMonitor();
                    file_put_contents($labelpath.'rewrite.php',$config_content);
                    Util::resumeFileMonitor();
                    Util::reloadWebman();
            }
            return parent::update($request);
        }
        return raw_view('plugin/update');
    }

    /**
     * 查询类型分类
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function typeclass(Request $request): Response
    {
        $redis  = Redis::connection('plugin.webniu.default');
        $key    = 'typeclass';
        $data   = $redis->get($key);
        if ($data) {
            return $this->json(0, 'ok', json_decode($data, true));
        };
        $client = $this->httpClient();
        $query  = $request->get();
        $query['version'] = $this->getAdminVersion();
        $response   = $client->get('/app/webniu/api/typeclass', ['query'=>$query]);
        $content    = $response->getBody()->getContents();
        $data       = json_decode($content, true);
        $redis->set($key,json_encode($data['data']??[]),60*60*24*7);
        return  $this->json(0,'ok',$data['data']??[]);
    }

    /**
     * 检测更新版本
     * @param Request $request
     * @return Response
     * @throws GuzzleException
     */
    public function version(Request $request): Response
    { 
        $version   = $request->post('version',null); 
        $data = []; 
        if(empty($version)){
            return json(['code' => 0, 'msg' => 'ok', 'data' => []]);
        }
        $names  = array_column($version, 'name');
        $name   = implode(',', $names);
        $client     = $this->httpClient();
        $response   = $client->post('/app/webniu/api/listapp', ['form_params'=>$version]);
        $content    = $response->getBody()->getContents();
        $content    = json_decode($content, true);

        foreach ($version as $item) {
            if(is_array($content['data']) && array_key_exists($item['name'], $content['data'])){
                $data[$item['name']] = $content['data'][$item['name']];  
            }
            $installed = $this->getPluginApp($item['name']);
            if (version_compare($installed['version'],$item['version'], '>')) {
                $data[$item['name']] = $installed;
            }
        }
        return json(['code' => 0, 'msg' => 'ok', 'data' => $data]);
    }

    /**
     * 安装更新
     * @param Request $request
     * @return Response
     * @throws GuzzleException|BusinessException
     */
    public function install(Request $request): Response
    {
        $post       = $request->post();
          
        $name       = $post['plugin_identifier'];
        $version    = $post['version'];
        $installed  = $post['installedtype']??false;
        $installed_app      = $this->getPluginVersion($name); 
        $installed_version  = $installed_app['version']??null; 
          
        if (!$name || !$version || !$installed) {
            return $this->json(1, '缺少参数');
        }
        
        if($installed == 'waitapps'){
            
            $base_path = base_path() . "/plugin/$name";
            $zip_file = "$base_path.zip";
            $extract_to = base_path() . '/plugin/';
            $this->downloadZipFile(base64_decode($post['tokens']), $zip_file);

            $has_zip_archive = class_exists(ZipArchive::class, false);
            if (!$has_zip_archive) {
                $cmd = $this->getUnzipCmd($zip_file, $extract_to);
                if (!$cmd) {
                    throw new BusinessException('请给php安装zip模块或者给系统安装unzip命令');
                }
                if (!function_exists('proc_open')) {
                    throw new BusinessException('请解除proc_open函数的禁用或者给php安装zip模块');
                }
            }
        }
         
        Util::pauseFileMonitor();
        try {
            // 解压zip到plugin目录
            if($installed == 'waitapps'){
                if ($has_zip_archive) {
                    $zip = new ZipArchive;
                    $zip->open($zip_file);
                }
    
                if (!empty($zip)) {
                    $zip->extractTo(base_path() . '/plugin/');
                    unset($zip);
                } else {
                    $this->unzipWithCmd($cmd);
                }
                unlink($zip_file);
            }
            
            $context = null;
            $install_class = "\\plugin\\$name\\api\\Install";
            if ($installed_version) {
                // 执行beforeUpdate
                if (class_exists($install_class) && method_exists($install_class, 'beforeUpdate')) {
                    $context = call_user_func([$install_class, 'beforeUpdate'], $installed_version, $version);
                }
            }
 
            if ($installed_version) {
                // 执行update更新
                if (class_exists($install_class) && method_exists($install_class, 'update')) {
                    call_user_func([$install_class, 'update'], $installed_version, $version, $context);
                }
            } else {
                // 执行install安装
                if (class_exists($install_class) && method_exists($install_class, 'install')) {
                    call_user_func([$install_class, 'install'], $version);
                }
            }
            $this->updateOrInsert($post); 

        } finally {
            Util::resumeFileMonitor();
        }

        Util::reloadWebman();

        return $this->json(0);
    }
    
    /**
     * 卸载
     * @param Request $request
     * @return Response
     */
    public function uninstall(Request $request): Response
    {
        $id     = $request->post('id');
        $Plugin = Plugin::where('id',$id)->first();
        if(!$Plugin){
            return $this->json(1, '插件不存在');
        }
        
        $name       = $Plugin->plugin_identifier;
        $version    = $Plugin->version;
        if (!$name || !preg_match('/^[a-zA-Z0-9_]+$/', $name) || $name == 'webniu') {
            return $this->json(1, '参数错误，卸载失败!');
        }
        // 获得插件路径
        clearstatcache();
        $path = get_realpath(base_path() . "/plugin/$name");
        if (!$path || !is_dir($path)) {
            return $this->json(1, '已经删除');
        }

        // 执行uninstall卸载
        $install_class = "\\plugin\\$name\\api\\Install";
        if (class_exists($install_class) && method_exists($install_class, 'uninstall')) {
            call_user_func([$install_class, 'uninstall'], $version);
        }

        // 删除目录
        clearstatcache();
        if (is_dir($path)) {
            $monitor_support_pause = method_exists(Monitor::class, 'pause');
            if ($monitor_support_pause) {
                Monitor::pause();
            }
            try {
                $this->rmDir($path);
            } finally {
                if ($monitor_support_pause) {
                    Monitor::resume();
                }
            }
            $Plugin->delete();
        }
        clearstatcache();

        Util::reloadWebman();

        return $this->json(0);
    }

    /**
     * 更新或插入插件信息
     * @param $post
     * @return string|Response
     * @throws GuzzleException
     */
    protected function updateOrInsert($post)
    {
        return Plugin::updateOrInsert(
            [
                'plugin_identifier'=> $post['plugin_identifier']
            ],
            [
                'plugin_type'   => $post['plugin_type']??'webniu',
                'plugin_class'  => $post['plugin_class']??'1',
                'plugin_name'   => $post['plugin_name']??'未定义名称',
                'plugin_desc'   => $post['plugin_desc']??'未定义描述',
                'plugin_author' => $post['plugin_author']??'未定义作者',
                'plugin_logo'   => $post['plugin_logo']??'',
                'plugin_icon'   => $post['plugin_icon']??'',
                'plugin_href'   => $post['plugin_href']??'',
                'plugin_open'   => $post['plugin_open']??'0',
                'version'       => $post['version']??'0.0.0',
                'installed'     => 1,
                'releases'      => $post['version']??'0.0.0', 
            ]
        ); 
    }

   

    /**
     * 登录验证码
     * @param Request $request
     * @return Response
     * @throws GuzzleException
     */
    public function captcha(Request $request): Response
    {
        $client = $this->httpClient();
        $response = $client->get('/user/captcha?type=login');
        $sid_str = $response->getHeaderLine('Set-Cookie');
        if (preg_match('/PHPSID=([a-zA-Z_0-9]+?);/', $sid_str, $match)) {
            $sid = $match[1];
            session()->set('app-plugin-token', $sid);
        }
        return response($response->getBody()->getContents())->withHeader('Content-Type', 'image/jpeg');
    }

    /**
     * 登录官网
     * @param Request $request
     * @return Response|string
     * @throws GuzzleException
     */
    public function login(Request $request)
    {
        $client = $this->httpClient();
        if ($request->method() === 'GET') {
            $response = $client->get("/webman-admin/login");
            return (string)$response->getBody();
        }

        $response = $client->post('/api/user/login', [
            'form_params' => [
                'email' => $request->post('username'),
                'password' => $request->post('password'),
                'captcha' => $request->post('captcha')
            ]
        ]);
        $content = $response->getBody()->getContents();
        $data = json_decode($content, true);
        if (!$data) {
            $msg = "/api/user/login return $content";
            echo "msg\r\n";
            Log::error($msg);
            return $this->json(1, '发生错误');
        }
        if ($data['code'] != 0) {
            return $this->json($data['code'], $data['msg']);
        }
        session()->set('app-plugin-user', [
            'uid' => $data['data']['uid']
        ]);
        return $this->json(0);
    }

    /**
     * 获取zip下载url
     * @param $name
     * @param $version
     * @return mixed
     * @throws BusinessException
     * @throws GuzzleException
     */
    protected function getDownloadUrl($name, $version)
    {
        $client = $this->httpClient();
        $response = $client->get("/app/download/$name?version=$version");

        $content = $response->getBody()->getContents();
        $data = json_decode($content, true);
        if (!$data) {
            $msg = "/api/app/download return $content";
            Log::error($msg);
            throw new BusinessException('访问官方接口失败 ' . $response->getStatusCode() . ' ' . $response->getReasonPhrase());
        }
        if ($data['code'] && $data['code'] != -1 && $data['code'] != -2) {
            throw new BusinessException($data['msg']);
        }
        if ($data['code'] == 0 && !isset($data['data']['url'])) {
            throw new BusinessException('官方接口返回数据错误');
        }
        return $data;
    }

    /**
     * 下载zip
     * @param $url
     * @param $file
     * @return void
     * @throws BusinessException
     * @throws GuzzleException
     */
    protected function downloadZipFile($url, $file)
    {
        $client = $this->downloadClient();
        $response = $client->get($url);
        $body = $response->getBody();
        $status = $response->getStatusCode();
        if ($status == 404) {
            throw new BusinessException('安装包不存在');
        }
        $zip_content = $body->getContents();
        if (empty($zip_content)) {
            throw new BusinessException('安装包不存在');
        }
        file_put_contents($file, $zip_content);
    }

    /**
     * 获取系统支持的解压命令
     * @param $zip_file
     * @param $extract_to
     * @return mixed|string|null
     */
    protected function getUnzipCmd($zip_file, $extract_to)
    {
        if ($cmd = $this->findCmd('unzip')) {
            $cmd = "$cmd -o -qq $zip_file -d $extract_to";
        } else if ($cmd = $this->findCmd('7z')) {
            $cmd = "$cmd x -bb0 -y $zip_file -o$extract_to";
        } else if ($cmd = $this->findCmd('7zz')) {
            $cmd = "$cmd x -bb0 -y $zip_file -o$extract_to";
        }
        return $cmd;
    }

    /**
     * 使用解压命令解压
     * @param $cmd
     * @return void
     * @throws BusinessException
     */
    protected function unzipWithCmd($cmd)
    {
        $desc = [
            0 => ["pipe", "r"],
            1 => ["pipe", "w"],
            2 => ["pipe", "w"],
        ];
        $handler = proc_open($cmd, $desc, $pipes);
        if (!is_resource($handler)) {
            throw new BusinessException("解压zip时出错:proc_open调用失败");
        }
        $err = fread($pipes[2], 1024);
        fclose($pipes[2]);
        proc_close($handler);
        if ($err) {
            throw new BusinessException("解压zip时出错:$err");
        }
    }

    /**
     * 获取已安装的插件列表
     * @return array
     */
    protected function getLocalPlugins(): array
    {
        clearstatcache();
        $installed = [];
        $plugin_names = array_diff(scandir(base_path() . '/plugin/'), array('.', '..')) ?: [];
        foreach ($plugin_names as $plugin_name) {
            if (is_dir(base_path() . "/plugin/$plugin_name") && $version = $this->getPluginVersion($plugin_name)['version']) {
                $installed[$plugin_name] = $version;
            }
        }
        return $installed;
    }

    /**
     * 获取已安装的插件列表
     * @param Request $request
     * @return Response
     */
    public function getInstalledPlugins(Request $request): Response
    {
        return $this->json(0, 'ok', $this->getLocalPlugins());
    }
    

    /**
     * 获取已安装插件版本
     * @param $name
     * @return array|mixed|null
     */
    protected function getPluginVersion($name)
    {
        $plugin = Plugin::where('plugin_identifier', $name)->first();
        if (!$plugin) {
            return null;
        }
        $config = $plugin->toArray();
        return $config ?? null;
    }

    /**
     * 获取本地插件信息
     * @param $name
     * @return array|mixed|null
     */
    protected function getPluginApp($name)
    {
        if (!is_file($file = base_path() . "/plugin/$name/config/app.php")) {
            return null;
        }
        $config = include $file;
        return $config ?? null;
    }

    /**
     * 获取本地插件信息
     * @param $name
     * @return array|mixed|null
     */
    protected function getPluginRewrite($name)
    {
        $data = [];
        if(is_file($file = base_path() . "/plugin/$name/app/support/rewrite.php")) {
            $config = include $file;
            if(is_array($config)){ 
                foreach($config as $k=>$v){
                    $data[] = [
                        'value' => $k,
                        'name'  => $v[0],
                        'action'=> $v[1]
                    ];
                }
            }
        }
        return $data ?? null;
    }

    /**
     * 获取webniu版本
     * @return string
     */
    protected function getAdminVersion(): string
    {
        return config('plugin.webniu.app.version', '');
    }

    /**
     * 删除目录
     * @param $src
     * @return void
     */
    protected function rmDir($src)
    {
        $dir = opendir($src);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                $full = $src . '/' . $file;
                if (is_dir($full)) {
                    $this->rmDir($full);
                } else {
                    unlink($full);
                }
            }
        }
        closedir($dir);
        rmdir($src);
    }

    /**
     * 获取httpclient
     * @return Client
     */
    protected function httpClient(): Client
    {
        // 下载zip
        $options = [
            'base_uri' => config('plugin.webniu.app.plugin_market_host'),
            'timeout' => 60,
            'connect_timeout' => 5,
            'verify' => false,
            'http_errors' => false,
            'headers' => [
                'Referer' => \request()->fullUrl(),
                'User-Agent' => 'webniu-app-plugin',
                'Accept' => 'application/json;charset=UTF-8',
            ]
        ];
        if ($token = session('app-plugin-token')) {
            $options['headers']['Cookie'] = "PHPSID=$token;";
        }
        return new Client($options);
    }

    /**
     * 获取下载httpclient
     * @return Client
     */
    protected function downloadClient(): Client
    {
        // 下载zip
        $options = [
            'timeout' => 59,
            'connect_timeout' => 5,
            'verify' => false,
            'http_errors' => false,
            'headers' => [
                'Referer' => \request()->fullUrl(),
                'User-Agent' => 'webman-app-plugin',
            ]
        ];
        if ($token = session('app-plugin-token')) {
            $options['headers']['Cookie'] = "PHPSID=$token;";
        }
        return new Client($options);
    }

    /**
     * 查找系统命令
     * @param string $name
     * @param string|null $default
     * @param array $extraDirs
     * @return mixed|string|null
     */
    protected function findCmd(string $name, ?string $default = null, array $extraDirs = [])
    {
        if (ini_get('open_basedir')) {
            $searchPath = array_merge(explode(PATH_SEPARATOR, ini_get('open_basedir')), $extraDirs);
            $dirs = [];
            foreach ($searchPath as $path) {
                if (@is_dir($path)) {
                    $dirs[] = $path;
                } else {
                    if (basename($path) == $name && @is_executable($path)) {
                        return $path;
                    }
                }
            }
        } else {
            $dirs = array_merge(
                explode(PATH_SEPARATOR, getenv('PATH') ?: getenv('Path')),
                $extraDirs
            );
        }

        $suffixes = [''];
        if ('\\' === DIRECTORY_SEPARATOR) {
            $pathExt = getenv('PATHEXT');
            $suffixes = array_merge($pathExt ? explode(PATH_SEPARATOR, $pathExt) : ['.exe', '.bat', '.cmd', '.com'], $suffixes);
        }
        foreach ($suffixes as $suffix) {
            foreach ($dirs as $dir) {
                if (@is_file($file = $dir . DIRECTORY_SEPARATOR . $name . $suffix) && ('\\' === DIRECTORY_SEPARATOR || @is_executable($file))) {
                    return $file;
                }
            }
        }

        return $default;
    }

}

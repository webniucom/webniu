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
use support\think\Cache;
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
     * 已安装查询
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function select(Request $request): Response
    {
        [$where, $format, $limit, $field, $order] = $this->selectInput($request);
        $query = $this->doSelect($where, $field, $order);
        return $this->doFormat($query, $format, $limit);
    }

    /**
     * 查询数据库后置方法，可用于修改数据
     * @param mixed $items 原数据
     * @return mixed 修改后数据
     */
    protected function afterQuery($items)
    {
        if(!empty($items)){
            $id = request()->get('id',false);
            if($id){
                foreach($items as $k=>$v){
                    $items[$k]['rewrite']    = $this->getPluginRewrite($v['plugin_identifier']);
                }
            }
        }
        return $items;
    }

    /**
     * 待安装查询
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function waitapps(Request $request): Response
    {
        $total  = 0;
        $data   = [];
        $code   = 0;
        $msg    = 'ok';
        try{
            $client = Util::httpClient();
            $query  = $request->get();
            $query['version']   = $this->getAdminVersion();
            $response   = $client->post('/api/v1/applist', ['form_params'=>$query]);
            $content    = $response->getBody()->getContents();
            $content    = json_decode($content, true); 
            if($content['code'] == 0){
                $total  = $content['count']??0;
                $data   = $content['data']??[];
                if(!empty($data)){
                    $plugin_identifier  = array_column($data, 'plugin_identifier');
                    $list   = $this->model->whereIn('plugin_identifier',$plugin_identifier)->select('plugin_identifier')->get();
                    if($list){  
                        $list   = $list->toArray();
                        $list   = array_column($list,'plugin_identifier');
                        foreach($data as $k=>$v){
                            if(in_array($v['plugin_identifier'],$list)){
                                unset($data[$k]);
                            }
                        }
                        $total  = count($data);
                    } 
                }
            }else{
                $code   = $content['code'];
                $msg    = $content['msg'];
            }
        }catch(\Throwable $e){
            return json(['code' => 1, 'msg' => $e->getMessage(), 'count' => 0, 'data' => []]);
        }
        return json(['code' => $code, 'msg' => $msg, 'count' => $total, 'data' => $data]);
    }

    /**
     * 本地版查询
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function localapps(Request $request): Response
    {
        $total  = 0;
        $data   = [];
        try{
            $localitems         = [];
            $plugin_names       = array_diff(scandir(base_path() . '/plugin/'), array('.', '..'));
            $existing_plugins   = array_column($this->model->select('plugin_identifier')->get()->toArray(), 'plugin_identifier');
            foreach ($plugin_names as $plugin_name) {
                if (!in_array($plugin_name, $existing_plugins)) {
                    $plugin_info    = $this->getPluginApp($plugin_name);
                    $plugin_info['installed']       = false;
                    $plugin_info['installedtype']   = 'localapps';
                    if(!empty($plugin_info['plugin_identifier'])){
                        $localitems[]   = $plugin_info;
                    }
                        
                }
            }
            $total  = count($localitems);
            $data   = $localitems;
        }catch(\Throwable $e){$total  = 0;$data   = [];}
        return json(['code' => 0, 'msg' => 'ok', 'count' => $total, 'data' => $data]);
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
        $key    = 'typeclass';
        $redis  = true;
        try {
            $data   = Cache::get($key);
        } catch (\Throwable $e) {
            $redis = false;
            $data  = false;
        }
        if ($data) {
            return $this->json(0, 'ok', json_decode($data, true));
        };
        $client             = Util::httpClient();
        $query              = $request->get();
        $query['version']   = $this->getAdminVersion();
        $response   = $client->post('/api/v1/category', ['form_params'=>$query]);
        $content    = $response->getBody()->getContents();
        $data       = json_decode($content, true); 
        if($redis){
            Cache::set($key,json_encode($data['data']??[]),60*60*24*7);
        }
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
        try{
            $names      = array_column($version, 'name');
            $name       = implode(',', $names);
            $client     = Util::httpClient();
            $form_params['plugin']  = $version;
            $response   = $client->post('/api/v1/checkupdate', ['form_params'=>$form_params]);
            $content    = $response->getBody()->getContents();
            $content    = json_decode($content, true); 
            if($content['code'] == 0){
                foreach ($version as $item) {
                    if(isset($content['data']) && is_array($content['data']) && array_key_exists($item['name'], $content['data'])){
                        if (version_compare($content['data'][$item['name']]['version'],$item['version'], '>')) {
                            $data[$item['name']] = $content['data'][$item['name']];continue;
                        }
                    }
                    $installed = $this->getPluginApp($item['name']);
                    if (version_compare($installed['version'],$item['version'], '>')) {
                        $data[$item['name']] = $installed;
                    }
                }
            }
        }catch(\Throwable $e){$data   = [];}
        return json(['code' => 0, 'msg' => 'ok', 'data' => $data]);
    }

    public function system(Request $request): Response
    { 
        return json(['code' => 0, 'msg' => 'ok', 'data' => []]);
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
        if($installed_app==null){$installed_app = $post;}
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
                    $context = call_user_func([$install_class, 'beforeUpdate'], $installed_version, $installed_app);
                }
            }
            if ($installed_version) {
                // 执行update更新
                if (class_exists($install_class) && method_exists($install_class, 'update')) {
                    call_user_func([$install_class, 'update'], $installed_version, $installed_app, $context);
                }
            } else {
                // 执行install安装
                if (class_exists($install_class) && method_exists($install_class, 'install')) { 
                    call_user_func([$install_class, 'install'], $installed_app);
                }
            }
            if($installed == 'waitapps'){
                unlink(base_path() . "/plugin/{$name}/public/config/install.php");
                unlink(base_path() . "/plugin/{$name}/public/config/update.php"); 
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
                //卸载不删除模块
                //$this->rmDir($path);
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
        $data = $this->inputFilter($post);
        $data['installed'] = 1;
        return Plugin::updateOrInsert(
            ['plugin_identifier'=> $data['plugin_identifier']
            ],$data
        ); 
    }
    
    /**
     * 网牛验证码
     * @param Request $request
     * @return Response
     * @throws GuzzleException
     */
    public function captcha(Request $request): Response
    {
        $client     = Util::httpClient();
        $response   = $client->get('/api/v1/captcha?type=login');
        $sid_str    = $response->getHeaderLine('Set-Cookie');
        if (preg_match('/PHPSID=([a-zA-Z_0-9]+?);/', $sid_str, $match)) {
            $sid = $match[1];
            session()->set('webniu-plugin-token', $sid);
        }
        return response($response->getBody()->getContents())->withHeader('Content-Type', 'image/jpeg');
    }

    /**
     * 登录网牛
     * @param Request $request
     * @return Response|string
     * @throws GuzzleException
     */
    public function login(Request $request)
    {
        $token = session()->get('webniu-plugin-token');
        if(!$token){
            return $this->json(1,'请先获取验证码');
        }
        try {
            $client     = Util::httpClient();
            $response   = $client->post('/api/v1/user/login', [
                'form_params' => [
                    'username' => $request->post('username'),
                    'password' => $request->post('password'),
                    'captcha'  => $request->post('captcha'),
                ]
            ]);
            $content = $response->getBody()->getContents();
            $data = json_decode($content, true);
            if($data['code'] == 2){
                return $this->json($data['code'], $data['msg'],[]);
            }
            if($data['code'] == 0){
                session()->set('webniu-plugin-user', $data['data']);
            }
            return $this->json($data['code'], $data['msg'], $data['data']);
        } catch (\Exception $e) {}
        return $this->json(1,'登录异常,请重试!',[]);
    }

    /**
     * 注册网牛
     * @param Request $request
     * @return Response|string
     * @throws GuzzleException
     */
    public function reg(Request $request)
    {
        $token = session()->get('webniu-plugin-token');
        if(!$token){
            return $this->json(1,'请先获取验证码');
        }
        try {
            $client     = Util::httpClient();
            $response   = $client->post('/api/v1/user/reg', [
                'form_params' => [
                    'username' => $request->post('username'),
                    'password' => $request->post('password'),
                    'confirmpassword'=> $request->post('confirmPassword'),
                    'nickname' => $request->post('nickname'),
                    'captcha'  => $request->post('captcha'),
                ]
            ]);
            $content = $response->getBody()->getContents();
            $data = json_decode($content, true);
            if($data['code'] == 2){
                return $this->json($data['code'], $data['msg'],[]);
            }
            return $this->json($data['code'], $data['msg'], $data['data']);
        } catch (\Exception $e) {}
        return $this->json(1,'注册异常,请官网注册!',[]);
    }

    /**
     * 注销登录
     * @param Request $request
     * @return Response|string
     * @throws GuzzleException
     */
    public function out(Request $request)
    {
        try {
            $client     = Util::httpClient();
            $response   = $client->post('/api/v1/user/logout', [
                'form_params' => [
                    'action' => 'logout',
                ]
            ]);
            $content = $response->getBody()->getContents();
            $data = json_decode($content, true);
            if($data['code'] == 2){
                return $this->json($data['code'], $data['msg'],[]);
            }
            if($data['code'] == 0){
                $request->session()->delete('webniu-plugin-user'); 
                return $this->json($data['code'], $data['msg'], []);
            }
             
        } catch (\Exception $e) {}
        return $this->json(1,'退出异常!',[]);
    }

    /**
     * 会员状态
     * @param Request $request
     * @return Response|string
     * @throws GuzzleException
     */
    public function wnyun(Request $request)
    {
        try {
            $client     = Util::httpClient();
            $response   = $client->post('/api/v1/user/account');
            $content    = $response->getBody()->getContents();
            $data       = json_decode($content, true);
            if($data['code'] == 102){
                $request->session()->delete('webniu-plugin-user'); 
                return $this->json($data['code'], $data['msg'], []);
            }
            return $this->json($data['code'], $data['msg'],$data['data']);
             
        } catch (\Exception $e) {
            return $this->json(1,$e->getMessage(),[]);
        }
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
        $client = Util::httpClient();
        $response = $client->get($url);
        $body = $response->getBody();
        $status = $response->getStatusCode();
        if ($status == 404) {
            throw new BusinessException('安装包不存在');
        }
        $zip_content = $body->getContents();
        if ($status == 503) {
            throw new BusinessException($zip_content);
        }
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

<?php

namespace plugin\webniu\app\admin\controller;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use plugin\webniu\app\common\Util;
use app\process\Monitor;
use support\exception\BusinessException;
use support\Request;
use support\Response;
use plugin\webniu\app\model\Plugin;
use plugin\webniu\app\model\Rule;
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
    protected $noNeedAuth = ['schema', 'captcha', 'licenses', 'unbindlicense'];

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
     * 插件配置
     * @return Response
     */
    public function config(Request $request)
    {
        $plugin = [];
        $confset    = confset_get('plugin', 'apptem');
        $globalplugin = options('globalplugin');
        //判断是否空数组
        if (empty($confset)) {
            $client             = Util::httpClient();
            $query              = $request->get();
            $query['version']   = $this->getAdminVersion();
            $response   = $client->post('/api/v1/plugin', ['form_params' => $query]);
            $content    = $response->getBody()->getContents();
            $data       = json_decode($content, true);
            if ($data['code'] == 200) {
                confset_set(['model' => 'apptem', 'name' => 'plugin', 'label' => '插件模板',], ['name' => 'plugin', 'label' => '插件类型', 'type' => 'select', 'key' => 'type', 'disabled' => 1, 'options' => json_encode($data['data']['type']), 'status' => 1,]);
                confset_set(['model' => 'apptem', 'name' => 'plugin', 'label' => '插件模板',], ['name' => 'plugin', 'label' => '插件分类', 'type' => 'select', 'key' => 'class', 'disabled' => 1, 'options' => json_encode($data['data']['class']), 'status' => 1,]);
                foreach ($data['data'] as $key => $val) {
                    $plugin[$key] = $val;
                }
            } else {
                $plugin = [];
            }
        } else {
            foreach ($confset as $key => $val) {
                $plugin[$val['key']] = json_decode($val['options'], true);
            }
        }
        return $this->json(200, '加载成功', [
            'plugin' => $plugin ?? [],
            'config' => [
                'title' => '插件',
                'layout' => '',
            ],
            ...$globalplugin,
        ]);
    }

    /**
     * 全局配置
     * @return Response
     */
    public function setconfig(Request $request): Response
    {
        $name = $request->input('label', false);
        $value = $request->input('value', false);
        if (empty($name) || empty($value)) {
            return $this->json(400, '参数错误');
        }
        if (!setoptions($name, $value)) {
            return $this->json(400, '保存失败');
        }
        return $this->json(200, '保存成功');
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
        if (!empty($where['name']) && is_string($where['name'])) {
            $where['name'] = ['like', "%{$where['name']}%"];
        }
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
        foreach ($items as $k => $v) {
            $items[$k]['rewrite']    = $this->getPluginRewrite($v['identifier']);
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
        $code   = 200;
        $msg    = 'ok';
        try {
            $client = Util::httpClient();
            $query  = $request->get();
            $query['version']   = $this->getAdminVersion();
            $response   = $client->post('/api/v1/applist', ['form_params' => $query]);
            $content    = $response->getBody()->getContents();
            $content    = json_decode($content, true);
            if ($content['code'] == 200) {
                $total  = $content['count'] ?? 0;
                $data   = $content['data'] ?? [];
                if (!empty($data) && is_array($data)) {
                    $plugin_identifier  = array_column($data, 'identifier');
                    $list   = $this->model->whereIn('identifier', $plugin_identifier)->select('identifier')->get();
                    if ($list) {
                        $list   = $list->toArray();
                        $list   = array_column($list, 'identifier');
                        foreach ($data as $k => $v) {
                            if (in_array($v['identifier'], $list)) {
                                unset($data[$k]);
                            }
                        }
                        $total  = count($data);
                    }
                }
            } else {
                $code   = $content['code'];
                $msg    = $content['msg'];
            }
        } catch (\Throwable $e) {
            return json(['code' => 400, 'msg' => '网牛云服务不可用', 'count' => 0, 'data' => []]);
        }
        return json(['code' => $code, 'msg' => $msg, 'count' => $total, 'data' => array_values($data)]);
    }

    /**
     * 授权列表
     *
     * 代理商城会员中心的应用授权数据。
     * @param Request $request
     * @return Response
     */
    public function licenses(Request $request): Response
    {
        try {
            $client   = Util::httpClient();
            $response = $client->post('/api/v1/user/licenses', ['form_params' => []]);
            $content  = json_decode($response->getBody()->getContents(), true);
            if (!is_array($content)) {
                return $this->json(400, '网牛云服务返回异常', []);
            }
            return json($content);
        } catch (\Throwable $e) {
            return $this->json(400, '网牛云服务不可用', []);
        }
    }

    /**
     * 解绑授权
     *
     * 代理商城解绑应用授权，解绑后可在任意站点重新安装。
     * @param Request $request
     * @return Response
     */
    public function unbindlicense(Request $request): Response
    {
        try {
            $client   = Util::httpClient();
            $response = $client->post('/api/v1/user/license/unbind', [
                'form_params' => ['app_id' => (int)$request->post('app_id', 0)],
            ]);
            $content  = json_decode($response->getBody()->getContents(), true);
            if (!is_array($content)) {
                return $this->json(400, '网牛云服务返回异常', []);
            }
            return json($content);
        } catch (\Throwable $e) {
            return $this->json(400, '网牛云服务不可用', []);
        }
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
        try {
            $localitems         = [];
            $plugin_names       = array_diff(scandir(base_path() . '/plugin/'), array('.', '..'));
            $existing_plugins   = array_column($this->model->select('identifier')->get()->toArray(), 'identifier');
            foreach ($plugin_names as $k => $plugin_name) {
                if (!in_array($plugin_name, $existing_plugins)) {
                    $plugin_info    = $this->getPluginApp($plugin_name);
                    $plugin_info['installed']       = false;
                    $plugin_info['installedtype']   = 'localapps';
                    if (!empty($plugin_info['identifier'])) {
                        $plugin_info['id'] = $k + 1;
                        $localitems[]   = $plugin_info;
                    }
                }
            }
            $total  = count($localitems);
            $data   = $localitems;
        } catch (\Throwable $e) {
            $total  = 0;
            $data   = [];
        }
        return json(['code' => 200, 'msg' => 'ok', 'count' => $total, 'data' => $data]);
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
                $rewrite  = $request->post('rewrite', false);
                $name   = $post['identifier'];
                if ($rewrite && isset($name)) {
                    $content = '';
                    foreach ($rewrite as $k => $v) {
                        if (empty($v['value']) || empty($v['name']) || empty($v['action'])) {
                            continue;
                        }
                        $v_value    = $v['value'];
                        $v_name     = $v['name'];
                        $v_action   = $v['action'];
                        $content    = $content . PHP_EOL . <<<EOF
                        '$v_value'   => [
                            $v_name::class,
                            '$v_action'
                        ],
                        EOF;
                    };
                } else {
                    $content    = '';
                };
                $config_content = <<<EOF
<?php
return [
    $content
];
EOF;
                $labelpath  = base_path() . '/plugin/' . $name . '/app/support/';
                if (!is_dir($labelpath)) {
                    if (!is_dir($labelpath)) {
                        mkdir($labelpath, 0777, true);
                    }
                }
                Util::pauseFileMonitor();
                file_put_contents($labelpath . 'rewrite.php', $config_content);
                Util::resumeFileMonitor();
                Util::reloadWebman();
            }
            if ($post['identifier']) {
                Rule::where('plugin', $post['identifier'])->update(['open' => $post['open']]);
            }
            return parent::update($request);
        }
        return raw_view('plugin/update');
    }

    /**
     * 检测更新版本（本地优先 + 远程查询）
     * 本地读取 plugin/{identifier}/config/app.php 的版本号，
     * 与客户端提交版本对比；本地版本 ≥ 远程版本时优先返回本地数据。
     * webniu 不走本地查询。
     * @param Request $request
     * @return Response
     * @throws GuzzleException
     */
    public function checkupdate(Request $request): Response
    {
        $version = $request->post('data', null);
        if (empty($version)) {
            return json(['code' => 0, 'msg' => '无更新版本', 'data' => []]);
        }

        // 规范化提交数据：统一为 [identifier => version] 映射
        $submitted = [];
        foreach ($version as $row) {
            if (!is_array($row)) {
                continue;
            }
            $identifier = $row['identifier'] ?? ($row[0] ?? '');
            $ver = $row['version'] ?? ($row[1] ?? '');
            if ($identifier) {
                $submitted[$identifier] = $ver;
            }
        }

        // 1. 远程查询（失败不阻断本地查询）
        $remote_map = [];
        try {
            $client = Util::httpClient();
            $form_params['plugin'] = $version;
            $response = $client->post('/api/v1/checkupdate', ['form_params' => $form_params]);
            $content = $response->getBody()->getContents();
            $content = json_decode($content, true);
            if (($content['code'] ?? 0) == 200) {
                foreach ($content['data'] ?? [] as $item) {
                    if (isset($item['identifier'])) {
                        $remote_map[$item['identifier']] = $item;
                    }
                }
            }
        } catch (\Throwable $e) {
            // 远程失败不阻断，继续本地查询
        }

        // 2. 本地兜底 + 远程合并
        $result = [];
        foreach ($submitted as $identifier => $current_ver) {
            // 远程有更新 → 直接用远程结果
            $remote_item = $remote_map[$identifier] ?? null;
            $remote_ver = $remote_item['version'] ?? '';
            $remote_has_update = $remote_item && $remote_ver && $current_ver
                && version_compare_custom($remote_ver, $current_ver, '>');

            if ($remote_has_update) {
                $result[] = array_merge($remote_item, ['source' => 'remote']);
                continue;
            }

            // webniu 不走本地查询
            if ($identifier === 'webniu') {
                continue;
            }

            // 远程无更新 → 本地兜底：读 plugin/{identifier}/config/app.php
            $local_app = $this->getPluginApp($identifier);
            $local_ver = $local_app['version'] ?? '';
            $local_has_update = $local_ver && $current_ver
                && version_compare_custom($local_ver, $current_ver, '>');

            if ($local_has_update) {
                $result[] = array_merge($local_app, [
                    'identifier' => $identifier,
                    'version' => $local_ver,
                    'installedtype' => 'localapps',
                    'source' => 'local',
                    'app_config' => $local_app,
                ]);
            }
        }

        return $this->json(200, '读取成功', $result);
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
        $globalplugin = options('globalplugin');
        $wn_version = config('plugin.webniu.app.version');
        $name       = $post['identifier'];
        $version    = $post['version'];
        $installed  = $post['installedtype'] ?? false;
        $installed_app      = $this->getPluginVersion($name);
        $installed_version  = $installed_app['version'] ?? null;
        if ($installed_app == null) {
            $installed_app = $post;
        }
        if (!$name || !$version || !$installed) {
            return $this->json(400, '缺少参数');
        }
        // 插件标识只允许字母数字下划线，避免路径/类名注入
        if (!preg_match('/^[a-zA-Z0-9_]+$/', (string)$name)) {
            return $this->json(400, '插件标识不合法');
        }

        // ========== 版本校验：先比前端传值（waitapps 云接口已带），再比本地文件（localapps） ==========
        $plugin_webniu_version = null;
        if (!empty($post['webniu_version'])) {
            // waitapps：前端从云接口已带 webniu_version，直接用（避免下载浪费流量）
            $plugin_webniu_version = $post['webniu_version'];
        } else {
            // localapps：读本地 plugin/{name}/config/app.php
            $local_app_config = $this->getPluginApp($name);
            $plugin_webniu_version = $local_app_config['webniu_version'] ?? null;
        }
        if ($plugin_webniu_version && !version_compare_custom($wn_version, $plugin_webniu_version, '>=')) {
            return $this->json(400, sprintf(
                '网牛引擎版本过低：当前基座版本 v%s，插件「%s」要求 v%s 以上版本',
                $wn_version,
                $name,
                $plugin_webniu_version
            ));
        }

        // waitapps 模式：下载 zip 到 runtime 临时目录（随机文件名，避免占用 plugin 目录、并发冲突与同名覆盖）
        $zip_file = null;
        $cmd = null;
        if ($installed == 'waitapps') {
            $zip_file = $this->makeTempZipPath($name);
            $this->downloadZipFile(base64_decode($post['tokens']), $zip_file);
            if (!class_exists(ZipArchive::class, false)) {
                $cmd = $this->getUnzipCmd($zip_file, base_path() . '/plugin/');
                if (!$cmd) {
                    return $this->json(400, '请给php安装zip模块或者给系统安装unzip命令');
                }
                if (!function_exists('proc_open')) {
                    return $this->json(400, '请解除proc_open函数的禁用或者给php安装zip模块');
                }
            }
        }

        // 解压 + 安装钩子：整体放在 pause/resume 里，避免文件监控误触发
        Util::pauseFileMonitor();
        try {
            // waitapps 解压 zip（zip 扩展可用时先做完整性与结构校验）
            if ($installed == 'waitapps' && $zip_file) {
                if (class_exists(ZipArchive::class, false)) {
                    $this->extractZip($zip_file, $name);
                } else {
                    $this->unzipWithCmd($cmd);
                }
            }

            // 执行安装/更新钩子
            $context = null;
            $install_class = "\\plugin\\$name\\api\\Install";
            if ($installed_version) {
                if (class_exists($install_class) && method_exists($install_class, 'beforeUpdate')) {
                    $context = call_user_func([$install_class, 'beforeUpdate'], $installed_version, $installed_app);
                }
            }
            if ($installed_version) {
                if (class_exists($install_class) && method_exists($install_class, 'update')) {
                    call_user_func([$install_class, 'update'], $installed_version, $installed_app, $context);
                }
            } else {
                if (class_exists($install_class) && method_exists($install_class, 'install')) {
                    call_user_func([$install_class, 'install'], $installed_app);
                }
            }

            // waitapps 安装后清理
            if ($installed == 'waitapps') {
                if (is_file(base_path() . "/plugin/{$name}/public/config/install.php")) {
                    if (isset($globalplugin['globalplugin']['delinstall']) && $globalplugin['globalplugin']['delinstall']) {
                        @unlink(base_path() . "/plugin/{$name}/public/config/install.php");
                    }
                }
                if (is_file(base_path() . "/plugin/{$name}/public/config/update.php")) {
                    if (isset($globalplugin['globalplugin']['delupdate']) && $globalplugin['globalplugin']['delupdate']) {
                        @unlink(base_path() . "/plugin/{$name}/public/config/update.php");
                    }
                }
            }
            $this->updateOrInsert($post);
        } finally {
            // 远程安装的包一律删除，不留存
            if ($zip_file && is_file($zip_file)) {
                @unlink($zip_file);
            }
            Util::resumeFileMonitor();
        }
        Util::reloadWebman();
        return $this->json(200, '安装成功');
    }

    /**
     * 卸载
     * @param Request $request
     * @return Response
     */
    public function uninstall(Request $request): Response
    {
        $globalplugin = options('globalplugin');
        $id     = $request->post('id');
        $Plugin = Plugin::where('id', $id)->first();
        if (!$Plugin) {
            return $this->json(1, '插件不存在');
        }

        $name       = $Plugin->identifier;
        $version    = $Plugin->version;
        if (!$name || !preg_match('/^[a-zA-Z0-9_]+$/', $name) || $name == 'webniu') {
            return $this->json(1, '参数错误，卸载失败!');
        }
        // 获得插件路径
        clearstatcache();
        $path = get_realpath(base_path() . "/plugin/$name");
        if (!$path || !is_dir($path)) {
            // 安装包已被手动删除：执行不到插件里的 uninstall，这里兜底清理安装记录与菜单数据
            Rule::where('plugin', $name)->delete();
            $Plugin->delete();
            Util::reloadWebman();
            return $this->json(200, '包已被手动删除，已清理安装记录与菜单数据');
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
                //卸载删除模块
                if (isset($globalplugin['globalplugin']['delplugin']) && $globalplugin['globalplugin']['delplugin']) {
                    $this->rmDir($path);
                }
            } finally {
                if ($monitor_support_pause) {
                    Monitor::resume();
                }
            }
            $Plugin->delete();
        }
        clearstatcache();

        Util::reloadWebman();

        return $this->json(200, '卸载成功');
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
        $data['created_at'] = date('Y-m-d H:i:s');
        unset($data['id']);
        return Plugin::updateOrInsert(
            [
                'identifier' => $data['identifier']
            ],
            $data
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
        if (!$token) {
            return $this->json(1, '请先获取验证码');
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
            if ($data['code'] == 200) {
                session()->set('webniu-plugin-user', $data['data']);
            }
            return $this->json($data['code'], $data['msg'], $data['data']);
        } catch (\Exception $e) {
        }
        return $this->json(1, '登录异常,请重试!', []);
    }

    /**
     * 注册网牛
     * @param Request $request
     * @return Response|string
     * @throws GuzzleException
     */
    public function register(Request $request)
    {
        $token = session()->get('webniu-plugin-token');
        if (!$token) {
            return $this->json(1, '请先获取验证码');
        }
        try {
            $client     = Util::httpClient();
            $response   = $client->post('/api/v1/user/register', [
                'form_params' => [
                    'username' => $request->post('username'),
                    'password' => $request->post('password'),
                    'confirmpassword' => $request->post('confirmpassword'),
                    'nickname' => $request->post('nickname'),
                    'captcha'  => $request->post('captcha'),
                ]
            ]);
            $content = $response->getBody()->getContents();
            $data = json_decode($content, true);
            if ($data['code'] == 200) {
                return $this->json($data['code'], $data['msg'], []);
            }
            return $this->json($data['code'], $data['msg'], $data['data']);
        } catch (\Exception $e) {
        }
        return $this->json(1, '注册异常,请官网注册!', []);
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
            if ($data['code'] == 200) {
                return $this->json($data['code'], $data['msg'], []);
            }
            if ($data['code'] == 407) {
                $request->session()->delete('webniu-plugin-user');
                return $this->json($data['code'], $data['msg'], []);
            }
        } catch (\Exception $e) {
            return $this->json(400, '退出异常!', []);
        }
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
            if ($data['code'] == 407) {
                $request->session()->delete('webniu-plugin-user');
                return $this->json($data['code'], $data['msg'], []);
            }
            return $this->json($data['code'], $data['msg'], $data['data']);
        } catch (\Exception $e) {
            return $this->json(1, $e->getMessage(), []);
        }
    }

    /**
     * 下载zip
     *
     * 下载到 runtime 临时文件并校验完整性，失败自动重试；
     * 不完整/非 zip 会立即报错，避免把坏包解压进 plugin 目录。
     *
     * @param string $url
     * @param string $file
     * @return void
     * @throws BusinessException
     */
    protected function downloadZipFile($url, $file)
    {
        $url = (string)$url;
        if (!preg_match('~^https?://~i', $url)) {
            throw new BusinessException('下载地址无效：' . $url);
        }
        $max_attempts = 3;
        $last_error   = '';
        for ($attempt = 1; $attempt <= $max_attempts; $attempt++) {
            @unlink($file);
            try {
                $this->fetchZipFile($url, $file);
                return;
            } catch (BusinessException $e) {
                // 业务错误（无权限 / 包不存在等）重试无意义
                @unlink($file);
                throw $e;
            } catch (\Throwable $e) {
                // 网络截断等可重试错误
                $last_error = $e->getMessage() ?: '下载失败';
                @unlink($file);
                if ($attempt < $max_attempts) {
                    usleep(300000);
                }
            }
        }
        throw new BusinessException(sprintf('安装包下载失败（已重试 %d 次）：%s', $max_attempts, $last_error ?: '未知错误'));
    }

    /**
     * 拉取 zip 内容并落盘
     *
     * @param string $url
     * @param string $file
     * @return void
     * @throws BusinessException|\Throwable
     */
    protected function fetchZipFile(string $url, string $file): void
    {
        // 下载安装包单独放宽超时，默认 60s 在慢网络/大包时会被截断
        $client = Util::httpClient([
            'base_uri'        => config('plugin.webniu.app.plugin_market_host'),
            'timeout'         => 300,
            'connect_timeout' => 10,
            'verify'          => false,
            'http_errors'     => false,
            'headers'         => [
                'Domain'     => hosturl(),
                'Referer'    => \request()->fullUrl(),
                'User-Agent' => 'webniu-app-plugin',
                'Accept'     => '*/*',
                'version'    => config('plugin.webniu.app.version'),
            ],
        ]);
        $response = $client->get($url);
        $status   = $response->getStatusCode();
        $body     = $response->getBody();
        if ($status == 404) {
            throw new BusinessException('安装包不存在（云端 404）');
        }

        $content = $body->getContents();
        if ($status == 503) {
            throw new BusinessException($content ?: '安装包服务暂时不可用');
        }
        if ($status != 200) {
            throw new \RuntimeException(sprintf('HTTP %s', $status));
        }
        if ($content === '') {
            throw new \RuntimeException('下载内容为空');
        }

        // 云端返回 JSON：业务错误（无权限/包不存在等），不重试
        if (str_starts_with(ltrim($content), '{')) {
            $json = json_decode($content, true);
            throw new BusinessException('安装包下载失败：' . (($json['msg'] ?? '') ?: '云端返回异常内容'));
        }

        // zip 必须以 PK 开头，否则多半是拦截页/登录页/CDN 错误页
        if (substr($content, 0, 2) !== 'PK') {
            throw new BusinessException(sprintf(
                '下载内容不是安装包（HTTP %s，Content-Type: %s，大小 %d 字节），请检查下载地址或服务器出网',
                $status,
                $response->getHeaderLine('Content-Type') ?: '未知',
                strlen($content)
            ));
        }

        // Content-Length 与实际字节数不符 → 传输被截断（gzip 传输时长度不可比，跳过）
        $content_length = $response->getHeaderLine('Content-Length');
        if ($response->getHeaderLine('Content-Encoding') === '' && $content_length !== '' && is_numeric($content_length)) {
            $declared = (int)$content_length;
            if (strlen($content) < $declared) {
                throw new \RuntimeException(sprintf('下载被截断（%d/%d 字节）', strlen($content), $declared));
            }
        }

        $bytes = @file_put_contents($file, $content);
        if ($bytes === false) {
            throw new BusinessException('安装包写入失败：请检查 runtime 目录写权限或磁盘空间');
        }
        if ($bytes !== strlen($content)) {
            throw new \RuntimeException(sprintf('写入不完整（%d/%d 字节）', $bytes, strlen($content)));
        }
    }

    /**
     * 生成临时安装包路径
     *
     * 放在 runtime 目录并使用随机文件名，避免占用 plugin 目录、开发者自打包同名文件、并发安装互相覆盖。
     *
     * @param string $name 插件标识
     * @return string
     * @throws BusinessException
     */
    protected function makeTempZipPath(string $name): string
    {
        $dir = runtime_path() . '/plugin-install';
        if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new BusinessException('无法创建临时目录：' . $dir . '，请检查 runtime 目录写权限');
        }
        $safe = preg_replace('/[^A-Za-z0-9_\-]/', '', $name);
        if ($safe === '') {
            $safe = 'plugin';
        }
        return $dir . '/' . $safe . '-' . uniqid('', true) . '.zip';
    }

    /**
     * 校验并解压安装包
     *
     * 解压前做一致性校验与目录结构校验，避免坏包/错误包污染 plugin 目录。
     *
     * @param string $zip_file
     * @param string $name 插件标识
     * @return void
     * @throws BusinessException
     */
    protected function extractZip(string $zip_file, string $name): void
    {
        clearstatcache(true, $zip_file);
        if (!is_file($zip_file) || filesize($zip_file) <= 0) {
            throw new BusinessException('安装包下载失败：文件不存在或为空');
        }
        $zip = new ZipArchive;
        // CHECKCONS：额外一致性校验，可识别大部分截断/损坏包
        $ret = $zip->open($zip_file, ZipArchive::CHECKCONS);
        if ($ret !== true) {
            throw new BusinessException(sprintf(
                '安装包已损坏，无法解压（错误码 %s，文件大小 %d 字节），请重新安装',
                $ret,
                (int)filesize($zip_file)
            ));
        }
        // 校验包内根目录为插件标识，并拒绝绝对路径/路径穿越条目（Zip Slip）
        $prefix = $name . '/';
        $found  = false;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entry = str_replace('\\', '/', (string)$zip->getNameIndex($i));
            // 兼容以 ./ 开头的打包工具
            $entry = preg_replace('#^(?:\./)+#', '', $entry);
            if ($entry === '') {
                continue;
            }
            if ($entry[0] === '/' || preg_match('#^[A-Za-z]:#', $entry) || preg_match('#(^|/)\.\.(/|$)#', $entry)) {
                $zip->close();
                throw new BusinessException('安装包包含非法路径，已终止安装');
            }
            if ($entry === $name || $entry === $prefix || str_starts_with($entry, $prefix)) {
                $found = true;
            }
        }
        if (!$found) {
            $zip->close();
            throw new BusinessException(sprintf('安装包结构异常：包内未找到「%s」目录，已终止安装', $name));
        }
        $ok = $zip->extractTo(base_path() . '/plugin/');
        $zip->close();
        if ($ok === false) {
            throw new BusinessException('安装包解压失败：请检查 plugin 目录写权限与磁盘空间');
        }
    }

    /**
     * 获取系统支持的解压命令
     * @param $zip_file
     * @param $extract_to
     * @return mixed|string|null
     */
    protected function getUnzipCmd($zip_file, $extract_to)
    {
        $zip_arg = escapeshellarg($zip_file);
        $dir_arg = escapeshellarg($extract_to);
        if ($cmd = $this->findCmd('unzip')) {
            $cmd = "$cmd -o -qq $zip_arg -d $dir_arg";
        } else if ($cmd = $this->findCmd('7z')) {
            $cmd = "$cmd x -bb0 -y $zip_arg -o$dir_arg";
        } else if ($cmd = $this->findCmd('7zz')) {
            $cmd = "$cmd x -bb0 -y $zip_arg -o$dir_arg";
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
        $plugin = Plugin::where('identifier', $name)->first();
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
        if (is_file($file = base_path() . "/plugin/$name/app/support/rewrite.php")) {
            $config = include $file;
            if (is_array($config)) {
                foreach ($config as $k => $v) {
                    $data[] = [
                        'value' => $k,
                        'name'  => $v[0],
                        'action' => $v[1]
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

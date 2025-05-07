<?php

namespace plugin\webniu\app\web\controller;

use Illuminate\Database\Capsule\Manager;
use plugin\webniu\app\common\Util;
use support\exception\BusinessException;
use support\Request;
use support\Response;
use Webman\Captcha\CaptchaBuilder;
use Workerman\Worker;

/**
 * 安装
 */
class InstallController extends Base
{
    /**
     * 不需要登录的方法
     * @var string[]
     */
    protected $noNeedLogin = ['step0','detection','step1','step2','not_found'];
    public function step0(Request $request): Response
    {
        $database_config_file = include base_path() . '/plugin/webniu/config/database.php'; 
        $config     = $database_config_file['connections']['mysql'];
        $db         = $this->getPdo($config['host'],$config['username'], $config['password'], $config['port']);
        $database   = $config['username'];
        $db->exec("use $database");
        // 导入菜单
        $menus = include base_path() . '/plugin/webniu/config/menu.php';
        // 安装过程中没有数据库配置，无法使用api\Menu::import()方法
        $this->importMenu($menus, $db,$config['prefix']);
        return $this->json(0);
    }
    /**
     * 404
     * @param Request $request
     * @return Response
     * @throws BusinessException|\Throwable
     */
    public function not_found(){
        return not_found();
    }
    /**
     * 系统检测
     * @param Request $request
     * @return Response
     * @throws BusinessException|\Throwable
     */
    public function detection(Request $request): Response
    { 
        $data = [
            [
                'title'     =>'操作系统',
                'recommend' =>'Linux',
                'current'   =>PHP_OS,
            ],[
                'title'     =>'PHP版本',
                'recommend' =>'>=8.1.0',
                'current'   =>phpversion(),
            ],[
                'title'     =>'Workerman版本',
                'recommend' =>'>=5.1.0',
                'current'   =>Worker::VERSION
            ],[
                'title'     =>'Webman版本',
                'recommend' =>'>=2.0.0',
                'current'   =>Util::getPackageVersion('workerman/webman-framework')
            ],[
                'title'     =>'Mysqli',
                'recommend' =>'模块',
                'current'   =>extension_loaded('mysqli')?'已安装':'未安装'
            ],[
                'title'     =>'Redis',
                'recommend' =>'扩展',
                'current'   =>Util::getPackageVersion('illuminate/redis')??'未安装'
            ]
        ];  
        $dirfile = check_dirfile();
        return json(['version' => config('plugin.webniu.app.version'), 'data' => $data, 'dirfile' => $dirfile]);
    }
    /**
     * 设置数据库
     * @param Request $request
     * @return Response
     * @throws BusinessException|\Throwable
     */
    public function step1(Request $request): Response
    {
        $database_config_file = base_path() . '/plugin/webniu/config/database.php';
        clearstatcache();
        if (is_file($database_config_file)) {
            return $this->json(1, '管理后台已经安装！如需重新安装，请删除该插件数据库配置文件并重启');
        }

        if (!class_exists(CaptchaBuilder::class) || !class_exists(Manager::class)) {
            return $this->json(1, '请运行 composer require -W illuminate/database 安装illuminate/database组件并重启');
        }

        $user       = $request->post('user');
        $password   = $request->post('password');
        $database   = $request->post('database');
        $host       = $request->post('host');
        $prefix     = $request->post('prefix');
        $port       = (int)$request->post('port') ?: 3306;
        $overwrite  = $request->post('overwrite');

        try {
            $db = $this->getPdo($host, $user, $password, $port);
            $smt = $db->query("show databases like '$database'");
            if (empty($smt->fetchAll())) {
                $db->exec("create database $database");
            }
            $db->exec("use $database");
            $smt = $db->query("show tables");
            $tables = $smt->fetchAll();
        } catch (\Throwable $e) {
            if (stripos($e, 'Access denied for user')) {
                return $this->json(1, '数据库用户名或密码错误');
            }
            if (stripos($e, 'Connection refused')) {
                return $this->json(1, 'Connection refused. 请确认数据库IP端口是否正确，数据库已经启动');
            }
            if (stripos($e, 'timed out')) {
                return $this->json(1, '数据库连接超时，请确认数据库IP端口是否正确，安全组及防火墙已经放行端口');
            }
            throw $e;
        }
 
        $tables_to_install = [$prefix.'account',$prefix.'account_application',$prefix.'account_datacube',$prefix.'account_fans',$prefix.'account_fans_groups',$prefix.'account_options',$prefix.'account_users',$prefix.'account_wechat',$prefix.'account_wechat_mass',$prefix.'account_wechat_menu',$prefix.'account_wechat_qrcode',$prefix.'account_wechat_qrcode_scenes',$prefix.'account_wechat_reply',$prefix.'account_wechat_rule',$prefix.'account_wxapps',$prefix.'admins',$prefix.'admin_log',$prefix.'admin_roles',$prefix.'application',$prefix.'article_category',$prefix.'article_news',$prefix.'options',$prefix.'pay_record',$prefix.'pmtype',$prefix.'redis_list',$prefix.'roles',$prefix.'rules',$prefix.'uploads',$prefix.'users',$prefix.'users_groups',$prefix.'users_record',$prefix.'users_site',$prefix.'sms_temp',$prefix.'email_temp', ];


        $tables_exist = [];
        foreach ($tables as $table) {
            $tables_exist[] = current($table);
        }
        $tables_conflict = array_intersect($tables_to_install, $tables_exist);
        if (!$overwrite) {
            if ($tables_conflict) {
                return $this->json(1, '以下表' . implode(',', $tables_conflict) . '已经存在，如需覆盖请选择强制覆盖');
            }
        } else {
            foreach ($tables_conflict as $table) {
                $db->exec("DROP TABLE `$table`");
            }
        }

        $sql_file = base_path() . '/plugin/webniu/public/config/install.sql';
        if (!is_file($sql_file)) {
            return $this->json(1, '数据库SQL文件不存在');
        }

        $sql_query = file_get_contents($sql_file);
        $sql_query = $this->removeComments($sql_query);
        $sql_query = $this->splitSqlFile($sql_query, ';');
        $sql_query = str_replace(" `__PREFIX__", " `{$prefix}", $sql_query);
        foreach ($sql_query as $sql) {
            $db->exec($sql);
        }

        // 导入菜单
        $menus = include base_path() . '/plugin/webniu/config/menu.php';
        // 安装过程中没有数据库配置，无法使用api\Menu::import()方法
        $this->importMenu($menus, $db,$prefix);

        $config_content = <<<EOF
<?php
return  [
    'default' => 'mysql',
    'connections' => [
        'mysql' => [
            'driver'      => 'mysql',
            'host'        => '$host',
            'port'        => '$port',
            'database'    => '$database',
            'username'    => '$user',
            'password'    => '$password',
            'charset'     => 'utf8mb4',
            'collation'   => 'utf8mb4_general_ci',
            'prefix'      => '$prefix',
            'strict'      => true,
            'engine'      => null,
        ],
    ],
];
EOF;

        file_put_contents($database_config_file, $config_content);

        $think_orm_config = <<<EOF
<?php
return [
    'default' => 'mysql',
    'connections' => [
        'mysql' => [
            // 数据库类型
            'type' => 'mysql',
            // 服务器地址
            'hostname' => '$host',
            // 数据库名
            'database' => '$database',
            // 数据库用户名
            'username' => '$user',
            // 数据库密码
            'password' => '$password',
            // 数据库连接端口
            'hostport' => $port,
            // 数据库连接参数
            'params' => [
                // 连接超时3秒
                \PDO::ATTR_TIMEOUT => 3,
            ],
            // 数据库编码默认采用utf8
            'charset' => 'utf8mb4',
            // 数据库表前缀
            'prefix' => '$prefix',
            // 断线重连
            'break_reconnect' => true,
            // 关闭SQL监听日志
            'trigger_sql' => true,
            // 自定义分页类
            'bootstrap' =>  ''
        ],
    ],
];
EOF;
        file_put_contents(base_path() . '/plugin/webniu/config/thinkorm.php', $think_orm_config);


        // 尝试reload
        if (function_exists('posix_kill')) {
            set_error_handler(function () {});
            posix_kill(posix_getppid(), SIGUSR1);
            restore_error_handler();
        }

        return $this->json(0);
    }

    /**
     * 设置管理员
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function step2(Request $request): Response
    {
        $username = $request->post('username');
        $password = $request->post('password');
        $password_confirm = $request->post('password_confirm');
        if ($password != $password_confirm) {
            return $this->json(1, '两次密码不一致');
        }
        if (!is_file($config_file = base_path() . '/plugin/webniu/config/database.php')) {
            return $this->json(1, '请先完成第一步数据库配置');
        }
        $config = include $config_file;
        $connection = $config['connections']['mysql'];
        $pdo = $this->getPdo($connection['host'], $connection['username'], $connection['password'], $connection['port'], $connection['database']);

        if ($pdo->query("select * from `{$connection['prefix']}admins`")->fetchAll()) {
            return $this->json(1, '后台已经安装完毕，无法通过此页面创建管理员');
        }

        $smt = $pdo->prepare("insert into `{$connection['prefix']}admins` (`username`, `password`, `nickname`, `created_at`, `updated_at`) values (:username, :password, :nickname, :created_at, :updated_at)");
        $time = date('Y-m-d H:i:s');
        $data = [
            'username' => $username,
            'password' => Util::passwordHash($password),
            'nickname' => '超级管理员',
            'created_at' => $time,
            'updated_at' => $time
        ];
        foreach ($data as $key => $value) {
            $smt->bindValue($key, $value);
        }
        $smt->execute();
        $admin_id = $pdo->lastInsertId();

        $smt = $pdo->prepare("insert into `{$connection['prefix']}admin_roles` (`role_id`, `admin_id`) values (:role_id, :admin_id)");
        $smt->bindValue('role_id', 1);
        $smt->bindValue('admin_id', $admin_id);
        $smt->execute();

        $options    = file_get_contents(base_path('plugin/webniu/public/config/pear.config.json')); 
        $options    = str_replace("__WEBNIUGICAI__",randStr(20), $options);
        $options    = json_decode($options, true); 
        $smt        = $pdo->prepare("insert into `{$connection['prefix']}options` (`model`,`name`,`value`,`created_at`) values (:model,:name,:value,:created_at)");
        foreach($options as $k=>$v){
            $smt->bindValue('model', 'webniu');
            $smt->bindValue('name', 'system_'.$k);
            $smt->bindValue('value',json_encode($v));
            $smt->bindValue('created_at',date('Y-m-d H:i:s'));
            $smt->execute();
        }

        $request->session()->flush();
        return $this->json(0);
    }

    /**
     * 添加菜单
     * @param array $menu
     * @param \PDO $pdo
     * @return int
     */
    protected function addMenu(array $menu, \PDO $pdo,$prefix): int
    {
        $allow_columns = ['title', 'key', 'icon', 'href', 'pid', 'weight', 'type','opentype'];
        $data = [];
        foreach ($allow_columns as $column) {
            if (isset($menu[$column])) {
                $data[$column] = $menu[$column];
            }
        }
        $time = date('Y-m-d H:i:s');
        $data['created_at'] = $data['updated_at'] = $time;
        $values = [];
        foreach ($data as $k => $v) {
            $values[] = ":$k";
        }
        $columns = array_keys($data);
        foreach ($columns as $k => $column) {
            $columns[$k] = "`$column`";
        }
        $sql = "insert into {$prefix}rules (" .implode(',', $columns). ") values (" . implode(',', $values) . ")";
        $smt = $pdo->prepare($sql);
        foreach ($data as $key => $value) {
            $smt->bindValue($key, $value);
        }
        $smt->execute();
        return $pdo->lastInsertId();
    }

    /**
     * 导入菜单
     * @param array $menu_tree
     * @param \PDO $pdo
     * @return void
     */
    protected function importMenu(array $menu_tree, \PDO $pdo,$prefix)
    {
        if (is_numeric(key($menu_tree)) && !isset($menu_tree['key'])) {
            foreach ($menu_tree as $item) {
                $this->importMenu($item, $pdo,$prefix);
            }
            return;
        }
        $children = $menu_tree['children'] ?? [];
        unset($menu_tree['children']);
        $smt = $pdo->prepare("select * from {$prefix}rules where `key`=:key limit 1");
        $smt->execute(['key' => $menu_tree['key']]);
        $old_menu = $smt->fetch();
        if ($old_menu) {
            $pid = $old_menu['id'];
            $params = [
                'title' => $menu_tree['title'],
                'icon' => $menu_tree['icon'] ?? '',
                'key' => $menu_tree['key'],
                'opentype' => $menu_tree['opentype'] ?? '_iframe',
            ];
            $sql = "update {$prefix}rules set title=:title, icon=:icon where `key`=:key";
            $smt = $pdo->prepare($sql);
            $smt->execute($params);
        } else {
            $pid = $this->addMenu($menu_tree, $pdo,$prefix);
        }
        foreach ($children as $menu) {
            $menu['pid'] = $pid;
            $this->importMenu($menu, $pdo,$prefix);
        }
    }

    /**
     * 去除sql文件中的注释
     * @param $sql
     * @return string
     */
    protected function removeComments($sql): string
    {
        return preg_replace("/(\n--[^\n]*)/","", $sql);
    }

    /**
     * 分割sql文件
     * @param $sql
     * @param $delimiter
     * @return array
     */
    function splitSqlFile($sql, $delimiter): array
    {
        $tokens = explode($delimiter, $sql);
        $output = array();
        $matches = array();
        $token_count = count($tokens);
        for ($i = 0; $i < $token_count; $i++) {
            if (($i != ($token_count - 1)) || (strlen($tokens[$i] > 0))) {
                $total_quotes = preg_match_all("/'/", $tokens[$i], $matches);
                $escaped_quotes = preg_match_all("/(?<!\\\\)(\\\\\\\\)*\\\\'/", $tokens[$i], $matches);
                $unescaped_quotes = $total_quotes - $escaped_quotes;

                if (($unescaped_quotes % 2) == 0) {
                    $output[] = $tokens[$i];
                    $tokens[$i] = "";
                } else {
                    $temp = $tokens[$i] . $delimiter;
                    $tokens[$i] = "";

                    $complete_stmt = false;
                    for ($j = $i + 1; (!$complete_stmt && ($j < $token_count)); $j++) {
                        $total_quotes = preg_match_all("/'/", $tokens[$j], $matches);
                        $escaped_quotes = preg_match_all("/(?<!\\\\)(\\\\\\\\)*\\\\'/", $tokens[$j], $matches);
                        $unescaped_quotes = $total_quotes - $escaped_quotes;
                        if (($unescaped_quotes % 2) == 1) {
                            $output[] = $temp . $tokens[$j];
                            $tokens[$j] = "";
                            $temp = "";
                            $complete_stmt = true;
                            $i = $j;
                        } else {
                            $temp .= $tokens[$j] . $delimiter;
                            $tokens[$j] = "";
                        }

                    }
                }
            }
        }

        return $output;
    }

    /**
     * 获取pdo连接
     * @param $host
     * @param $username
     * @param $password
     * @param $port
     * @param $database
     * @return \PDO
     */
    protected function getPdo($host, $username, $password, $port, $database = null): \PDO
    {
        $dsn = "mysql:host=$host;port=$port;";
        if ($database) {
            $dsn .= "dbname=$database";
        }
        $params = [
            \PDO::MYSQL_ATTR_INIT_COMMAND => "set names utf8mb4",
            \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
            \PDO::ATTR_EMULATE_PREPARES => false,
            \PDO::ATTR_TIMEOUT => 5,
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        ];
        return new \PDO($dsn, $username, $password, $params);
    }

}

<?php

/**
 * Here is your custom functions.
 */

use plugin\webniu\app\model\Admin;
use plugin\webniu\app\model\AdminRole;
use plugin\webniu\app\model\AdminLog;
use plugin\webniu\app\model\Option;
use plugin\webniu\app\model\Statistics;
use plugin\webniu\app\model\Confset;
use plugin\webniu\app\model\ConfsetGroup;
use plugin\webniu\app\common\Util;
use support\Response;

/**
 * 当前管理员id
 * @return integer|null
 */
if (!function_exists('admin_id')) {
    function admin_id(): ?int
    {
        return session('admin.id');
    }
}
/**
 * 当前管理员
 * @param null|array|string $fields
 * @return array|mixed|null
 * @throws Exception
 */
if (!function_exists('admin')) {
    function admin($fields = null)
    {
        refresh_admin_session();
        if (!$admin = session('admin')) {
            return null;
        }
        if ($fields === null) {
            return $admin;
        }
        if (is_array($fields)) {
            $results = [];
            foreach ($fields as $field) {
                $results[$field] = $admin[$field] ?? null;
            }
            return $results;
        }
        return $admin[$fields] ?? null;
    }
}


/**
 * 刷新当前管理员session
 * @param bool $force
 * @return void
 * @throws Exception
 */
if (!function_exists('refresh_admin_session')) {
    function refresh_admin_session(bool $force = false)
    {
        $admin_session = session('admin');
        if (!$admin_session) {
            return null;
        }
        $admin_id = $admin_session['id'];
        $time_now = time();
        // session在2秒内不刷新
        $session_ttl = 2;
        $session_last_update_time = session('admin.session_last_update_time', 0);
        if (!$force && $time_now - $session_last_update_time < $session_ttl) {
            return null;
        }
        $session = request()->session();
        $admin = Admin::find($admin_id);
        if (!$admin) {
            $session->forget('admin');
            return null;
        }
        $admin = $admin->toArray();
        $admin['password'] = md5($admin['password']);
        $admin_session['password'] = $admin_session['password'] ?? '';
        if ($admin['password'] != $admin_session['password']) {
            $session->forget('admin');
            return null;
        }
        // 账户被禁用
        if ($admin['status'] != 1) {
            $session->forget('admin');
            return;
        }
        $admin['roles'] = AdminRole::where('admin_id', $admin_id)->pluck('role_id')->toArray();
        $admin['session_last_update_time'] = $time_now;
        $session->set('admin', $admin);
    }
}

if (!function_exists('admin_error_401_script')) {
    function admin_error_401_script(): Response
    {
        return response(
            <<<EOF
<script>top.location.href = '/webniu';</script>
EOF
        );
    }
}

/**
 * 下划线转小驼峰（数据库字段 => 接口字段）
 * @param string $str 下划线字符串 user_name
 * @return string 小驼峰 userName
 */
if (!function_exists('to_camel_case')) {
    function to_camel_case(string $str): string
    {
        // 把下划线后面的字母变大写，再删除下划线
        $str = ucwords(str_replace('_', ' ', $str));
        $str = str_replace(' ', '', lcfirst($str));

        return $str;
    }
}

/**
 * 批量把数组key从下划线转小驼峰 arrayKeyToCamel
 * @param array $data
 * @return array
 */
if (!function_exists('array_key_to_camel')) {
    function array_key_to_camel(array $data): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            $camelKey = to_camel_case($key);
            $result[$camelKey] = $value;
        }
        return $result;
    }
}

/**
 * 小驼峰转下划线（接口字段 => 数据库字段）
 * @param string $str 小驼峰字符串 userName
 * @return string 下划线字符串 user_name
 */
if (!function_exists('to_snake_case')) {
    function to_snake_case(string $str): string
    {
        $str = preg_replace('/([A-Z])/', '_$1', $str);
        $str = strtolower($str);
        $str = ltrim($str, '_');

        return $str;
    }
}

/**
 * 批量把数组key从小驼峰转下划线 arrayKeyToSnake
 * @param array $data
 * @return array
 */
if (!function_exists('array_key_to_snake')) {
    function array_key_to_snake(array $data): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            $snakeKey = to_snake_case($key);
            $result[$snakeKey] = $value;
        }
        return $result;
    }
}

/**
 * 检测读写环境
 */
if (!function_exists('check_dirfile')) {
    function check_dirfile()
    {
        $success    = 'ri:check-fill';
        $error      = 'ri:close-fill';
        $items      = array(
            array('dir' => 'dir', 'write' => $success, 'read' => $success, 'path' => '/'),
            array('dir' => 'dir', 'write' => $success, 'read' => $success, 'path' => '/public'),
            array('dir' => 'dir', 'write' => $success, 'read' => $success, 'path' => '/runtime'),
            array('dir' => 'dir', 'write' => $success, 'read' => $success, 'path' => '/plugin/webniu/app'),
            array('dir' => 'dir', 'write' => $success, 'read' => $success, 'path' => '/plugin/webniu/config'),
            array('dir' => 'dir', 'write' => $success, 'read' => $success, 'path' => '/plugin/webniu/public/upload'),
        );

        foreach ($items as &$value) {
            $item = base_path() . $value['path'];
            // 写入权限
            if (!is_writable($item)) {
                $value['write'] = $error;
            }
            // 读取权限
            if (!is_readable($item)) {
                $value['read'] = $error;
            }
        }
        return $items;
    }
}

/**
 * 随机生成字符串
 * @param int $length 字符串长度（不包含前缀）
 * @param string $prefix 前缀
 * @param string $dictionary 字符字典
 * @return string
 */
if (!function_exists('random_string')) {
    function random_string(int $length = 8, string $prefix = '', string $dictionary = ''): string
    {
        // 默认字典：字母+数字
        if (empty($dictionary)) {
            $dictionary = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }

        $string = '';
        $dict_length = strlen($dictionary);

        for ($i = 0; $i < $length; $i++) {
            $string .= $dictionary[mt_rand(0, $dict_length - 1)];
        }

        return $prefix . $string;
    }
}
/**
 * 处理配置文件options字段
 * @param array $str options字段
 * @return array
 */
if (!function_exists('options')) {
    function options(array|string $str, string $model = 'system'): array
    {
        $arr = [];
        if (empty($str)) {
            return [];
        }
        if (is_array($str)) {
            foreach ($str as $item) {
                $arr[$item] = json_decode(Option::where([
                    ['name', '=', $item],
                    ['model', '=', $model],
                ])->pluck('value')->first() ?? '{}', true) ?? [];
            }
        } else {
            $option = Option::where([
                ['name', '=', $str],
                ['model', '=', $model],
            ])->pluck('value')->first();
            $arr[$str] = json_decode($option ?? '{}', true) ?? [];
        }
        return $arr;
    }
}

/**
 * 处理配置文件options字段
 * @param array $str options字段
 * @return array
 */
if (!function_exists('setoptions')) {
    function setoptions(string $label, array $value, string $model = 'system'): bool
    {
        if (empty($label) || empty($value)) {
            return false;
        }
        try {
            $admin = admin();
            $option = Option::where([
                ['name', '=', $label],
                ['model', '=', $model],
            ])->first();
            if ($option) {
                $option->value = json_encode($value);
                $option->updated_at = date('Y-m-d H:i:s');
                $option->username = $admin['username'];
                $option->save();
            } else {
                $option = new Option();
                $option->name = $label;
                $option->model = $model;
                $option->value = json_encode($value);
                $option->username = $admin['username'];
                $option->save();
            }
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }
}

/**
 * 删除配置文件options字段
 * @param array $str options字段
 * @return array
 */
if (!function_exists('deloptions')) {
    function deloptions(array|string $str, string $model = 'system'): bool
    {
        if (empty($str)) {
            return false;
        }
        if (is_array($str)) {
            foreach ($str as $item) {
                Option::where([
                    ['name', '=', $item],
                    ['model', '=', $model],
                ])->delete();
            }
        } else {
            Option::where([
                ['name', '=', $str],
                ['model', '=', $model],
            ])->delete();
        }
        return true;
    }
}

/**
 * 判断是否安装向导完成
 * @return bool
 */
if (!function_exists('is_install')) {
    function is_install(): bool
    {
        clearstatcache();
        if (!is_file(base_path('plugin/webniu/config/database.php'))) {
            return false;
        }
        return true;
    }
}

/**
 * 判断是否二维数组是否为空
 * @param array $arr 二维数组
 * @return bool
 */
if (!function_exists('isEmpty2DArray')) {
    function isEmpty2DArray($arr)
    {
        // 先判断外层是否为空
        if (empty($arr)) {
            return true;
        }
        // 遍历每个子数组，只要有一个不为空，就返回 false
        foreach ($arr as $item) {
            if (!empty($item)) {
                return false;
            }
        }
        // 所有子数组都为空，返回 true
        return true;
    }
}
/**
 * 判断字符串是否为指定类型
 * @param string $str 要验证的字符串
 * @param string $type 类型：mobile(手机号)、email(邮箱)、url(网址)、ip(IP地址)、idcard(身份证号)、tel(固话)
 * @return bool
 */
if (!function_exists('is_type')) {
    function is_type(string $str, string $type): bool
    {
        $str = trim($str);
        if (empty($str)) {
            return false;
        }

        switch (strtolower($type)) {
            case 'mobile':
            case '手机号':
                // 中国大陆手机号验证
                return preg_match('/^1[3-9]\d{9}$/', $str) === 1;

            case 'email':
            case '邮箱':
                return filter_var($str, FILTER_VALIDATE_EMAIL) !== false;

            case 'url':
            case '网址':
                return filter_var($str, FILTER_VALIDATE_URL) !== false;

            case 'ip':
            case 'ip地址':
                return filter_var($str, FILTER_VALIDATE_IP) !== false;

            case 'idcard':
            case '身份证号':
                // 18位身份证号验证
                return preg_match('/^\d{17}[\dXx]$/', $str) === 1;

            case 'tel':
            case '固话':
                // 固定电话验证（支持区号）
                return preg_match('/^(\d{3,4}-)?\d{7,8}$/', $str) === 1;

            default:
                return false;
        }
    }
}
/**
 * 记录管理员操作日志
 * @return bool
 */
if (!function_exists('adminlog')) {
    function adminlog()
    {
        $a_session  = session('admin');
        $request    = request();
        $header     = $request->header();
        $AdminLog   = new AdminLog;
        $userAgent  = $header['user-agent'];
        $AdminLog->username     = $a_session['username'];
        $AdminLog->nickname     = $a_session['nickname'] ?? '未知';
        $AdminLog->user_ip      = $request->getRealIp();
        $AdminLog->user_agent   = $userAgent;
        if (preg_match('/.*?\((.*?)\).*?/', $userAgent, $matches)) {
            $user_os = substr($matches[1], 0, strpos($matches[1], ';'));
        } else {
            $user_os = '未知';
        }
        $AdminLog->user_os      = $user_os;
        $AdminLog->admin_id     = $a_session['id'];
        $AdminLog->user_browser = preg_replace('/[^(]+\((.*?)[^)]+\) .*?/', '$1', $userAgent);
        $AdminLog->error        = '成功';
        $AdminLog->status       = '1';
        $AdminLog->save();
        return true;
    }
}

/**
 * 统计插件安装量
 * @return bool
 */
if (!function_exists('statistics')) {
    function statistics($model = 'webniu'): bool
    {
        $AdminLog   = new Statistics;
        $timestamp  = date("Y-m-d", time());
        if (!$AdminLog->where('model', $model)->whereDate('created_at', $timestamp)->increment('count')) {
            $AdminLog->model       = $model;
            $AdminLog->count       = 1;
            $AdminLog->created_at  = $timestamp;
            $AdminLog->save();
        };
        return true;
    }
}

//获取当前完整url
if (!function_exists('fullUrl')) {
    function fullUrl()
    {
        $request    = request();
        return ($request->header('x-forwarded-proto') ?? 'http') . ":" . $request->fullUrl();
    }
}

//获取域名
if (!function_exists('hosturl')) {
    function hosturl($port = true)
    {
        $request    = request();
        return ($request->header('x-forwarded-proto') ?? 'http') . "://" . $request->host();
    }
}

/**
 * 随机获取字符串
 * @param integer   $m
 * @param string    $strs
 * @return string
 */
if (!function_exists('randStr')) {
    function randStr($m = 6, $strs = '')
    {
        $new_str = '';
        if ($strs == '') {
            $strs   = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghizklmnopqrstuvwxyz0123456789';
        }
        $str = $strs;
        $max = strlen($str) - 1;
        for ($i = 1; $i <= $m; ++$i) {
            $new_str .= $str[mt_rand(0, $max)];
        }
        return $new_str;
    }
}

/**
 * 自动生成url
 * @param string $path
 * @param array $params
 * @return string
 */
if (!function_exists('autourl')) {
    function autourl($path, $params = [])
    {

        if (empty($params)) {
            return $path;
        }
        foreach ($params as $key => $value) {
            if (is_array($value) && empty($value)) {
                unset($params[$key]);
            }
        }

        $parsedUrl  = parse_url($path);
        $hosturl = '';

        // 检查是否有scheme和host
        if (isset($parsedUrl['scheme']) && isset($parsedUrl['host'])) {
        } else {
            $hosturl = hosturl();
        }

        $query = [];
        build_query($params, $query);
        $queryString = implode('&', $query);
        if (strpos($path, '?') !== false) {
            return $hosturl . $path . '&' . $queryString;
        } else {
            return $queryString == '' ? $hosturl . $path : $hosturl . $path . '?' . $queryString;
        }
    }
}

// 构建查询字符串
if (!function_exists('build_query')) {
    // 递归函数将多维数组转换为查询字符串
    function build_query($params, &$query)
    {
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                build_query($value, $query);
            } else {
                if ($value === null || $value === '' || $value == 0 || $value === '0') {
                    continue; // 跳过值为 null 的参数   
                }
                $query[] = urlencode($key) . '=' . urlencode($value);
            }
        }
    }
}

//获取配置
if (!function_exists('confset_get')) {
    function confset_get($name, $model = 'system')
    {
        if (empty($name)) {
            return [];
        }
        $confset = Confset::where([
            ['name', '=', $name],
            ['model', '=', $model],
        ])->get();
        if ($confset) {
            $confset = $confset->toArray();
        }
        return $confset ?? [];
    }
}
/**
 * 设置配置
 * @param array $array 分组数据
 * @param array $data 配置数据
 * @param string $model 模型
 * @return bool
 */
if (!function_exists('confset_set')) {
    function confset_set($group, $data, $model = 'system')
    {
        if (empty($group) || !is_array($group) || empty($data)) {
            return false;
        }
        $group_fields = ['model', 'name', 'label'];
        $data_fields = ['model', 'name', 'label', 'label_width', 'desc', 'type', 'key', 'value', 'options', 'dict', 'span', 'sort', 'hidden', 'props', 'disabled', 'status'];
        $array_group = [];
        foreach ($group_fields as $field) {
            if (isset($group[$field])) {
                $array_group[$field] = $group[$field];
            }
        }
        //判断数组是否为空
        if (isEmpty2DArray($array_group)) {
            return false;
        }
        $array_data = [];
        foreach ($data_fields as $field) {
            if (isset($data[$field])) {
                $array_data[$field] = $data[$field];
            }
        }
        //判断数组是否为空
        if (isEmpty2DArray($array_data)) {
            return false;
        }
        ConfsetGroup::updateOrInsert(
            ['name' => $array_group['name']],
            $array_group
        );
        Confset::updateOrInsert(
            ['name' => $array_data['name'], 'model' => $array_group['model'], 'key' => $array_data['key']],
            $array_data
        );
        return true;
    }
}


/**
 * 分割sql文件
 * @param string $sql sql文件内容
 * @param string $delimiter 分隔符
 * @return array
 */
if (!function_exists('splitSqlFile')) {
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
}

/**
 * 检测表是否存在
 * @param string $tablename 表名
 * @return bool
 */
if (!function_exists('pdo_hasTable')) {
    function pdo_hasTable($tablename)
    {
        return Util::schema()->hasTable($tablename);
    }
}
/**
 * 检测表是否存在
 * @param string $tablename 表名
 * @param string $Column 列名
 * @return bool
 */
if (!function_exists('pdo_hasColumn')) {
    function pdo_hasColumn($tablename, $Column)
    {
        return Util::schema()->hasColumn($tablename, $Column);
    }
}
/**
 * 执行SQL语句
 * @param string $sql SQL语句
 * @return bool
 */
if (!function_exists('pdo_unprepared')) {
    function pdo_unprepared($sql)
    {
        $ret    = $sql;
        if (!empty($ret = splitSqlFile($ret, ";"))) {
            foreach ($ret as $k => $v) {
                Util::db()->unprepared($v);
            }
        } else {
            return Util::db()->unprepared($sql);
        }
        return true;
    }
}
/**
 * 表前缀
 * @param string $tablename 表名
 * @return string
 */
if (!function_exists('pdo_tablename')) {
    function pdo_tablename($tablename)
    {
        $prefix = config('plugin.webniu.database.connections.mysql.prefix');
        return $prefix . $tablename;
    }
}

/**
 * 转换一个安全路径
 * @param string $value 路径
 * @param string $default 默认值
 * @return string
 */
if (!function_exists('safe_gpc_path')) {
    function safe_gpc_path($value, $default = '')
    {
        $path = safe_gpc_string($value);
        $path = str_replace(array('..', '..\\', '\\\\', '\\', '..\\\\'), '', $path);

        if (empty($path) || $path != $value) {
            $path = $default;
        }

        return $path;
    }
}

/**
 * 转换一个安全字符串
 * @param string $value 字符串
 * @param string $default 默认值
 * @return string
 */
if (!function_exists('safe_gpc_string')) {
    function safe_gpc_string($value, $default = '')
    {
        $value = safe_bad_str_replace($value);
        $value = preg_replace('/&((#(\d{3,5}|x[a-fA-F0-9]{4}));)/', '&\\1', $value);

        if (empty($value) && $default != $value) {
            $value = $default;
        }

        return $value;
    }
}

/**
 * 转换一个安全数字
 * @param string $value 数字
 * @param int $default 默认值
 * @return int|float
 */
if (!function_exists('safe_gpc_int')) {
    function safe_gpc_int($value, $default = 0)
    {
        if (false !== strpos($value, '.')) {
            $value = floatval($value);
            $default = floatval($default);
        } else {
            $value = intval($value);
            $default = intval($default);
        }

        if (empty($value) && $default != $value) {
            $value = $default;
        }

        return $value;
    }
}

/**
 * 转换一个安全数组
 * @param array $value 数组
 * @param array $default 默认值
 * @return array
 */
if (!function_exists('safe_gpc_array')) {
    function safe_gpc_array($value, $default = array())
    {
        if (empty($value) || !is_array($value)) {
            return $default;
        }
        foreach ($value as &$row) {
            if (is_numeric($row)) {
                $row = safe_gpc_int($row);
            } elseif (is_array($row)) {
                $row = safe_gpc_array($row, $default);
            } else {
                $row = safe_gpc_string($row);
            }
        }

        return $value;
    }
}

/**
 * 转换一个安全布尔值
 * @param string $value 布尔值
 * @return bool
 */
if (!function_exists('safe_gpc_boolean')) {
    function safe_gpc_boolean($value)
    {
        return boolval($value);
    }
}

/**
 * 过滤掉一些不安全的字符串
 * @param string $string 字符串
 * @return string
 */
if (!function_exists('safe_bad_str_replace')) {
    function safe_bad_str_replace($string)
    {
        if (empty($string)) {
            return '';
        }
        $badstr = array("\0", '%00', '%3C', '%3E', '<?', '<%', '<?php', '{php', '{if', '{loop', '../', '%0D%0A');
        $newstr = array('_', '_', '&lt;', '&gt;', '_', '_', '_', '_', '_', '_', '.._', '_');
        $string = str_replace($badstr, $newstr, $string);

        return $string;
    }
}

/**
 * 比较版本号
 * @param string $version1 版本号1
 * @param string $version2 版本号2
 * @param string $operator 比较运算符：=, ==, !=, <>, <, <=, >, >=
 * @return bool
 */
function version_compare_custom(string $version1, string $version2, string $operator = '>='): bool
{
    return version_compare($version1, $version2, $operator);
}

// 转换为媒体URL
if (!function_exists('tomedia')) {
    function tomedia($imagePath)
    {
        // 解析URL以检查协议和主机是否存在
        $parsedUrl = parse_url($imagePath);

        // 检查是否有scheme和host
        if (isset($parsedUrl['scheme']) && isset($parsedUrl['host'])) {
            // URL是完整的，直接返回
            return $imagePath;
        } else {
            // URL不完整，使用hosturl()拼接
            $baseUrl = hosturl();
            // 确保baseUrl以斜杠结尾
            if (substr($baseUrl, -1) !== '/') {
                $baseUrl .= '/';
            }
            if (strpos($imagePath, '\\') !== false) {
                $imagePath = str_replace('\\', '/', $imagePath);
            }
            // 去除imagePath开头的斜杠以避免双斜杠
            $relativePath = ltrim($imagePath, '/');
            return $baseUrl . $relativePath;
        }
    }
}

/**
 * 渲染公共模板,读取vue模板
 * @param string $template 模板路径，例如：'admin/dist/index'
 * @param array $vars 变量数组
 * @return Response
 */
if (!function_exists('public_view')) {
    function public_view(string $template, array $vars = [], string $plugin = 'webniu'): Response
    {
        $path = base_path() . "/plugin/$plugin/public/" . ltrim($template, '/');
        if (!str_ends_with($path, '.html')) {
            $path .= '.html';
        }
        if (!is_file($path)) {
            throw new \support\exception\BusinessException("模板文件不存在：$path", 404);
        }
        $content = file_get_contents($path);
        // 如果传入变量，则执行 PHP 变量替换
        if ($vars) {
            $content = preg_replace_callback('/\{\{\s*\$(\w+)\s*\}\}/', function ($matches) use ($vars) {
                return $vars[$matches[1]] ?? '';
            }, $content);
        }
        return new Response(200, ['Content-Type' => 'text/html'], $content);
    }
}

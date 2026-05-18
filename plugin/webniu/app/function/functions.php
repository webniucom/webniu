<?php

/**
 * Here is your custom functions.
 */

use plugin\webniu\app\model\Admin;
use plugin\webniu\app\model\AdminRole;
use plugin\webniu\app\model\Option;
use support\Response;

/**
 * 当前管理员id
 * @return integer|null
 */
function admin_id(): ?int
{
    return session('admin.id');
}

/**
 * 当前管理员
 * @param null|array|string $fields
 * @return array|mixed|null
 * @throws Exception
 */
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


/**
 * 刷新当前管理员session
 * @param bool $force
 * @return void
 * @throws Exception
 */
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
    if ($admin['status'] != 0) {
        $session->forget('admin');
        return;
    }
    $admin['roles'] = AdminRole::where('admin_id', $admin_id)->pluck('role_id')->toArray();
    $admin['session_last_update_time'] = $time_now;
    $session->set('admin', $admin);
}

function admin_error_401_script(): Response
{
    return response(
        <<<EOF
<script>top.location.href = '/app/webniu';</script>
EOF
    );
}

/**
 * 下划线转小驼峰（数据库字段 => 接口字段）
 * @param string $str 下划线字符串 user_name
 * @return string 小驼峰 userName
 */
function to_camel_case(string $str): string
{
    // 把下划线后面的字母变大写，再删除下划线
    $str = ucwords(str_replace('_', ' ', $str));
    $str = str_replace(' ', '', lcfirst($str));

    return $str;
}

/**
 * 批量把数组key从下划线转小驼峰 arrayKeyToCamel
 * @param array $data
 * @return array
 */
function array_key_to_camel(array $data): array
{
    $result = [];
    foreach ($data as $key => $value) {
        $camelKey = to_camel_case($key);
        $result[$camelKey] = $value;
    }
    return $result;
}

/**
 * 小驼峰转下划线（接口字段 => 数据库字段）
 * @param string $str 小驼峰字符串 userName
 * @return string 下划线字符串 user_name
 */
function to_snake_case(string $str): string
{
    $str = preg_replace('/([A-Z])/', '_$1', $str);
    $str = strtolower($str);
    $str = ltrim($str, '_');

    return $str;
}

/**
 * 批量把数组key从小驼峰转下划线 arrayKeyToSnake
 * @param array $data
 * @return array
 */
function array_key_to_snake(array $data): array
{
    $result = [];
    foreach ($data as $key => $value) {
        $snakeKey = to_snake_case($key);
        $result[$snakeKey] = $value;
    }
    return $result;
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

/**
 * 处理配置文件options字段
 * @param array $str options字段
 * @return array
 */
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

/**
 * 判断是否安装向导完成
 * @return bool
 */
function is_install(): bool
{
    clearstatcache();
    if (!is_file(base_path('plugin/webniu/config/database.php'))) {
        return false;
    }
    return true;
}

/**
 * 判断是否二维数组是否为空
 * @param array $arr 二维数组
 * @return bool
 */
function isEmpty2DArray($arr) {
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
    return true;
}

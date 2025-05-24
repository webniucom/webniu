<?php
/**
 * Here is your custom functions.
 */

use plugin\webniu\app\model\Admin;
use plugin\webniu\app\model\AdminRole;
use plugin\webniu\app\model\AdminLog;
use plugin\webniu\app\model\Statistics;
use plugin\webniu\app\model\Option;
use plugin\webniu\app\common\Util; 

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

/**
 * 检测读写环境
 */
if (!function_exists('check_dirfile')) {
    function check_dirfile() 
    { 
        $success    = 'layui-icon-ok layui-font-blue';
        $error      = 'layui-icon-close layui-font-red';
        $items      = array(
            array('dir'=>'dir','write'=>$success,'read'=>$success, 'path'=>'/'),
            array('dir'=>'dir','write'=>$success,'read'=>$success, 'path'=>'/public'),
            array('dir'=>'dir','write'=>$success,'read'=>$success, 'path'=>'/runtime'),
            array('dir'=>'dir','write'=>$success,'read'=>$success, 'path'=>'/plugin/webniu/app'),
            array('dir'=>'dir','write'=>$success,'read'=>$success, 'path'=>'/plugin/webniu/config'),
            array('dir'=>'dir','write'=>$success,'read'=>$success, 'path'=>'/plugin/webniu/public/upload'),
        );

        foreach ($items as &$value) {
            $item = base_path().$value['path'];  
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
 * 随机获取字符串
 * @param integer   $m
 * @param string    $strs
 * @return string
 */
if (!function_exists('randStr')) {
    function randStr($m=6,$strs=''){
        $new_str = '';
        if($strs==''){
            $strs   = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghizklmnopqrstuvwxyz0123456789';
        }
		$str = $strs;
		$max=strlen($str)-1;
		for ($i = 1; $i <= $m; ++$i) {
			$new_str .=$str[mt_rand(0, $max)];
		}
		return $new_str;
    }
}

//读取配置
if (!function_exists('options')) {
    function options(array $array = [], $model = null): array
    {
        $output = [];
        
        // 设置默认模型值
        $defaultModel = 'webniu';
        $model = $model ?? $defaultModel;

        foreach ($array as $v) {
            // 构建查询条件
            $where = [
                ['model', '=', $model],
                ['name', '=', 'system_' . $v]
            ];

            // 获取配置值
            $config = Option::where($where)->value('value');

            // 解析并存储结果
            if (!empty($config)) {
                $output[$v] = json_decode($config, true);
            } else {
                $output[$v] = null;
            }
        }

        return $output;
    }

}

//写入日志
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

function getDirectorySize($dir) {
    $size = 0;
    // 创建 RecursiveDirectoryIterator 对象
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $size += $file->getSize();
        }
    }
    return $size;
}

function formatSizeUnits($bytes) {
    if ($bytes >= 1099511627776) {
        $bytes = ($bytes / 1099511627776);
        return round($bytes, 2) . ' TB';
    } elseif ($bytes >= 1073741824) {
        $bytes = ($bytes / 1073741824);
        return round($bytes, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        $bytes = ($bytes / 1048576);
        return round($bytes, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        $bytes = ($bytes / 1024);
        return round($bytes, 2) . ' KB';
    } elseif ($bytes > 1) {
        return $bytes . ' bytes';
    } elseif ($bytes == 1) {
        return $bytes . ' byte';
    } else {
        return '0 bytes';
    }
}
//统计数据
if (!function_exists('hosturl')) {
    function hosturl()
    {   
        $request    = request();
        return $request->header('x-forwarded-proto')."://".$request->host();
    }

}

//统计数据
if (!function_exists('statistics')) {
    function statistics($model='webniu'): bool
    {
        $AdminLog   = new Statistics;
        $timestamp  = date("Y-m-d", time());
        if(!$AdminLog->where('model', $model)->whereDate('created_at', $timestamp)->increment('count')){
            $AdminLog->model       = $model;
            $AdminLog->count       = 1;
            $AdminLog->created_at  = $timestamp;
            $AdminLog->save();
        };
        return true;
    }

}

//分割SQL语句
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
    
//检测表是否纯在
if (!function_exists('pdo_hasTable')) {
    function pdo_hasTable($tablename){
        return Util::schema()->hasTable($tablename);
    }
}
//检测表 / 列是否存在
if (!function_exists('pdo_hasColumn')) {
    function pdo_hasColumn($tablename,$Column){
        return Util::schema()->hasColumn($tablename,$Column);
    }
}
//执行SQL
if (!function_exists('pdo_unprepared')) {
    function pdo_unprepared($sql){
        $ret    = $sql;
        if(!empty($ret = splitSqlFile($ret,";"))){
            foreach($ret as $k=>$v){
                Util::db()->unprepared($v);
            }
        }else{
            return Util::db()->unprepared($sql);
        }
        return true;
    }
}
//表前缀
if (!function_exists('pdo_tablename')) {
    function pdo_tablename($tablename){
        $prefix = config('plugin.webniu.database.connections.mysql.prefix');
        return $prefix.$tablename;
    }    
}

//转换一个安全路径
if (!function_exists('safe_gpc_path')) {
    function safe_gpc_path($value, $default = '') {
        $path = safe_gpc_string($value);
        $path = str_replace(array('..', '..\\', '\\\\', '\\', '..\\\\'), '', $path);
    
        if (empty($path) || $path != $value) {
            $path = $default;
        }
    
        return $path;
    }
}

//转换一个安全字符串
if (!function_exists('safe_gpc_string')) {
	function safe_gpc_string($value, $default = '') {
        $value = safe_bad_str_replace($value);
        $value = preg_replace('/&((#(\d{3,5}|x[a-fA-F0-9]{4}));)/', '&\\1', $value);
    
        if (empty($value) && $default != $value) {
            $value = $default;
        }
    
        return $value;
    }
}

//转换一个安全数字
if (!function_exists('safe_gpc_int')) {
    function safe_gpc_int($value, $default = 0) {
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

//转换一个安全的字符串型数组
if (!function_exists('safe_gpc_array')) {
    function safe_gpc_array($value, $default = array()) {
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

//转换一个安全的布尔值
if (!function_exists('safe_gpc_boolean')) {
    function safe_gpc_boolean($value) {
        return boolval($value);
    }   
}

//过滤掉一些不安全的字符串
if (!function_exists('safe_bad_str_replace')) {
    function safe_bad_str_replace($string) {
        if (empty($string)) {
            return '';
        }
        $badstr = array("\0", '%00', '%3C', '%3E', '<?', '<%', '<?php', '{php', '{if', '{loop', '../', '%0D%0A');
        $newstr = array('_', '_', '&lt;', '&gt;', '_', '_', '_', '_', '_', '_', '.._', '_');
        $string = str_replace($badstr, $newstr, $string);
    
        return $string;
    }  
}
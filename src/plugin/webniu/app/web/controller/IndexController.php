<?php

namespace plugin\webniu\app\web\controller;

use plugin\webniu\app\common\Util;
use plugin\webniu\app\model\Option;
use plugin\webniu\app\model\User;
use plugin\webniu\app\model\Admin;
use plugin\webniu\app\model\Plugin;
use plugin\webniu\app\model\Statistics;
use support\exception\BusinessException;
use support\think\Cache;
use support\Request;
use support\Response;
use think\db\Where;
use Throwable;
use Workerman\Worker;

class IndexController
{

    /**
     * 无需登录的方法
     * @var string[]
     */
    protected $noNeedLogin = ['index'];

    /**
     * 不需要鉴权的方法
     * @var string[]
     */
    protected $noNeedAuth = ['dashboard','readata'];

    /**
     * 后台主页
     * @param Request $request
     * @return Response
     * @throws BusinessException|Throwable
     */
    public function index(Request $request): Response
    {
        clearstatcache();
        if (!is_file(base_path('plugin/webniu/config/database.php'))) {
            return raw_view('index/install');
        }
        $admin = admin();
        if (!$admin) {
            $config = options(['base','user']); 
            $config['get']  = $request->get();
            if(!isset($config['get']['type'])){
                $config['get']['type']      = 'login';
                $config['get']['version']   = config('plugin.webniu.app.version');
            }   
            return view('account/login',['data'=>$config]);
        }
        // 统计访问数据
        statistics();
        return raw_view('index/index');
    }

    /**
     * 仪表板
     * @param Request $request
     * @return Response
     * @throws Throwable
     */
    public function dashboard(Request $request): Response
    {
        // mysql版本
        $version = Util::db()->select('select VERSION() as version');
        $mysql_version = $version[0]->version ?? 'unknown'; 
        return view('index/dashboard', [
            'php_version'   => PHP_VERSION,
            'workerman_version' =>  Worker::VERSION,
            'webman_version'=> Util::getPackageVersion('workerman/webman-framework'),
            'admin_version' => config('plugin.webniu.app.version'),
            'mysql_version' => $mysql_version,
            'think_cache'   => Util::getPackageVersion('webman/think-cache'),
            'os' => PHP_OS,
        ]);
    }

    /**
     * 拉取数据
     * @param Request $request
     * @return Response
     * @throws Throwable
     */
    public function readata(Request $request): Response
    {
        $top_data   = [];
        $top_data['user_count']         = User::count();
        $top_data['admin_count']        = Admin::count();
        $top_data['stat_count']         = Statistics::where('model','webniu')->sum('count');
        $top_data['plugin_count']       = Plugin::count();
        $plugin = Plugin::where('installed','1')->select('plugin_identifier','plugin_name')->get();
        $series = [];
        $labels = [];
        $size   = [];
        $top_data['plugin']             = [];
        if($plugin){
            foreach($plugin as $key=>$val){
                $series[] = 1;
                $labels[] = $val->plugin_name;
                if($sizea = Cache::get('plugin_'.$val->plugin_identifier.'_size')){
                    $size[] = $sizea;
                }else{
                    $sizea = formatSizeUnits(getDirectorySize(base_path().'/plugin/'.$val->plugin_identifier));
                    Cache::set('plugin_'.$val->plugin_identifier.'_size',$sizea,60*60*24);
                    $size[] = $sizea;
                }   
            }
            $top_data['plugin'] = [
                'series' => $series,
                'labels' => $labels,
                'size'   => $size,
            ];
        }
        // mysql版本
        $version = Util::db()->select('select VERSION() as version');
        $mysql_version = $version[0]->version ?? 'unknown';

        $day15_series = [];
        $day15_labels = [];
        $now = time();
        for ($i = 0; $i < 15; $i++) {
            $date = date('Y-m-d', $now - 24 * 60 * 60 * $i);
            $day15_series[] = Statistics::where('model','=',"webniu")->where('created_at', '=', "$date 00:00:00")->sum('count');
            $day15_labels[] = substr($date, 5);
        }
        $top_data['day15_detail']   = [
            'series' => array_reverse($day15_series),
            'labels' => array_reverse($day15_labels),
        ];
        return json(['code' => 0, 'data' => $top_data, 'msg' => 'ok']);

    }

}

<?php

namespace plugin\webniu\app\admin\controller;

use plugin\webniu\app\common\Util;
use plugin\webniu\app\model\User;
use plugin\webniu\app\model\Admin;
use plugin\webniu\app\model\Plugin;
use plugin\webniu\app\model\Statistics;
use support\exception\BusinessException;
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
    protected $noNeedAuth = ['dashboard'];

    /**
     * 后台主页
     * @param Request $request
     * @return Response
     * @throws BusinessException|Throwable
     */
    public function index(Request $request): Response
    {
        if (is_file(base_path('plugin/webniu/config/database.php'))) {
            statistics();
        }
        return public_view('admin/dist/index', [
            'confit_url' => '/webniu/admin/config',
            'worktab' => 'webniu',
        ]);
    }

    /**
     * 仪表板
     * @param Request $request
     * @return Response
     * @throws Throwable
     */
    public function dashboard(Request $request): Response
    {
        $top_data   = [];
        $version = Util::db()->select('select VERSION() as version');
        $mysql_version = $version[0]->version ?? 'unknown';
        $top_data['version'] =  [
            'php_version'   => PHP_VERSION,
            'workerman_version' =>  Worker::VERSION,
            'webman_version' => Util::getPackageVersion('workerman/webman-framework'),
            'admin_version' => config('plugin.webniu.app.version'),
            'mysql_version' => $mysql_version,
            'think_cache'   => Util::getPackageVersion('webman/think-cache'),
            'os' => PHP_OS,
        ];
        $top_data['user_num']         = User::count();
        $top_data['manage_num']        = Admin::count();
        $top_data['page_num']         = Statistics::where('model', 'webniu')->sum('count');
        $top_data['plugin_num']       = Plugin::count();
        $plugin = Plugin::where('installed', '1')->select('identifier', 'name')->get();
        $top_data['plugin']             = [];
        if ($plugin) {
            $count = count($plugin);
            foreach ($plugin as $key => $val) {
                $top_data['plugin'][] = [
                    'value' => intval(100 / $count),
                    'name' => $val->name,
                ];
            }
        }
        $day15_series = [];
        $day15_labels = [];
        $now = time();
        for ($i = 0; $i < 15; $i++) {
            $date = date('Y-m-d', $now - 24 * 60 * 60 * $i);
            $day15_series[] = Statistics::where('model', '=', "webniu")->where('created_at', '=', "$date 00:00:00")->sum('count');
            $day15_labels[] = substr($date, 5);
        }
        $top_data['day15_detail']   = [
            'series' => array_reverse($day15_series),
            'labels' => array_reverse($day15_labels),
        ];
        return json(['code' => 200, 'data' => $top_data, 'msg' => 'ok']);
    }
}

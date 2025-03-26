<?php

namespace plugin\webniu\app\mobile\controller;

use support\exception\BusinessException;
use plugin\webniu\app\web\controller\Base; 
use support\Request;
use support\Response;
use Throwable;

class IndexController extends Base
{

    protected $model = null;
    /**
     * 无需登录的方法
     * @var string[]
     */
    protected $noNeedLogin = ['index'];

    /**
     * 后台主页
     * @param Request $request
     * @return Response
     * @throws BusinessException|Throwable
     */
    public function index(Request $request): Response
    {
        return view('index/index');
    }

}

<?php

namespace plugin\webniu\app\mobile\controller;

use support\exception\BusinessException;
use support\Request;
use support\Response;
use Throwable;

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
        return view('index/index');
    }

     
}

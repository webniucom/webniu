<?php

namespace plugin\webniu\app\admin\controller;

use support\Request;
use support\Response;
use Throwable;

/**
 * 系统配置相关
 */
class WlhController extends Crud
{
    /**
     * 表单构建
     * @return Response
     * @throws Throwable
     */
    public function index()
    {
        return $this->json(200, '读取成功');
    }

}

<?php

namespace plugin\webniu\app\web\controller;

use plugin\webniu\app\model\User;
use support\exception\BusinessException;
use support\Request;
use support\Response;
use Throwable;

/**
 * 用户管理 
 */
class UserController extends Crud
{
    
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
        $this->model = new User;
    }

    /**
     * 浏览
     * @return Response
     * @throws Throwable
     */
    public function index(): Response
    {
        return raw_view('user/index');
    }

    /**
     * 插入
     * @param Request $request
     * @return Response
     * @throws BusinessException|Throwable
     */
    public function insert(Request $request): Response
    {
        if ($request->method() === 'POST') {
            $data   = $this->insertInput($request);
            $id     = $this->doInsert($data);
            return $this->json(0, 'ok', ['id' => $id]); 
        }

        return raw_view('user/insert');
    }
    
    protected function doInsert(array $data)
    {   
        $id = parent::doInsert($data);
        if($id){statistics('user');}
        return $id;
    }

    /**
     * 更新
     * @param Request $request
     * @return Response
     * @throws BusinessException|Throwable
     */
    public function update(Request $request): Response
    {
        if ($request->method() === 'POST') {
            return parent::update($request);
        }
        return raw_view('user/update');
    }

}

<?php

namespace plugin\webniu\app\web\controller;

use plugin\webniu\app\model\User;
use support\exception\BusinessException;
use support\Request;
use support\Response;
use Webman\Event\Event;
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
            // 注册广播事件
            Event::emit('user.insert',array_merge($data, ['id' => $id]));
            return $this->json(0, 'ok', ['id' => $id]); 
        }

        return raw_view('user/insert');
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
            [$id, $data] = $this->updateInput($request);
            $this->doUpdate($id, $data);
            Event::emit('user.update',array_merge($data, ['id'=>$id]));
            return $this->json(0);
        }
        return raw_view('user/update');
    }

    /**
     * 删除
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function delete(Request $request): Response
    {
        $ids = $this->deleteInput($request);
        $this->doDelete($ids);
        // 注册广播事件
        Event::emit('user.delete', $ids);
        return $this->json(0);
    }

}

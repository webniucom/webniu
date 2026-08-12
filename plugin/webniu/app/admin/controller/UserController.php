<?php

namespace plugin\webniu\app\admin\controller;

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
        return $this->json(200, '加载成功', [
            'config' => [
                'title' => '用户',
                'layout' => 'refresh,size,fullscreen,columns,settings',
            ],
        ]);
    }

    /**
     * 查询
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function select(Request $request): Response
    {
        [$where, $format, $limit, $field, $order] = $this->selectInput($request);
        if (!empty($where['username']) && is_string($where['username'])) {
            $where['username'] = ['like', "%{$where['username']}%"];
        }
        if (!empty($where['nickname']) && is_string($where['nickname'])) {
            $where['nickname'] = ['like', "%{$where['nickname']}%"];
        }
        $query = $this->doSelect($where, $field, $order);
        return $this->doFormat($query, $format, $limit);
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
            $data = $this->insertInput($request);
            $id = $this->doInsert($data);
            statistics('user');//统计注册量
            return $this->json(200, 'ok', ['id' => $id]);
        }
        return $this->json(400, '请求方法错误');
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
        return $this->json(400, '请求方法错误');
    }
}

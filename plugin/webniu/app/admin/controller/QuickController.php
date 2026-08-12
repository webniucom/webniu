<?php

namespace plugin\webniu\app\admin\controller;

use plugin\webniu\app\model\Quick;
use support\exception\BusinessException;
use plugin\webniu\app\common\RuleService;
use support\Request;
use support\Response;

/**
 * 权限菜单
 */
class QuickController extends Crud
{
    /**
     * @var Quick
     */
    protected $model = null;

    /**
     * 只返回当前管理员数据
     * @var string
     */
    protected $dataLimit = 'personal';

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->model = new Quick;
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
        if (!empty($where['title']) && is_string($where['title'])) {
            $where['title'] = ['like', "%{$where['title']}%"];
        }
        $query = $this->doSelect($where, $field, $order);
        return $this->doFormat($query, $format, $limit);
    }

    /**
     * 添加
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function insert(Request $request): Response
    {
        $data = $this->insertInput($request);
        $data['username'] = admin('username');
        $id = $this->doInsert($data);
        return $this->json(200, 'ok', ['id' => $id]);
    }

    /**
     * 更新
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function update(Request $request): Response
    {
        [$id, $data] = $this->updateInput($request);
        $this->doUpdate($id, $data);
        return $this->json(200);
    }

    /**
     * 执行更新
     * @param $id
     * @param $data
     * @return void
     */
    protected function doUpdate($id, $data)
    {
        $model = $this->model->find($id);
        if ($model->is_login_jump != $data['is_login_jump']) {
            $this->model->where([
                ['admin_id', '=', admin('id')]
            ])->update(['is_login_jump' => 0]);
        }
        foreach ($data as $key => $val) {
            $model->{$key} = $val;
        }
        $model->save();
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
        return $this->json(200);
    }

    /**
     * 选择菜单
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function rules(Request $request): Response
    {
        $menus = RuleService::getMenus(admin('roles'), '0,1,2');
        return $this->json(200, 'ok', $menus);
    }
}

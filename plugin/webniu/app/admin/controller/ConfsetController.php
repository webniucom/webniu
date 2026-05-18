<?php

namespace plugin\webniu\app\admin\controller;

use plugin\webniu\app\model\ConfsetGroup;
use plugin\webniu\app\model\Confset;
use support\exception\BusinessException;
use support\Request;
use support\Response;
use Throwable;

/**
 * 字典管理
 */
class ConfsetController extends Crud
{
    /**
     * @var DictGroup
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new Confset;
    }
    /**
     * 分组查询
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function select(Request $request): Response
    {
        [$where, $format, $limit, $field, $order] = $this->selectInput($request);
        $query = $this->doSelect($where, $field, $order);
        $query = $query->orderBy('sort', 'asc');
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
            if ($this->model->where([
                ['model', '=', $data['model']],
                ['label', '=', $data['label']],
                ['key', '=', $data['key']]
            ])->first()) {
                return $this->json(400, "配置字段已经存在");
            }
            $id = $this->doInsert($data);
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
            [$id, $data] = $this->updateInput($request);
            $model = $this->model->find($id);
            if ($model->key!=$data['key']) {
                if ($this->model->where([
                    ['model', '=', $data['model']],
                    ['label', '=', $data['label']],
                    ['key', '=', $data['key']]
                ])->first()) {
                    return $this->json(400, "配置字段已经存在");
                }
            }
            foreach ($data as $key => $val) {
                $model->{$key} = $val;
            }
            $model->save();
            return $this->json(200, '修改成功');
        }
        return $this->json(400, '请求方法错误');
    }

    /**
     * 分组查询
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function groupselect(Request $request): Response
    {
        $this->model = new ConfsetGroup;
        [$where, $format, $limit, $field, $order] = $this->selectInput($request);
        $query = $this->doSelect($where, $field, $order);
        $query = $query->orderBy('sort', 'asc');
        return $this->doFormat($query, $format, $limit);
    }

    /**
     * 通用格式化
     * @param $items
     * @param $total
     * @return Response
     */
    protected function formatNormal($items, $total): Response
    {
        return json(['code' => 200, 'msg' => 'success', 'data' => [
            'records' => $items,
            'total' => $total
        ]]);
    }

    /**
     * 分组插入
     * @param Request $request
     * @return Response
     * @throws BusinessException|Throwable
     */
    public function groupinsert(Request $request): Response
    {
        $this->model = new ConfsetGroup;
        if ($request->method() === 'POST') {
            $data = $this->insertInput($request);
            $id = $this->doInsert($data);
            return $this->json(200, '插入成功', ['id' => $id]);
        }
        return $this->json(400, '请求方法错误');
    }

    /**
     * 分组更新
     * @param Request $request
     * @return Response
     * @throws BusinessException|Throwable
     */
    public function groupupdate(Request $request): Response
    {
        $this->model = new ConfsetGroup;
        if ($request->method() === 'POST') {
            if ($dragsort = $request->post('dragsort', false)) {
                if ($dragsort) {
                    foreach ($dragsort as $item) {
                        $this->model->where('id', $item['id'])->update(['sort' => $item['sort']]);
                    }
                }
                return $this->json(200, '排序成功');
            };
            [$id, $data] = $this->updateInput($request);
            $this->doUpdate($id, $data);
            return $this->json(200, '更新成功');
        }
        return $this->json(400, '请求方法错误');
    }

    /**
     * 分组删除
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function groupdelete(Request $request): Response
    {
        $this->model = new ConfsetGroup;
        $ids = $this->deleteInput($request);
        $this->doDelete($ids);
        return $this->json(200, '删除成功');
    }
}

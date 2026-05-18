<?php

namespace plugin\webniu\app\admin\controller;

use plugin\webniu\app\model\Dict;
use plugin\webniu\app\model\DictGroup;
use plugin\webniu\app\common\Tree;
use support\exception\BusinessException;
use support\Request;
use support\Response;
use Throwable;

/**
 * 字典管理
 */
class DictController extends Crud
{
    /**
     * 不需要授权的方法
     */
    protected $noNeedAuth = ['get'];

    /**
     * @var Dict
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new Dict;
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
        if (!empty($where['label']) && is_string($where['label'])) {
            $where['label'] = ['like', "%{$where['label']}%"];
        }
        $query = $this->doSelect($where, $field, $order);
        return $this->doFormat($query, $format, $limit);
    }

    /**
     * 添加
     * @param Request $request
     * @return Response
     * @throws BusinessException|Throwable
     */
    public function insert(Request $request): Response
    {
        if ($request->method() === 'POST') {
            return parent::insert($request);
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


    /**
     * 查询分组
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function groupselect(Request $request): Response
    {
        $this->model = new DictGroup;
        [$where, $format, $limit, $field, $order] = $this->selectInput($request);
        $query = $this->doSelect($where, $field, $order);
        return $this->doFormat($query, $format, $limit);
    }

    /**
     * 添加分组
     * @param Request $request
     * @return Response
     * @throws BusinessException|Throwable
     */
    public function groupinsert(Request $request): Response
    {
        $this->model = new DictGroup;
        if ($request->method() === 'POST') {
            $data = $this->insertInput($request);
            $id = $this->doInsert($data);
            return $this->json(200, '添加成功', ['id' => $id]);
        }
        return $this->json(400, '请求方法错误');
    }

    /**
     * 更新分组
     * @param Request $request
     * @return Response
     * @throws BusinessException|Throwable
     */
    public function groupupdate(Request $request): Response
    {
        $this->model = new DictGroup;
        if ($request->method() === 'POST') {
            [$id, $data] = $this->updateInput($request);
            $this->doUpdate($id, $data);
            return $this->json(200, '更新成功');
        }
        return $this->json(400, '请求方法错误');
    }

    /**
     * 删除分组
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function groupdelete(Request $request): Response
    {
        $this->model = new DictGroup;
        $ids = $this->deleteInput($request);
        $this->doDelete($ids);
        return $this->json(200, '删除成功');
    }

    /**
     * 获取
     * @param Request $request
     * @param $name
     * @return Response
     */
    public function get(Request $request, $name): Response
    {
        $dict = $this->model->orderBy('sort', 'asc')->where([
            'group' => $name,
            'status' => 1,
        ])->get()->toArray();
        if (!$dict) {
            return $this->json(404, '字典不存在');
        }
        $new_dict = [];
        foreach ($dict as $item) {
            $new_dict[] = [
                'id'    => $item['id'],
                'pid'   => $item['pid'],
                'label' => $item['label'],
                'value' => $item['value'],
                'disabled' => $item['disabled'] == 1 ? true : false,
            ];
        }
        $tree = new Tree($new_dict);
        $tree_items = $tree->getTree();
        return $this->json(200, 'ok',$tree_items);
    }
    

}

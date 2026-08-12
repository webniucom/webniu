<?php

namespace plugin\webniu\app\admin\controller;

use plugin\webniu\app\model\Dict;
use plugin\webniu\app\model\DictGroup;
use plugin\webniu\app\common\Tree;
use plugin\webniu\app\common\RuleService;
use plugin\webniu\app\model\Role;
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
     * 自定义格式化
     * @param $items
     * @param $total
     * @return Response
     */
    protected function formatCustom($items, $total): Response
    {
        $records = [
            [
                'id' => 0,
                'label' => '系统菜单',
                'value' => 'rules',
            ],
            [
                'id' => 0,
                'label' => '系统角色',
                'value' => 'roles',
            ],
        ];
        foreach ($items as $item) {
            $records[] = [
                'id' => $item['id'],
                'label' => $item['label'],
                'value' => $item['value'],
            ];
        }
        return json(['code' => 200, 'msg' => 'success', 'data' => [
            'records' => $records,
            'total' => $total
        ]]);
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
            $systemlabel = ['rules','roles'];
            $data = $this->insertInput($request);
            if (in_array($data['value'], $systemlabel)) {
                return $this->json(400, '['.$data['value'].']系统标签不能使用');
            }
            $group = $this->model->where('value', $data['value'])->first();
            if ($group) {
                return $this->json(400, '分组标签已存在');
            }
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
        $new_dict = [];
        $systemlabel = ['rules','roles']; 
        if (in_array($name, $systemlabel)) {
            if($name == 'rules'){
                $new_dict = RuleService::getMenus(admin('roles'));
                return $this->json(200, '读取成功', $new_dict);
            }
            if($name == 'roles'){
                $this->model = new Role;
                $new_dict = $this->model->get();
            }
        }else{
            $dict = $this->model->orderBy('sort', 'asc')->where([
                'group' => $name,
                'status' => 1,
            ])->get()->toArray();
            if (!$dict) {
                return $this->json(404, '字典不存在');
            }
            
            foreach ($dict as $item) {
                $new_dict[] = [
                    'id'    => $item['id'],
                    'pid'   => $item['pid'],
                    'label' => $item['label'],
                    'value' => $item['value'],
                    'disabled' => $item['disabled'] == 1 ? true : false,
                ];
            }
        }
         
        $tree = new Tree($new_dict);
        $tree_items = $tree->getTree();
        return $this->json(200, 'ok',$tree_items);
    }
    

}

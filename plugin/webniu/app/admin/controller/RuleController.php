<?php

namespace plugin\webniu\app\admin\controller;

use Exception;
use plugin\webniu\app\common\Tree;
use plugin\webniu\app\common\Util;
use plugin\webniu\app\model\Rule;
use plugin\webniu\app\model\Plugin;
use plugin\webniu\app\common\RuleService;
use support\exception\BusinessException;
use support\Request;
use support\Response;
use Throwable;

/**
 * 权限菜单
 */
class RuleController extends Crud
{
    /**
     * 不需要权限的方法
     *
     * @var string[]
     */
    protected $noNeedAuth = ['get'];

    /**
     * @var Rule
     */
    protected $model = null;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->model = new Rule;
    }

    /**
     * 浏览
     * @return Response
     * @throws Throwable
     */
    public function index(): Response
    {
        return raw_view('rule/index');
    }

    /**
     * 查询
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function select(Request $request): Response
    {
        $this->syncRules();
        [$where, $format, $limit, $field, $order] = $this->selectInput($request);
        if (!empty($where['title']) && is_string($where['title'])) {
            $where['title'] = ['like', "%{$where['title']}%"];
        }
        $query = $this->doSelect($where, $field, $order);
        return $this->doFormat($query, $format, $limit);
    }
    /**
     * 查询数据库后置方法，可用于修改数据
     * @param mixed $items 原数据
     * @return mixed 修改后数据
     */
    protected function afterQuery($items)
    {
        $formatted_items = [];
        foreach ($items as $item) {
            $meta = array_key_to_camel($item->toArray());
            $formatted_items[] = [
                'id'    => $item['id'],
                'pid'   => $item['pid'],
                'path'  => $item['path'],
                'name'  => $item['name'],
                'component' => $item['component'],
                'meta'  => $meta,
            ];
        }
        return $formatted_items;
    }
    /**
     * 格式化表格树
     * @param $items
     * @return Response
     */
    protected function formatTableTree($items): Response
    {
        $tree = new Tree($items);
        return $this->json(200, '完成', $tree->getTree());
    }
    /**
     * 临时查询
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    function get1(Request $request): Response
    {
        $types = $request->get('type', '0,1');
        $menus = RuleService::getMenus(admin('roles'), $types);
        return $this->json(200, '读取成功', $menus);
    }
    /**
     * 获取菜单
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    function get(Request $request): Response
    {
        $this->syncRules();
        $menus = RuleService::getMenus(admin('roles'));
        return $this->json(200, '读取成功', $menus);
    }

    /**
     * 根据类同步规则到数据库
     * @return void
     */
    protected function syncRules()
    {
        $items = $this->model->where('key', 'like', '%\\\\%')->get()->keyBy('key');
        $methods_in_db = [];
        $methods_in_files = [];
        foreach ($items as $item) {
            $class = $item->key;
            if (strpos($class, '@')) {
                $methods_in_db[$class] = $class;
                continue;
            }
            if (class_exists($class)) {
                $reflection = new \ReflectionClass($class);
                $properties = $reflection->getDefaultProperties();
                $no_need_auth = array_merge($properties['noNeedLogin'] ?? [], $properties['noNeedAuth'] ?? []);
                $use_auth = array_merge($properties['noAuthMark'] ?? []);
                $class = $reflection->getName();
                $pid = $item->id;
                $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
                foreach ($methods as $method) {
                    $method_name = $method->getName();
                    if (strtolower($method_name) === 'index' || strpos($method_name, '__') === 0 || in_array($method_name, $no_need_auth) || in_array($method_name, $use_auth)) {
                        continue;
                    }
                    $name = "$class@$method_name";

                    $methods_in_files[$name] = $name;
                    $title = Util::getCommentFirstLine($method->getDocComment()) ?: $method_name;
                    $menu = $items[$name] ?? [];
                    if ($menu) {
                        // if ($menu->title != $title) {
                        //     Rule::where('key', $name)->update([
                        //         'title' => $title,
                        //         'auth_mark' => $method_name
                        //     ]);
                        // }
                        continue;
                    }
                    $menu               = new Rule;
                    $menu->model        = $item->model;
                    $menu->menu         = $item->model == 'webniu' ? 0 : 1;
                    $menu->pid          = $pid;
                    $menu->auth_mark    = $method_name;
                    $menu->key          = $name;
                    $menu->title        = $title;
                    $menu->type         = 2;
                    $menu->is_enable    = 1;
                    $menu->save();
                }
            }
        }
        // 从数据库中删除已经不存在的方法
        $menu_names_to_del = array_diff($methods_in_db, $methods_in_files);
        if ($menu_names_to_del) {
            Rule::whereIn('key', $menu_names_to_del)->delete();
        }
    }

    /**
     * 查询前置方法
     * @param Request $request
     * @return array
     * @throws BusinessException
     */
    protected function selectInput(Request $request): array
    {
        [$where, $format, $limit, $field, $order] = parent::selectInput($request);
        // 允许通过type=0,1格式传递菜单类型
        $types = $request->get('type');
        if ($types && is_string($types)) {
            $where['type'] = ['in', explode(',', $types)];
        }
        // 默认sort排序
        if (!$field) {
            $field = 'sort';
            $order = 'desc';
        }
        return [$where, $format, $limit, $field, $order];
    }

    /**
     * 添加
     * @param Request $request
     * @return Response
     * @throws BusinessException|Throwable
     */
    public function insert(Request $request): Response
    {
        $data = $this->insertInput($request);
        if (empty($data['type'])) {
            $data['type'] = strpos($data['key'], '\\') ? 1 : 0;
        }
        $data['key'] = str_replace('\\\\', '\\', $data['key']);
        $key = $data['key'] ?? '';
        if ($this->model->where('key', $key)->first()) {
            return $this->json(400, "菜单标识 $key 已经存在");
        }
        $data['name'] = random_string(8, 'name_') . '_' . $data['path'];
        if ($this->model->where([
            ['pid', '=', $data['pid']],
            ['path', '=', $data['path']],
        ])->first()) {
            return $this->json(400, "路由名称 {$data['path']} 已经存在");
        }
        $data['pid'] = empty($data['pid']) ? 0 : $data['pid'];
        if($data['model'] != 'webniu'){
            $data['menu'] = 1;
        }
        $this->doInsert($data);
        return $this->json(200, '创建成功');
    }

    /**
     * 更新
     * @param Request $request
     * @return Response
     * @throws BusinessException|Throwable
     */
    public function update(Request $request): Response
    {
        [$id, $data] = $this->updateInput($request);
        if (!$row = $this->model->find($id)) {
            return $this->json(400, '记录不存在');
        }
        if (isset($data['pid'])) {
            $data['pid'] = $data['pid'] ?: 0;
            if ($data['pid'] == $row['id']) {
                return $this->json(400, '不能将自己设置为上级菜单');
            }
        }
        if ($data['path'] != $row['path']) {
            $data['name'] = random_string(8, 'name_') . '_' . $data['path'];
            if ($this->model->where([
                ['pid', '=', $data['pid']],
                ['path', '=', $data['path']],
            ])->first()) {
                return $this->json(400, "路由名称 {$data['path']} 已经存在");
            }
        } else {
            $data['name'] = $row['name'];
        }
        if ($data['key'] != $row['key']) {
            if ($this->model->where([
                ['key', '=', $data['key']],
            ])->first()) {
                return $this->json(400, "菜单标识 {$data['key']} 已经存在");
            }
        }

        if (isset($data['key'])) {
            $data['key'] = str_replace('\\\\', '\\', $data['key']);
        }
        $this->doUpdate($id, $data);
        return $this->json(200, '更新成功');
    }

    /**
     * 删除
     * @param Request $request
     * @return Response
     */
    public function delete(Request $request): Response
    {
        $ids = $this->deleteInput($request);
        // 子规则一起删除
        $delete_ids = $children_ids = $ids;
        while ($children_ids) {
            $children_ids = $this->model->whereIn('pid', $children_ids)->pluck('id')->toArray();
            $delete_ids = array_merge($delete_ids, $children_ids);
        }
        $this->doDelete($delete_ids);
        return $this->json(200, '删除成功');
    }

}

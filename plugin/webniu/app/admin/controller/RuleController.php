<?php

namespace plugin\webniu\app\admin\controller;

use Exception;
use plugin\webniu\app\common\Tree;
use plugin\webniu\app\common\Util;
use plugin\webniu\app\model\Role;
use plugin\webniu\app\model\Rule;
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
    protected $noNeedAuth = ['get', 'permission', 'menulist'];
    //protected $noNeedLogin = ['get'];

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
    function get1(Request $request): Response
    {
        $types = $request->get('type', '0,1');
        $types = is_string($types) ? explode(',', $types) : [0, 1];
        $items = Rule::orderBy('sort', 'desc')->get()->toArray();

        $formatted_items = [];
        foreach ($items as $item) {
            $meta = array_key_to_camel($item);
            $formatted_items[] = [
                'id'    => $item['id'],
                'pid'   => $item['pid'],
                'name'  => $item['name'],
                'path'  => $item['path'],
                'key'   => $item['key'],
                'title' => $item['title'],
                'type'  => $item['type'],
                'icon'  => $item['icon'],
                'component' => $item['component']
            ];
        }

        $tree = new Tree($formatted_items);
        $tree_items = $tree->getTree();
        $this->removeNotContain($tree_items, 'type', $types);
        $this->removeField($tree_items, ['id', 'pid','name']);
        print_r($tree_items);
        return $this->json(200, '读取成功', $tree_items);
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
        $rules = $this->getRules(admin('roles'));
        $types = $request->get('type', '0,1,2,3');
        $types = is_string($types) ? explode(',', $types) : [0, 1, 2, 3];
        $items = Rule::orderBy('sort', 'desc')->get()->toArray();

        $formatted_items = [];
        foreach ($items as $item) {
            $meta = array_key_to_camel($item);
            $formatted_items[] = [
                'id'    => $item['id'],
                'pid'   => $item['pid'],
                'type'  => $item['type'],
                'path'  => $item['path'],
                'name'  => $item['path'],
                'component' => $item['component'],
                'meta'  => $meta,
            ];
        }

        $tree = new Tree($formatted_items);
        $tree_items = $tree->getTree();
        // 超级管理员权限为 *
        if (!in_array('*', $rules)) {
            $this->removeNotContain($tree_items, 'id', $rules);
        }
        $this->removeNotContain($tree_items, 'type', $types);
        $tree_items = $this->processAuthList($tree_items);
        $menus = $this->empty_filter(Tree::arrayValues($tree_items));
        return $this->json(200, '读取成功', $menus);
    }


    /**
     * 递归处理树结构，菜单项
     * @param array $tree
     * @return array
     */
    protected function processAuthList(array $tree): array
    {
        foreach ($tree as &$node) {
            $authList = [];
            $children = [];

            // 处理子节点
            if (isset($node['children']) && is_array($node['children'])) {
                foreach ($node['children'] as $child) {
                    if (isset($child['type']) && $child['type'] == 2) {
                        // type=2的节点放入authList
                        $authList[] = [
                            'title' => $child['meta']['title'],
                            'authMark' => $child['meta']['authMark'],
                        ];
                    } else {
                        // type!=2的节点先递归处理，然后放入children
                        $processedChild = $this->processAuthList([$child]);
                        $children[] = $processedChild[0];
                    }
                }
            }

            // 设置authList和children
            if (!empty($authList)) {
                $node['meta']['authList'] = $authList;
            }
            if (!empty($children)) {
                $node['children'] = $children;
            } else {
                unset($node['children']);
            }
        }

        return $tree;
    }

    /**
     * 获取菜单
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    function menulist(Request $request): Response
    {
        $menus = [
            [
                'name' => 'Dashboard',
                'path' => '/dashboard',
                'component' => '/index/index',
                'meta' => [
                    'title' => 'menus.dashboard.title',
                    'icon' => 'ri:pie-chart-line'
                ],
                'children' => [
                    [
                        'path' => 'console',
                        'name' => 'Console',
                        'component' => '/dashboard/console',
                        'meta' => [
                            'title' => 'menus.dashboard.console',
                            'icon' => 'ri:home-smile-2-line',
                            'keepAlive' => false,
                            'fixedTab' => true
                        ]
                    ]
                ]
            ],
            [
                'path' => '/system',
                'name' => 'System',
                'component' => '/index/index',
                'meta' => [
                    'title' => 'menus.system.title',
                    'icon' => 'ri:user-3-line'
                ],
                'children' => [
                    [
                        'path' => 'user',
                        'name' => 'User',
                        'component' => '/system/user',
                        'meta' => [
                            'title' => 'menus.system.user',
                            'icon' => 'ri:user-line',
                            'keepAlive' => true,
                            'roles' => ['R_SUPER', 'R_ADMIN']
                        ]
                    ],
                    [
                        'path' => 'role',
                        'name' => 'Role',
                        'component' => '/system/role',
                        'meta' => [
                            'title' => 'menus.system.role',
                            'icon' => 'ri:user-settings-line',
                            'keepAlive' => true,
                            'roles' => ['R_SUPER']
                        ]
                    ],
                    [
                        'path' => 'user-center',
                        'name' => 'UserCenter',
                        'component' => '/system/user-center',
                        'meta' => [
                            'title' => 'menus.system.userCenter',
                            'icon' => 'ri:user-line',
                            'isHide' => true,
                            'keepAlive' => true,
                            'isHideTab' => true
                        ]
                    ],
                    [
                        'path' => 'menu',
                        'name' => 'Menus',
                        'component' => '/system/menu',
                        'meta' => [
                            'title' => 'menus.system.menu',
                            'icon' => 'ri:menu-line',
                            'keepAlive' => true,
                            'roles' => ['R_SUPER'],
                            'authList' => [
                                [
                                    'title' => '新增',
                                    'authMark' => 'add'
                                ],
                                [
                                    'title' => '编辑',
                                    'authMark' => 'edit'
                                ],
                                [
                                    'title' => '删除',
                                    'authMark' => 'delete'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            [
                'path' => '/result',
                'name' => 'Result',
                'component' => '/index/index',
                'meta' => [
                    'title' => 'menus.result.title',
                    'icon' => 'ri:checkbox-circle-line'
                ],
                'children' => [
                    [
                        'path' => 'success',
                        'name' => 'ResultSuccess',
                        'component' => '/result/success',
                        'meta' => [
                            'title' => 'menus.result.success',
                            'icon' => 'ri:checkbox-circle-line',
                            'keepAlive' => false
                        ]
                    ],
                    [
                        'path' => 'fail',
                        'name' => 'ResultFail',
                        'component' => '/result/fail',
                        'meta' => [
                            'title' => 'menus.result.fail',
                            'icon' => 'ri:close-circle-line',
                            'keepAlive' => false
                        ]
                    ]
                ]
            ],
            [
                'path' => '/exception',
                'name' => 'Exception',
                'component' => '/index/index',
                'meta' => [
                    'title' => 'menus.exception.title',
                    'icon' => 'ri:error-warning-line'
                ],
                'children' => [
                    [
                        'path' => '403',
                        'name' => '403',
                        'component' => '/exception/403',
                        'meta' => [
                            'title' => 'menus.exception.forbidden',
                            'keepAlive' => true,
                            'isFullPage' => true
                        ]
                    ],
                    [
                        'path' => '404',
                        'name' => '404',
                        'component' => '/exception/404',
                        'meta' => [
                            'title' => 'menus.exception.notFound',
                            'keepAlive' => true,
                            'isFullPage' => true
                        ]
                    ],
                    [
                        'path' => '500',
                        'name' => '500',
                        'component' => '/exception/500',
                        'meta' => [
                            'title' => 'menus.exception.serverError',
                            'keepAlive' => true,
                            'isFullPage' => true
                        ]
                    ]
                ]
            ]
        ];

        return $this->json(200, 'ok', $menus);
    }

    private function empty_filter($menus)
    {
        return array_map(
            function ($menu) {
                if (isset($menu['children'])) {
                    $menu['children'] = $this->empty_filter($menu['children']);
                }
                return $menu;
            },
            array_values(array_filter(
                $menus,
                function ($menu) {
                    return $menu['type'] != 0 || isset($menu['children']) && count($this->empty_filter($menu['children'])) > 0;
                }
            ))
        );
    }

    /**
     * 获取权限
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function permission(Request $request): Response
    {
        $rules = $this->getRules(admin('roles'));
        // 超级管理员
        if (in_array('*', $rules)) {
            return $this->json(0, 'ok', ['*']);
        }
        $keys = Rule::whereIn('id', $rules)->pluck('key');
        $permissions = [];
        foreach ($keys as $key) {
            if (!$key = Util::controllerToUrlPath($key)) {
                continue;
            }
            $code = str_replace('/', '.', trim($key, '/'));
            $permissions[] = $code;
        }
        return $this->json(0, 'ok', $permissions);
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
                $class = $reflection->getName();
                $pid = $item->id;
                $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
                foreach ($methods as $method) {
                    $method_name = $method->getName();
                    if (strtolower($method_name) === 'index' || strpos($method_name, '__') === 0 || in_array($method_name, $no_need_auth)) {
                        continue;
                    }
                    $name = "$class@$method_name";

                    $methods_in_files[$name] = $name;
                    $title = Util::getCommentFirstLine($method->getDocComment()) ?: $method_name;
                    $menu = $items[$name] ?? [];
                    if ($menu) {
                        if ($menu->title != $title) {
                            Rule::where('key', $name)->update([
                                //'title' => $title,
                                'auth_mark' => $method_name
                            ]);
                        }
                        continue;
                    }
                    $menu           = new Rule;
                    $menu->pid      = $pid;
                    $menu->auth_mark = $method_name;
                    $menu->key      = $name;
                    $menu->title    = $title;
                    $menu->type     = 2;
                    $menu->is_enable = 1;
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
        print_r($data);
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
        print_r($data);
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

    /**
     * 删除数组中指定的字段（包括子数组中的）
     * @param array $array
     * @param string $key
     * @return void
     */
    protected function removeField(&$array, $keys)
    {
        foreach ($array as &$item) {
            if (!is_array($item)) {
                continue;
            }
            foreach ($keys as $key) {
                if (isset($item[$key])) {
                    unset($item[$key]);
                }
            }
            // 递归处理子数组
            if (isset($item['children']) && is_array($item['children'])) {
                $this->removeField($item['children'], $keys);
            }
        }
    }

    /**
     * 移除不包含某些数据的数组
     * @param $array
     * @param $key
     * @param $values
     * @return void
     */
    protected function removeNotContain(&$array, $key, $values)
    {
        foreach ($array as $k => &$item) {
            if (!is_array($item)) {
                continue;
            }
            if (!$this->arrayContain($item, $key, $values)) {
                unset($array[$k]);
            } else {
                if (!isset($item['children'])) {
                    continue;
                }
                $this->removeNotContain($item['children'], $key, $values);
            }
        }
    }

    /**
     * 判断数组是否包含某些数据
     * @param $array
     * @param $key
     * @param $values
     * @return bool
     */
    protected function arrayContain(&$array, $key, $values): bool
    {
        if (!is_array($array)) {
            return false;
        }
        if (isset($array[$key]) && in_array($array[$key], $values)) {
            return true;
        }
        if (!isset($array['children'])) {
            return false;
        }
        foreach ($array['children'] as $item) {
            if ($this->arrayContain($item, $key, $values)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 获取权限规则
     * @param $roles
     * @return array
     */
    protected function getRules($roles): array
    {
        $rules_strings = $roles ? Role::whereIn('id', $roles)->pluck('rules') : [];
        $rules = [];
        foreach ($rules_strings as $rule_string) {
            if (!$rule_string) {
                continue;
            }
            $rules = array_merge($rules, explode(',', $rule_string));
        }
        return $rules;
    }
}

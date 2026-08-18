<?php

namespace plugin\webniu\app\common;

use plugin\webniu\app\model\Rule;
use plugin\webniu\app\model\Role;
use plugin\webniu\app\common\Tree;

class RuleService
{
    /**
     * 获取权限菜单
     * @param array $roles
     * @param string $types
     * @param array $where
     * @param string $plugin
     * @return array
     */
    public static function getMenus(array $roles, $types = '0,1,2,3', $where = [['menu', '=', 0]],$plugin = 'webniu')
    {
        $rules = self::getRules($roles);
        $types = is_string($types) ? explode(',', $types) : [0, 1, 2, 3];
        $items = Rule::where($where)->orderBy('sort', 'desc')->get()->toArray();
        $formatted_items = [];
        foreach ($items as $item) {
            if ($item['is_iframe']) {
                $item['is_iframe'] = true;
            }
            $item['plugin'] = $item['open'] == 0 ? $plugin : $item['plugin'];
            $meta = array_key_to_camel($item);
            $formatted_items[] = [
                'id'    => $item['id'],
                'title' => $item['title'],
                'model' => $item['model'],
                'pid'   => $item['pid'],
                'type'  => $item['type'],
                'path'  => $item['path'],
                'name'  => $item['name'] ?: $item['path'],
                'component' => $item['component'],
                'meta'  => $meta,
            ];
        }
        $tree = new Tree($formatted_items);
        $tree_items = $tree->getTree();
        if (!in_array('*', $rules)) {
            self::removeNotContain($tree_items, 'id', $rules);
        }
        self::removeNotContain($tree_items, 'type', $types);
        $tree_items = self::processAuthList($tree_items);
        $menus = self::empty_filter(Tree::arrayValues($tree_items));
        return $menus;
    }

    /**
     * 移除不包含某些数据的数组
     * @param $array
     * @param $key
     * @param $values
     * @return void
     */
    protected static function removeNotContain(&$array, $key, $values)
    {
        foreach ($array as $k => &$item) {
            if (!is_array($item)) {
                continue;
            }
            if (!self::arrayContain($item, $key, $values)) {
                unset($array[$k]);
            } else {
                if (!isset($item['children'])) {
                    continue;
                }
                self::removeNotContain($item['children'], $key, $values);
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
    protected static function arrayContain(&$array, $key, $values): bool
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
            if (self::arrayContain($item, $key, $values)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 递归处理树结构，菜单项
     * @param array $tree
     * @return array
     */
    protected static function processAuthList(array $tree): array
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
                        $processedChild = self::processAuthList([$child]);
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
     * 过滤空菜单
     * @param $menus
     * @return array
     */
    private static function empty_filter($menus)
    {
        return array_map(
            function ($menu) {
                if (isset($menu['children'])) {
                    $menu['children'] = self::empty_filter($menu['children']);
                }
                return $menu;
            },
            array_values(array_filter(
                $menus,
                function ($menu) {
                    return $menu['type'] != 0 || isset($menu['children']) && count(self::empty_filter($menu['children'])) > 0;
                }
            ))
        );
    }

    /**
     * 获取权限规则
     * @param $roles
     * @return array
     */
    protected static function getRules($roles): array
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

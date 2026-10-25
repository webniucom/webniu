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
            $pluginName = $item['plugin'] ?? '';
            // ---- 微前端探测：仅子应用需要 remoteEntry，基座 webniu 直接跳过 ----
            // 每个菜单项严格用自己的 pluginName 查，绝不扫全目录
            // （否则 A 插件的 remoteEntry 会被误分配给 B 插件的菜单项 → 路由串台）
            $remoteEntry = '';
            if ($pluginName && $pluginName !== 'webniu') {
                // DB typo 纠正：case-insensitive 匹配 pluginName 与真实目录名
                $realName = $pluginName;
                $pluginDir = base_path('plugin');
                if (is_dir($pluginDir)) {
                    foreach (scandir($pluginDir) ?: [] as $dir) {
                        if ($dir === '.' || $dir === '..') continue;
                        if (strcasecmp($dir, $pluginName) === 0) {
                            $realName = $dir;
                            break;
                        }
                    }
                }
                // 优先级 1：子应用自己的 config/app.php 里 mfe_dev_server
                //   true           → 自动拼 dev server 地址（用 mfe_dev_port，默认 5174）
                //   false / 空     → 跳过 dev server，走生产构建包
                //   URL 字符串     → 直接用（向后兼容旧写法）
                $devServerRaw = config("plugin.{$realName}.app.mfe_dev_server");
                $devServer = '';
                if ($devServerRaw === true) {
                    $port = (int) config("plugin.{$realName}.app.mfe_dev_port", 5174);
                    $devServer = "http://localhost:{$port}/app/{$realName}/admin/dist/remoteEntry.js";
                } elseif (is_string($devServerRaw) && $devServerRaw !== '') {
                    $devServer = $devServerRaw;
                }
                if ($devServer !== '') {
                    $pluginName = $realName;
                    $remoteEntry = $devServer;
                } else {
                    // 优先级 2：生产构建包 plugin/<name>/public/admin/dist/remoteEntry.js
                    $remoteFile = base_path("plugin/{$realName}/public/admin/dist/remoteEntry.js");
                    if (is_file($remoteFile)) {
                        $pluginName = $realName;
                        // remoteEntry.js 无 hash 文件名，追加构建版本号（文件 mtime）避免浏览器缓存旧产物
                        $remoteEntry = "/app/{$realName}/admin/dist/remoteEntry.js?v=" . filemtime($remoteFile);
                    }
                }
            }
            // ---- end 微前端 ----
            // route name 必须全局唯一：Vue Router addRoute 按 name 去重，
            // 不同 plugin 即使 path 相同（如 webniu 和 devs 都有 path='user'）也不能冲突
            // 规则：显式 name 优先，否则 plugin + path 组合兜底，再不行用 id
            $routeName = $item['name'];
            if (empty($routeName)) {
                $pathPart = !empty($item['path']) ? $item['path'] : ('id' . $item['id']);
                $routeName = ($pluginName ?: 'base') . '_' . $pathPart;
            }

            $meta = array_key_to_camel($item);
            // 前端 RouteMeta 是 Open interface，remoteEntry/pluginName 塞进 meta 就能安全取用
            $meta['pluginName'] = $pluginName;
            $meta['remoteEntry'] = $remoteEntry;
            $formatted_items[] = [
                'id'    => $item['id'],
                'title' => $item['title'],
                'model' => $item['model'],
                'pid'   => $item['pid'],
                'type'  => $item['type'],
                'path'  => $item['path'],
                'name'  => $routeName,
                'component' => $item['component'],
                'pluginName' => $pluginName,
                'remoteEntry' => $remoteEntry,
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

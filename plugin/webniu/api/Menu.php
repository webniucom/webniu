<?php
namespace plugin\webniu\api;

use plugin\webniu\app\model\Rule;

/**
 * 对外提供的菜单接口
 */
class Menu
{

    /**
     * 根据key获取菜单
     * @param $key
     * @param $model
     * @return array
     */
    public static function get($key, $model = 'webniu')
    {
        $menu = Rule::where([
            'key' => $key,
            'model' => $model
        ])->first();
        return $menu ? $menu->toArray() : null;
    }

    /**
     * 根据id获得菜单
     * @param $id
     * @return array
     */
    public static function find($id): array
    {
        return Rule::find($id)->toArray();
    }

    /**
     * 添加菜单
     * @param array $menu
     * @return int
     */
    public static function add(array $menu)
    {
        $item = new Rule;
        foreach ($menu as $key => $value) {
            $item->$key = $value;
            if($key == 'model' && isset($menu['model'])){
                $item->model= $menu['model'];
                $item->menu = $menu['model'] == 'webniu' ? 0 : 1;
            }
        }
        $item->save();
        return $item->id;
    }

    /**
     * 导入菜单
     * @param array $menu_tree
     * @return void
     */
    public static function import(array $menu_tree)
    {
        if (is_numeric(key($menu_tree)) && !isset($menu_tree['key'])) {
            foreach ($menu_tree as $item) {
                static::import($item);
            }
            return;
        }
        $children = $menu_tree['children'] ?? [];
        $model = $menu_tree['model'] ?? 'webniu';
        $plugin = $menu_tree['plugin'] ?? null;
        // 打开方式：菜单自定义 > 插件 config/app.php 声明 > 默认 0
        $menu_tree['open'] = $menu_tree['open'] ?? static::pluginOpen($plugin);
        $open = $menu_tree['open'];
        unset($menu_tree['children']);
        if (isset($menu_tree['key']) && $old_menu = Menu::get($menu_tree['key'], $model, $plugin)) {
            $pid = $old_menu['id'];
            if(isset($menu_tree['model'])){
                $model = $old_menu['model'];
                $plugin = $old_menu['plugin'];
            }
            // 已存在的菜单不覆盖 open，保留后台「路由」页签里的手动设置
            $open = $old_menu['open'];
            unset($menu_tree['open']);
            Rule::where([
                'key' => $menu_tree['key'],
                'model' => $model,
                'plugin' => $plugin
            ])->update($menu_tree);
        } else {
            $pid = static::add($menu_tree);
            if(isset($menu_tree['model'])){
                $model = $menu_tree['model'] ?? 'webniu';
                $plugin = $menu_tree['plugin'] ?? null;
                $open = $menu_tree['open'] ?? 0;
            }
        }
        foreach ($children as $menu) {
            $menu['pid'] = $pid;
            $menu['model'] = $model;
            $menu['plugin'] = $plugin;
            // 子菜单未自定义 open 时继承父级
            if (!isset($menu['open'])) {
                $menu['open'] = $open;
            }
            static::import($menu);
        }
    }

    /**
     * 读取插件 config/app.php 中声明的默认打开方式
     * 安装时新插件配置尚未加载，这里直接读文件，不依赖 config()
     *
     * @param string|null $plugin
     * @return mixed
     */
    protected static function pluginOpen(?string $plugin)
    {
        if (!$plugin) {
            return 0;
        }
        clearstatcache();
        $file = base_path() . "/plugin/{$plugin}/config/app.php";
        if (!is_file($file)) {
            return 0;
        }
        $app = include $file;
        return is_array($app) && array_key_exists('open', $app) ? $app['open'] : 0;
    }

    /**
     * 准备删除菜单
     * @param array $menu_tree
     * @return void
     */
    public static function delete($menu_tree)
    {
        if(!isset($menu_tree[0]['key'])){
            $menu_tree = $menu_tree[0];
            if(!isset($menu_tree[0]['key'])){
                $menu_tree = $menu_tree[0];
            }
        } 
        foreach ($menu_tree as $menu) { 
            static::impdelete($menu['key'], $menu['model'] ?? 'webniu');
        }
    }

    /**
     * 删除菜单
     * @param $key
     * @param $model
     * @return void
     */
    public static function impdelete($key, $model = 'webniu')
    {
        $item = Rule::where([
            'key' => $key,
            'model' => $model
        ])->first();
        if (!$item) {
            return;
        }
        // 子规则一起删除
        $delete_ids = $children_ids = [$item['id']];
        while($children_ids) {
            $children_ids = Rule::whereIn('pid', $children_ids)->pluck('id')->toArray();
            $delete_ids = array_merge($delete_ids, $children_ids);
        }
        Rule::whereIn('id', $delete_ids)->delete();
    }


    /**
     * 获取菜单中某个(些)字段的值
     * @param $menu
     * @param null $column
     * @param null $index
     * @return array|mixed
     */
    public static function column($menu, $column = null, $index = null)
    {
        $values = [];
        if (is_numeric(key($menu)) && !isset($menu['key'])) {
            foreach ($menu as $item) {
                $values = array_merge($values, static::column($item, $column, $index));
            }
            return $values;
        }

        $children = $menu['children'] ?? [];
        unset($menu['children']);
        if ($column === null) {
            if ($index) {
                $values[$menu[$index]] = $menu;
            } else {
                $values[] = $menu;
            }
        } else {
            if (is_array($column)) {
                $item = [];
                foreach ($column as $f) {
                    $item[$f] = $menu[$f] ?? null;
                }
                if ($index) {
                    $values[$menu[$index]] = $item;
                } else {
                    $values[] = $item;
                }
            } else {
                $value = $menu[$column] ?? null;
                if ($index) {
                    $values[$menu[$index]] = $value;
                } else {
                    $values[] = $value;
                }
            }
        }
        foreach ($children as $child) {
            $values = array_merge($values, static::column($child, $column, $index));
        }
        return $values;
    }

}
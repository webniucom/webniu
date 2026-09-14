<?php

namespace plugin\webniu\app\model;

use plugin\webniu\app\model\Base;

/**
 * 数据源同步日志
 * @property integer $id 主键
 * @property integer $pid 父级ID
 * @property string $model 平台
 * @property string $plugin 插件
 * @property integer $menu 菜单
 * @property integer $pid 父级
 * @property integer $type 类型
 * @property string $title 标题
 * @property string $name 名称
 * @property string $icon 图标
 * @property string $path 路径
 * @property string $key 键
 * @property string $href 链接
 * @property integer $open 打开方式
 * @property string $component 组件
 * @property string $link 链接
 * @property string $show_text_badge 显示文本徽章
 * @property string $auth_mark 权限
 * @property integer $show_badge 显示徽章
 * @property integer $is_hide 隐藏菜单
 * @property integer $is_hide_tab 隐藏标签
 * @property integer $is_iframe 是否iframe
 * @property integer $is_enable 是否启用
 * @property integer $is_full_page 是否全屏页
 * @property integer $is_blank 是否新窗口打开
 * @property integer $keep_alive 是否保持活动
 * @property integer $fixed_tab 固定标签
 * @property string $active_path 激活路径
 * @property integer $sort 排序
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class Rule extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rules';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    
    
    
    
}

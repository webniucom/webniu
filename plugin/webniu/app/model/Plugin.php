<?php

namespace plugin\webniu\app\model;

use plugin\webniu\app\model\Base;

/**
 * 插件模型
 * @property int $id 主键
 * @property string $type 类型
 * @property int $class 分类id
 * @property string $name 应用标题
 * @property string $desc 描述
 * @property string $author 作者
 * @property string $identifier 应用标识
 * @property string $logo 应用图标
 * @property string $icon icon图标
 * @property string $href 应用入口
 * @property string $open 打开方式
 * @property string $version 当前版本
 * @property int $installed 是否安装
 * @property int $disabled 是否禁用
 * @property int $jump 跳转入口
 * @property string $releases 历史版本
 * @property string $created_at 插入时间
 * @property string $updated_at 更新时间
 */
class Plugin extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'plugin';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    
    
}

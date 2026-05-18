<?php

namespace plugin\webniu\app\model;

use plugin\webniu\app\model\Base;

/**
 * @property integer $id 主键(主键)
 * @property integer $pid 父级ID
 * @property string $group 分组
 * @property string $label 标签
 * @property string $value 值
 * @property integer $sort 排序
 * @property integer $disabled 禁用
 * @property integer $status 状态
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class Dict extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dict';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';
}

<?php

namespace plugin\webniu\app\model;

use plugin\webniu\app\model\Base;

/**
 * @property integer $id 主键(主键)
 * @property string $model 模型
 * @property string $name 名称
 * @property string $label 标签
 * @property integer $sort 排序
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class ConfsetGroup extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'confset_group';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';
}

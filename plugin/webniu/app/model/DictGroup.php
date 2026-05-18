<?php

namespace plugin\webniu\app\model;

use plugin\webniu\app\model\Base;

/**
 * @property integer $id 主键(主键)
 * @property string $label 标签
 * @property string $value 值
 * @property integer $status 状态
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class DictGroup extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dict_group';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';
}

<?php

namespace plugin\webniu\app\model;

use plugin\webniu\app\model\Base;

/**
 * @property integer $id 主键(主键)
 * @property string $model 模型
 * @property integer $count 数量
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class Statistics extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'statistics';

    /**
     * The primary key associated with the table.
     * @var string
     */
    protected $primaryKey = 'id';
    
    
    public $timestamps = true;
    
}

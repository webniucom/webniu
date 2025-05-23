<?php

namespace plugin\webniu\app\model;


/**
 * @property integer $id (主键)
 * @property string $model 键
 * @property string $name 键
 * @property mixed $value 值
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class Option extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'options';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        'model',
        'name',
        'value',
        'created_at',
        'updated_at'
    ];

}

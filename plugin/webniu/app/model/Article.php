<?php

namespace plugin\webniu\app\model;

use plugin\webniu\app\model\Base;

/**
 * @property integer $id 主键(主键)
 * @property integer $group 分组
 * @property string $title 标题
 * @property string $source 来源
 * @property string $author 作者
 * @property integer $sort 排序
 * @property integer $gender 男女
 * @property integer $status 状态
 * @property string $content 内容
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class Article extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'article';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    
    
    
    
}

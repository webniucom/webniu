<?php

namespace plugin\webniu\app\model;

use plugin\webniu\app\model\Base;

/**
 * @property integer $id 主键(主键)
 * @property integer $cateid 分类ID
 * @property string $title 文章标题
 * @property string $thumb 缩略图
 * @property string $source 来源
 * @property string $author 作者
 * @property integer $displayorder 排序
 * @property integer $click 阅读次数
 * @property integer $status 状态
 * @property string $content 文章内容
 */
class ArticleCategory extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'article_category';

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
    public $timestamps = false;

    
    
}

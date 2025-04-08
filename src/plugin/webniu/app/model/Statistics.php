<?php

namespace plugin\webniu\app\model;

use plugin\webniu\app\model\Base;

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
     *
     * @var string
     */
    protected $primaryKey = 'id';
    
    
    public $timestamps = true;
    
}

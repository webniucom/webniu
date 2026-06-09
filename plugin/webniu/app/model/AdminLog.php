<?php

namespace plugin\webniu\app\model;

use plugin\webniu\app\model\Base;

class AdminLog extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'admin_log';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    
    
    public $timestamps = true;
    
}

<?php

namespace plugin\webniu\app\model;

use plugin\webniu\app\model\Base;

/**
 *
 */
class EmailTemp extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'email_temp';

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

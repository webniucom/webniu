<?php

namespace plugin\webniu\app\model;

use plugin\webniu\app\model\Base;

/**
 *
 */
class Plugin extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'plugin';

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

<?php

namespace plugin\webniu\app\model;

use DateTimeInterface;
use support\Model;


class Base extends Model
{
    /**
     * @var string
     */
    protected $connection = 'plugin.webniu.mysql';

    /**
     * 格式化日期
     *
     * @param DateTimeInterface $date
     * @return string
     */
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}

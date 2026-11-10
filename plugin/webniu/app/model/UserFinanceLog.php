<?php

namespace plugin\webniu\app\model;

/**
 * 会员余额/积分变动记录
 *
 * @property integer $id 主键
 * @property integer $uid 会员ID
 * @property integer $type 类型(1=余额 2=积分)
 * @property string $change 变动值(正数增加 负数扣减)
 * @property string $before 变动前
 * @property string $after 变动后
 * @property string $remark 备注
 * @property string $source 来源(admin=后台 lottery=抽奖等)
 * @property integer $operator_id 操作人ID(0=系统)
 * @property string $biz_key 业务唯一键(幂等)
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class UserFinanceLog extends Base
{
    /**
     * 余额
     */
    public const TYPE_MONEY = 1;

    /**
     * 积分
     */
    public const TYPE_SCORE = 2;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_finance_log';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uid',
        'type',
        'change',
        'before',
        'after',
        'remark',
        'source',
        'operator_id',
        'biz_key',
    ];
}

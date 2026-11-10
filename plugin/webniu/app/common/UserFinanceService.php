<?php

namespace plugin\webniu\app\common;

use plugin\webniu\app\model\User;
use plugin\webniu\app\model\UserFinanceLog;
use Illuminate\Database\QueryException;
use support\exception\BusinessException;

/**
 * 会员余额/积分变动服务
 *
 * 全局唯一入口：调用方只需提供会员ID(uid)、类型、加减数值、备注即可，
 * 不依赖 openid / 粉丝体系，任何应用（后台充值、抽奖发奖等）都能直接调用。
 *
 * 保证：
 *  - 行锁防并发，余额/积分不会被同时改乱
 *  - 每笔变动都落一条记录，可追溯
 *  - 扣减不能把余额/积分扣成负数
 *  - 积分强制取整，余额保留两位小数
 *  - 传入 bizKey 时具备幂等性，重复调用不会重复入账
 */
class UserFinanceService
{
    /**
     * 变动余额或积分
     *
     * @param int $uid 会员ID
     * @param int $type 1=余额 2=积分
     * @param float|int $amount 变动值，正数增加，负数扣减
     * @param string $remark 备注
     * @param string $source 来源标识(admin/lottery/...)
     * @param int $operatorId 操作人ID，0 表示系统
     * @param string|null $bizKey 业务唯一键，用于幂等，如 lottery:prizelog:123；为 null 时不校验
     * @return UserFinanceLog
     * @throws BusinessException
     */
    public static function change(int $uid, int $type, $amount, string $remark = '', string $source = 'admin', int $operatorId = 0, ?string $bizKey = null): UserFinanceLog
    {
        $type = (int)$type;
        if (!in_array($type, [UserFinanceLog::TYPE_MONEY, UserFinanceLog::TYPE_SCORE], true)) {
            throw new BusinessException('类型不正确');
        }

        if ($uid <= 0) {
            throw new BusinessException('会员ID不正确');
        }

        if ($type === UserFinanceLog::TYPE_SCORE) {
            // 积分按整数处理，传入小数会被强制取整
            $amount = (int)round((float)$amount);
            if ($amount === 0) {
                throw new BusinessException('积分变动值不能为0');
            }
        } else {
            // 余额保留两位小数
            $amount = round((float)$amount, 2);
            if (abs($amount) < 0.01) {
                throw new BusinessException('金额变动值不能为0');
            }
        }

        $bizKey = $bizKey === null ? null : mb_substr(trim($bizKey), 0, 64, 'UTF-8');
        if ($bizKey === '') {
            $bizKey = null;
        }

        return User::query()->getConnection()->transaction(function () use ($uid, $type, $amount, $remark, $source, $operatorId, $bizKey) {
            // 幂等校验：同一业务单号已处理过，直接返回原记录
            if ($bizKey !== null) {
                $exists = UserFinanceLog::where('biz_key', $bizKey)->first();
                if ($exists) {
                    return self::assertSameChange($exists, $uid, $type, $amount);
                }
            }

            /** @var User|null $user */
            $user = User::where('id', $uid)->lockForUpdate()->first();
            if (!$user) {
                throw new BusinessException('会员不存在');
            }

            $isMoney = $type === UserFinanceLog::TYPE_MONEY;
            $field   = $isMoney ? 'money' : 'score';
            $before  = $isMoney ? round((float)$user->money, 2) : (int)$user->score;
            $after   = $isMoney ? round($before + $amount, 2) : $before + $amount;

            if ($after < 0) {
                throw new BusinessException($isMoney ? '余额不足，扣减后不能为负数' : '积分不足，扣减后不能为负数');
            }

            $user->{$field} = $after;
            $user->save();

            try {
                return UserFinanceLog::create([
                    'uid'         => $uid,
                    'type'        => $type,
                    'change'      => $amount,
                    'before'      => $before,
                    'after'       => $after,
                    'remark'      => mb_substr($remark, 0, 100, 'UTF-8'),
                    'source'      => $source,
                    'operator_id' => $operatorId,
                    'biz_key'     => $bizKey,
                ]);
            } catch (QueryException $e) {
                // 并发下唯一索引兜底：若已被其他请求抢先写入，则按幂等返回
                if ($bizKey !== null && $e->getCode() === '23000') {
                    $exists = UserFinanceLog::where('biz_key', $bizKey)->first();
                    if ($exists) {
                        return self::assertSameChange($exists, $uid, $type, $amount);
                    }
                }
                throw $e;
            }
        });
    }

    /**
     * 校验已存在的幂等记录与本次请求参数是否一致
     *
     * @param UserFinanceLog $log 已存在的记录
     * @param int $uid 会员ID
     * @param int $type 类型
     * @param float|int $amount 变动值
     * @return UserFinanceLog
     * @throws BusinessException
     */
    private static function assertSameChange(UserFinanceLog $log, int $uid, int $type, $amount): UserFinanceLog
    {
        $same = (int)$log->uid === $uid
            && (int)$log->type === $type
            && abs(round((float)$log->change, 2) - (float)$amount) < 0.005;

        if (!$same) {
            throw new BusinessException('业务单号已存在，且与本次变动参数不一致');
        }

        return $log;
    }
}

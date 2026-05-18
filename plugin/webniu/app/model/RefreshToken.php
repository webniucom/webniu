<?php

namespace plugin\webniu\app\model;

use plugin\webniu\app\model\Base;

/**
 * 刷新令牌模型
 * @property integer $id ID(主键)
 * @property integer $admin_id 管理员ID
 * @property string $refresh_token 刷新令牌
 * @property string $access_token 访问令牌
 * @property string $expires_at 过期时间
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class RefreshToken extends Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'refresh_tokens';

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
        'admin_id',
        'refresh_token',
        'access_token',
        'expires_at',
    ];

    /**
     * 生成刷新令牌
     *
     * @param int $adminId
     * @param string $accessToken
     * @param int $expireDays 过期天数，默认7天
     * @return string
     */
    public static function generate(int $adminId, string $accessToken, int $expireDays = 7): string
    {
        $refreshToken = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + ($expireDays * 24 * 3600));

        self::create([
            'admin_id' => $adminId,
            'refresh_token' => $refreshToken,
            'access_token' => $accessToken,
            'expires_at' => $expiresAt,
        ]);

        return $refreshToken;
    }

    /**
     * 验证刷新令牌
     *
     * @param string $refreshToken
     * @return array|null
     */
    public static function validate(string $refreshToken): ?array
    {
        $token = self::where('refresh_token', $refreshToken)
            ->where('expires_at', '>', date('Y-m-d H:i:s'))
            ->first();

        if (!$token) {
            return null;
        }

        return [
            'admin_id' => $token->admin_id,
            'access_token' => $token->access_token,
        ];
    }

    /**
     * 刷新令牌并生成新的访问令牌
     *
     * @param string $refreshToken
     * @param string $newAccessToken
     * @return bool
     */
    public static function refreshToken(string $refreshToken, string $newAccessToken): bool
    {
        $token = self::where('refresh_token', $refreshToken)
            ->where('expires_at', '>', date('Y-m-d H:i:s'))
            ->first();

        if (!$token) {
            return false;
        }

        $token->access_token = $newAccessToken;
        $token->save();

        return true;
    }

    /**
     * 撤销刷新令牌
     *
     * @param string $refreshToken
     * @return bool
     */
    public static function revoke(string $refreshToken): bool
    {
        return self::where('refresh_token', $refreshToken)->delete() > 0;
    }

    /**
     * 撤销管理员的所有刷新令牌
     *
     * @param int $adminId
     * @return bool
     */
    public static function revokeByAdmin(int $adminId): bool
    {
        return self::where('admin_id', $adminId)->delete() > 0;
    }

    /**
     * 清理过期的刷新令牌
     *
     * @return int 删除的记录数
     */
    public static function cleanExpired(): int
    {
        return self::where('expires_at', '<=', date('Y-m-d H:i:s'))->delete();
    }
}

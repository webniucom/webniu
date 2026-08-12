<?php

namespace plugin\webniu\app\model;

use plugin\webniu\app\model\Base;

class PasswordReset extends Base
{
    protected $table = 'password_resets';
    
    protected $fillable = ['email', 'token', 'expire_time'];
   

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    /**
     * 创建重置令牌
     * @param string $email
     * @return string
     */
    public static function createToken(string $email): string
    {
        // 删除该邮箱之前的令牌
        self::where('email', $email)->delete();
        
        // 生成唯一令牌（结合时间戳和随机数）
        $token = md5(uniqid(mt_rand(), true) . $email);
        
        // 有效期1小时
        $expireTime = time() + 3600;
        
        self::create([
            'email' => $email,
            'token' => $token,
            'expire_time' => date('Y-m-d H:i:s', $expireTime),
        ]);
        
        return $token;
    }
    
    /**
     * 验证令牌
     * @param string $token
     * @return bool|self
     */
    public static function validateToken(string $token)
    {
        $record = self::where('token', $token)->first();
        
        if (!$record) {
            return false;
        }
        
        // 检查是否过期
        if (strtotime($record->expire_time) < time()) {
            $record->delete();
            return false;
        }
        
        return $record;
    }
    
    /**
     * 删除令牌
     * @param string $token
     */
    public static function removeToken(string $token)
    {
        self::where('token', $token)->delete();
    }
}
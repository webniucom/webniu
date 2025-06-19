<?php
namespace plugin\webniu\app\event;

use support\Request;
class User
{
    // 注册成功后执行的代码
    public function insert($user)
    {
        // 统计注册量：
        statistics('user');
    }
    public function update($ids)
    {
        
    }
    public function delete($ids)
    {
        
    }
}
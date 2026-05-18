<?php

namespace plugin\webniu\app\admin\controller;

use plugin\webniu\app\common\Auth;
use plugin\webniu\app\common\Util;
use plugin\webniu\app\model\Admin;
use plugin\webniu\app\model\RefreshToken;
use support\exception\BusinessException;
use support\Request;
use support\Response;
use Throwable;
use Webman\Captcha\CaptchaBuilder;
use Webman\Captcha\PhraseBuilder;

/**
 * 管理员账户
 */
class AccountController extends Crud
{
    /**
     * 不需要登录的方法
     * @var string[]
     */
    protected $noNeedLogin = ['login', 'logout', 'captcha', 'refreshToken','register'];

    /**
     * 不需要鉴权的方法
     * @var string[]
     */
    protected $noNeedAuth = ['info'];

    /**
     * @var Admin
     */
    protected $model = null;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->model = new Admin;
    }

    /**
     * 账户设置
     * @return Response
     * @throws Throwable
     */
    public function index()
    {
        return raw_view('account/index');
    }

    /**
     * 登录
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function login(Request $request): Response
    {
        $this->checkDatabaseAvailable();
        $captcha = $request->post('captcha', '');
        if ($captcha && strtolower($captcha) !== session('captcha-login')) {
            return $this->json(400, '验证码错误');
        }
        $request->session()->forget('captcha-login');
        print_r($request->post());
        $username = $request->post('userName', '');
        $password = $request->post('password', '');
        if (!$username) {
            return $this->json(400, '用户名不能为空');
        }
        if (!$password) {
            return $this->json(400, '密码不能为空');
        }
        $this->checkLoginLimit($username);
        $admin = Admin::where('username', $username)->first();
        if (!$admin || !Util::passwordVerify($password, $admin->password)) {
            return $this->json(400, '账户不存在或密码错误');
        }
        if ($admin->status != 0) {
            return $this->json(400, '当前账户暂时无法登录');
        }
        $admin->login_at = date('Y-m-d H:i:s');
        $admin->save();
        $this->removeLoginLimit($username);
        $admin = $admin->toArray();
        $session = $request->session();
        $admin['password'] = md5($admin['password']);
        $session->set('admin', $admin);
        
        $accessToken = $request->sessionId();
        $refreshToken = RefreshToken::generate($admin['id'], $accessToken, 7);
        
        return $this->json(200, '登录成功', [
            'nickname' => $admin['nickname'],
            'token' => $accessToken,
            'refresh_token' => $refreshToken,
        ]);
    }

    public function register(Request $request): Response
    {
        $data = $this->insertInput($request);
        //print_r($data);
        return $this->json(200, '注册成功', ['id' => 2]);
    }

    /**
     * 退出
     * @param Request $request
     * @return Response
     */
    public function logout(Request $request): Response
    {
        $adminId = admin_id();
        if ($adminId) {
            RefreshToken::revokeByAdmin($adminId);
        }
        $request->session()->delete('admin');
        return $this->json(200, '退出成功');
    }

    /**
     * 刷新令牌
     * @param Request $request
     * @return Response
     */
    public function refreshToken(Request $request): Response
    {
        $refreshToken = $request->post('refresh_token');
        if (!$refreshToken) {
            return $this->json(1, '刷新令牌不能为空');
        }

        $tokenData = RefreshToken::validate($refreshToken);
        if (!$tokenData) {
            return $this->json(1, '刷新令牌无效或已过期');
        }

        $admin = Admin::find($tokenData['admin_id']);
        if (!$admin || $admin->status != 0) {
            RefreshToken::revoke($refreshToken);
            return $this->json(1, '账户不存在或已被禁用');
        }

        $admin->login_at = date('Y-m-d H:i:s');
        $admin->save();

        $adminArray = $admin->toArray();
        $adminArray['password'] = md5($adminArray['password']);
        $adminArray['roles'] = \plugin\webniu\app\model\AdminRole::where('admin_id', $admin->id)->pluck('role_id')->toArray();
        
        $session = $request->session();
        $session->set('admin', $adminArray);

        $newAccessToken = $request->sessionId();
        RefreshToken::refresh($refreshToken, $newAccessToken);

        return $this->json(0, '刷新成功', [
            'nickname' => $adminArray['nickname'],
            'token' => $newAccessToken,
            'refresh_token' => $refreshToken,
        ]);
    }

    /**
     * 获取登录信息
     * @param Request $request
     * @return Response
     */
    public function info(Request $request): Response
    {
        $admin = admin();
        if (!$admin) {
            return $this->json(400);
        }
        $info = [
            'id' => $admin['id'],
            'user_name' => $admin['username'],
            'nick_name' => $admin['nickname'],
            'avatar' => $admin['avatar'],
            'email' => $admin['email'],
            'mobile' => $admin['mobile'],
        ];
        $info = array_key_to_camel($info);
        $info['isSuperAdmin'] = Auth::isSuperAdmin();
        $info['buttons'] = ['B_CODE1', 'B_CODE2', 'B_CODE3'];
        $info['roles'] = ['R_SUPER'];
        $info['token'] = $request->sessionId();
        return $this->json(200, 'ok', $info);
    }

    /**
     * 更新
     * @param Request $request
     * @return Response
     */
    public function update(Request $request): Response
    {
        $allow_column = [
            'nickname' => 'nickname',
            'avatar' => 'avatar',
            'email' => 'email',
            'mobile' => 'mobile',
        ];

        $data = $request->post();
        $update_data = [];
        foreach ($allow_column as $key => $column) {
            if (isset($data[$key])) {
                $update_data[$column] = $data[$key];
            }
        }
        if (isset($update_data['password'])) {
            $update_data['password'] = Util::passwordHash($update_data['password']);
        }
        Admin::where('id', admin_id())->update($update_data);
        $admin = admin();
        unset($update_data['password']);
        foreach ($update_data as $key => $value) {
            $admin[$key] = $value;
        }
        $request->session()->set('admin', $admin);
        return $this->json(0);
    }

    /**
     * 修改密码
     * @param Request $request
     * @return Response
     */
    public function password(Request $request): Response
    {
        $hash = Admin::find(admin_id())['password'];
        $password = $request->post('password');
        if (!$password) {
            return $this->json(2, '密码不能为空');
        }
        if ($request->post('password_confirm') !== $password) {
            return $this->json(3, '两次密码输入不一致');
        }
        if (!Util::passwordVerify($request->post('old_password'), $hash)) {
            return $this->json(1, '原始密码不正确');
        }
        $update_data = [
            'password' => Util::passwordHash($password)
        ];
        Admin::where('id', admin_id())->update($update_data);
        return $this->json(0);
    }

    /**
     * 验证码
     * @param Request $request
     * @param string $type
     * @return Response
     */
    public function captcha(Request $request, string $type = 'login'): Response
    {
        $builder = new PhraseBuilder(4, 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ');
        $captcha = new CaptchaBuilder(null, $builder);
        $captcha->setDistortion(false);
        $captcha->setBackgroundColor(255, 255, 255);
        $captcha->build(120);
        $request->session()->set("captcha-$type", strtolower($captcha->getPhrase()));
        $img_content = $captcha->get();
        return response($img_content, 200, ['Content-Type' => 'image/jpeg']);
    }

    /**
     * 检查登录频率限制
     * @param $username
     * @return void
     * @throws BusinessException
     */
    protected function checkLoginLimit($username)
    {
        $limit_log_path = runtime_path() . '/login';
        if (!is_dir($limit_log_path)) {
            mkdir($limit_log_path, 0777, true);
        }
        $limit_file = $limit_log_path . '/' . md5($username) . '.limit';
        $time = date('YmdH') . ceil(date('i')/5);
        $limit_info = [];
        if (is_file($limit_file)) {
            $json_str = file_get_contents($limit_file);
            $limit_info = json_decode($json_str, true);
        }

        if (!$limit_info || $limit_info['time'] != $time) {
            $limit_info = [
                'username' => $username,
                'count' => 0,
                'time' => $time
            ];
        }
        $limit_info['count']++;
        file_put_contents($limit_file, json_encode($limit_info));
        if ($limit_info['count'] >= 5) {
            throw new BusinessException('登录失败次数过多，请5分钟后再试');
        }
    }

    /**
     * 解除登录频率限制
     * @param $username
     * @return void
     */
    protected function removeLoginLimit($username)
    {
        $limit_log_path = runtime_path() . '/login';
        $limit_file = $limit_log_path . '/' . md5($username) . '.limit';
        if (is_file($limit_file)) {
            unlink($limit_file);
        }
    }

    protected function checkDatabaseAvailable()
    {
        if (!config('plugin.webniu.database')) {
            throw new BusinessException('请重启webman');
        }
    }

}

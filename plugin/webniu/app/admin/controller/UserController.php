<?php

namespace plugin\webniu\app\admin\controller;

use plugin\webniu\app\common\UserFinanceService;
use plugin\webniu\app\model\User;
use plugin\webniu\app\model\UserFinanceLog;
use support\exception\BusinessException;
use support\Request;
use support\Response;
use Throwable;

/**
 * 用户管理 
 */
class UserController extends Crud
{

    /**
     * @var User
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new User;
    }

    /**
     * 浏览
     * @return Response
     * @throws Throwable
     */
    public function index(): Response
    {
        return $this->json(200, '加载成功', [
            'config' => [
                'title' => '用户',
                'layout' => 'refresh,size,fullscreen,columns,settings',
            ],
        ]);
    }

    /**
     * 查询
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function select(Request $request): Response
    {
        [$where, $format, $limit, $field, $order] = $this->selectInput($request);
        if (!empty($where['username']) && is_string($where['username'])) {
            $where['username'] = ['like', "%{$where['username']}%"];
        }
        if (!empty($where['nickname']) && is_string($where['nickname'])) {
            $where['nickname'] = ['like', "%{$where['nickname']}%"];
        }
        $query = $this->doSelect($where, $field, $order);
        return $this->doFormat($query, $format, $limit);
    }

    /**
     * 插入
     * @param Request $request
     * @return Response
     * @throws BusinessException|Throwable
     */
    public function insert(Request $request): Response
    {
        if ($request->method() === 'POST') {
            $data = $this->insertInput($request);
            $id = $this->doInsert($data);
            statistics('user');//统计注册量
            return $this->json(200, 'ok', ['id' => $id]);
        }
        return $this->json(400, '请求方法错误');
    }

    /**
     * 更新
     * @param Request $request
     * @return Response
     * @throws BusinessException|Throwable
     */
    public function update(Request $request): Response
    {
        if ($request->method() === 'POST') {
            return parent::update($request);
        }
        return $this->json(400, '请求方法错误');
    }

    /**
     * 插入前过滤：余额/积分只能通过充值接口变动，避免绕过变动记录
     * @param Request $request
     * @return array
     */
    protected function insertInput(Request $request): array
    {
        $data = parent::insertInput($request);
        unset($data['money'], $data['score']);
        return $data;
    }

    /**
     * 更新前过滤：余额/积分只能通过充值接口变动，避免绕过变动记录
     * @param Request $request
     * @return array
     */
    protected function updateInput(Request $request): array
    {
        [$id, $data] = parent::updateInput($request);
        unset($data['money'], $data['score']);
        return [$id, $data];
    }

    /**
     * 余额/积分充值（金额正数增加、负数扣减）
     * @param Request $request
     * @return Response
     */
    public function recharge(Request $request): Response
    {
        if ($request->method() !== 'POST') {
            return $this->json(400, '请求方法错误');
        }
        $uid    = (int)$request->post('id', 0);
        $type   = (int)$request->post('type', 0);
        $amount = $request->post('amount');
        $remark = trim((string)$request->post('remark', ''));
        if ($uid <= 0) {
            return $this->json(400, '会员ID不能为空');
        }
        if (!in_array($type, [UserFinanceLog::TYPE_MONEY, UserFinanceLog::TYPE_SCORE], true)) {
            return $this->json(400, '充值类型不正确');
        }
        if (!is_numeric($amount) || (float)$amount == 0) {
            return $this->json(400, $type === UserFinanceLog::TYPE_MONEY ? '金额不能为0' : '积分不能为0');
        }
        try {
            $log  = UserFinanceService::change($uid, $type, (float)$amount, $remark, 'admin', (int)admin_id());
            $user = User::find($uid);
            return $this->json(200, '操作成功', [
                'log'   => $log,
                'money' => $user ? $user->money : 0,
                'score' => $user ? $user->score : 0,
            ]);
        } catch (BusinessException $e) {
            return $this->json(400, $e->getMessage());
        }
    }

    /**
     * 余额/积分变动记录
     * @param Request $request
     * @return Response
     */
    public function rechargelogs(Request $request): Response
    {
        $uid   = (int)$request->get('uid', 0);
        $type  = (int)$request->get('type', 0);
        $page  = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', 10);
        $page  = $page > 0 ? $page : 1;
        $limit = $limit > 0 ? min($limit, 50) : 10;
        if ($uid <= 0) {
            return $this->json(400, '会员ID不能为空');
        }
        $query = UserFinanceLog::where('uid', $uid);
        if (in_array($type, [UserFinanceLog::TYPE_MONEY, UserFinanceLog::TYPE_SCORE], true)) {
            $query->where('type', $type);
        }
        $paginator = $query->orderByDesc('id')->paginate($limit, ['*'], 'page', $page);
        return $this->json(200, 'ok', [
            'data'  => $paginator->items(),
            'total' => $paginator->total(),
        ]);
    }
}

<?php
namespace plugin\webniu\app\admin\controller;

use plugin\webniu\app\admin\controller\Crud;
use support\exception\BusinessException;
use support\Request; 
use support\Response; 
use plugin\webniu\app\model\AdminLog;

class LogtimeController extends Crud
{
     
    /**
     * @var AdminLog
     */
    protected $model = null;

    /**
     * 只返回当前管理员数据
     * @var string
     */
    protected $dataLimit = 'personal';

    protected $noNeedAuth = ['select'];


    /**
     * 禁用自动获权限的方法
     * @var string[]
     */
    protected $noAuthMark = ['insert', 'update', 'delete'];



    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new AdminLog;
    }

    /**
     * 浏览
     * @return Response
     * @throws Throwable
     */
    public function index(): Response
    {
        return $this->json(200, '加载成功', [
            'form' => [],
            'grouplist' => [],
            'config' => [
                'title' => '日志',
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
        $query = $this->doSelect($where, $field, $order);
        return $this->doFormat($query, $format, $limit);
    }
     
}
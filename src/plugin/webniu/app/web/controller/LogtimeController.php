<?php
namespace plugin\webniu\app\web\controller;

use plugin\webniu\app\web\controller\Crud;
use support\exception\BusinessException;
use support\Request; 
use support\Response; 
use plugin\webniu\app\model\AdminLog;

class LogtimeController extends Crud
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
        $this->model = new AdminLog;
    }

    /**
     * 浏览
     * @return Response
     * @throws Throwable
     */
    public function index(): Response
    {
        return raw_view('logtime/index');
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
        $query = $query->orderBy('id', 'desc');
        return $this->doFormat($query, $format, $limit);
    }
     
}
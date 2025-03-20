<?php

namespace plugin\webniu\app\web\controller;

use support\Request;
use support\Response;
use plugin\webniu\app\model\EmailTemp; 
use support\exception\BusinessException;
use plugin\webniu\app\common\Email;
use app\exception\MyBusinessException;

/**
 * 邮件模板 
 */
class EmailTempController extends Crud
{
    
    /**
     * @var EmailTemp
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new EmailTemp;
    }
    
    /**
     * 浏览
     * @return Response
     */
    public function index(): Response
    {
        return view('emailtemp/index');
    }

    /**
     * 邮件测试
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function test(Request $request): Response
    {
        $from       = $request->post('from');
        $to         = $request->post('to');
        $subject    = $request->post('subject');
        $content    = $request->post('content');
        if($from=='' || $to=='' || $subject=='' || $content=='' ){
            return json(['code' => 1, 'msg' => '参数不能为空']);
        }
         
        try {
            Email::send($from, $to, $subject, $content);
        }  catch (Throwable $e) {
            if (method_exists($e, 'getExceptions')) {
                throw new BusinessException(current($e->getExceptions())->getMessage());
            }
            throw $e;
        }
        return json(['code' => 0, 'msg' => 'ok']);
    }
    
    /**
     * 插入
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function insert(Request $request): Response
    {
        if ($request->method() === 'POST') {
            $data   = $this->insertInput($request); 
            if($this->model->where([
                ['uniacid','=','0'],
                ['name','=',$data['name']]
            ])->count()>0){
                return $this->json(1, '名称已存在', []);
            };
            $id     = $this->doInsert($data);
            return $this->json(0, 'ok', ['id' => $id]);
        }
        $config = options(['api']);
         
        return view('emailtemp/insert',$config);
    }

    /**
     * 更新
     * @param Request $request
     * @return Response
     * @throws BusinessException
    */
    public function update(Request $request): Response
    {
        if ($request->method() === 'POST') {
            if($name = $request->post('name',false)){
                if($this->model->where([
                    ['uniacid','=','0'],
                    ['name','=',$name]
                ])->count()>0){
                    return $this->json(1, '名称已存在', []);
                };
            };
            
            return parent::update($request);
        }
        return view('emailtemp/update');
    }

}

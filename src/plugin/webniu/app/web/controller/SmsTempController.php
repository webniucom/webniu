<?php

namespace plugin\webniu\app\web\controller;

use support\Request;
use support\Response;
use plugin\webniu\app\model\SmsTemp; 
use support\exception\BusinessException;
use plugin\webniu\app\common\Sms;

/**
 * 短信模板 
 */
class SmsTempController extends Crud
{
    
    /**
     * @var SmsTemp
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new SmsTemp;
    }
    
    /**
     * 浏览
     * @return Response
     */
    public function index(): Response
    {
        return view('smstemp/index');
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
            $id     = $this->doInsert($data);
            return $this->json(0, 'ok', ['id' => $id]);
        }
        $config = options(['api']);
        $config['api']['sms']['sign'] = ''; 
        if($config['api']['sms']['type']!=0 && $config['api']['sms']['type']!=''){
            $config['api']['sms']['sign'] = $config['api']['sms'][$config['api']['sms']['type']]['sign_name'];
        }
         
        return view('smstemp/insert',$config);
    }
    /**
     * 短信测试
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function test(Request $request): Response
    {
         
        $template_id    = $request->post('template_id');
        $phone_number   = $request->post('phone_number');
        $jsondata       = $request->post('jsondata',false);
        $sign           = $request->post('sign');
        $jsondata       = json_decode($jsondata, true);
         
        try {
            Sms::send($phone_number, [
                'template' => $template_id,
                'data' => $jsondata,
            ]);
        }  catch (Throwable $e) {
            if (method_exists($e, 'getExceptions')) {
                throw new BusinessException(current($e->getExceptions())->getMessage());
            }
            throw $e;
        }
        return json(['code' => 0, 'msg' => 'ok']);
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
            return parent::update($request);
        }
        return view('smstemp/update');
    }

}

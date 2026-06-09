<?php

namespace plugin\webniu\app\admin\controller;

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
     * 只返回当前管理员数据
     * @var string
     */
    protected $dataLimit = 'personal';

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
        return $this->json(200, '加载成功', [
            'form' => [],
            'grouplist' => [],
            'config' => [
                'group' => true,
                'title' => '公告',
                'groupname' => '分类',
                'layout' => 'refresh,size,fullscreen,columns,settings',
            ],
        ]);
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
            return parent::insert($request);
        }
        return $this->json(400, '请求方法错误', []);
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
        }  catch (BusinessException $e) {
            if (method_exists($e, 'getExceptions')) {
                return $this->json(400, $e->getMessage(), []);
            }
            throw $e;
        }
        return $this->json(200, 'ok', []);
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
        return $this->json(400, '请求方法错误', []);
    }

}

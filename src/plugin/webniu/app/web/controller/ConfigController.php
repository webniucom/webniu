<?php

namespace plugin\webniu\app\web\controller;

use plugin\webniu\app\common\Util;
use plugin\webniu\app\model\Option;
use support\exception\BusinessException;
use support\Request;
use support\Response;
use Throwable;

/**
 * 系统设置
 */
class ConfigController extends Base
{
    /**
     * 不需要验证权限的方法
     * @var string[]
     */
    protected $noNeedAuth = ['get'];

    /**
     * 账户设置
     * @return Response
     * @throws Throwable
     */
    public function index(): Response
    {
        return raw_view('config/index');
    }

    /**
     * 获取配置
     * @return Response
     */ 
    public function get(Request $request): Response
    {
        $name   = $request->get('name','base');
        $name   = explode(",", $name); 
        $output = [];
        foreach($name as $k=>$v){
            $jsondata = Option::where([ 
                ['name','=','system_'.$v]
            ])->value('value');
            if($jsondata!=NULL){
                $jsondata   = json_decode($jsondata, true);
                if($v=='api'){
                     
                }
                $output[$v] = $jsondata;
            }
        }
        return json($output);
    }

    /**
     * 更改
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function update(Request $request): Response
    {
        $post   = $request->post(); 
        $data   = []; 
        foreach ($post as $section => $items) {
            $obj        = Option::where([ 
                ['name','=','system_'.$section]
            ]);
            $jsondata   = $obj->value('value');
            if ($jsondata==NULL) {
                //continue;
            }
            $jsondata   = $jsondata ? json_decode($jsondata, true):[];
            switch ($section) {
                case 'base':
                    $data['title']        = htmlspecialchars($items['title'] ?? '');
                    $data['keyword']      = htmlspecialchars($items['keyword'] ?? '');
                    $data['description']  = htmlspecialchars($items['description'] ?? '');
                    $data['siteurl']      = htmlspecialchars($items['siteurl'] ?? '');
                    $data['image']        = Util::filterUrlPath($items['image'] ?? '');
                    $data['ipcbeian']     = htmlspecialchars($items['ipcbeian'] ?? '');
                    $data['copyright']    = htmlspecialchars($items['copyright'] ?? '');
                    break;
                case 'core': 
                    $data['entrance']     = htmlspecialchars($items['entrance'] ?? '');
                    if(isset($jsondata['entrance']) && $jsondata['entrance'] != $data['entrance']){ 
                        //同时把旧的路由地址传过去；
                        $items['old_entrance']  = $jsondata['entrance'];
                    }
                    break;
                case 'user': 
                    $data['managereg']        = !empty($items['managereg']);
                    $data['background_image'] = Util::filterUrlPath($items['background_image'] ?? '');
                     
                    $data['roles']            = htmlspecialchars($items['roles'] ?? '');
                    $data['m_image_verify']   = !empty($items['m_image_verify']);
                    $data['m_phone_verify']   = !empty($items['m_phone_verify']);
                    $data['userreg']          = !empty($items['userreg']);
                    $data['u_image_verify']   = !empty($items['u_image_verify']);
                    $data['u_phone_verify']   = !empty($items['u_phone_verify']);
                    $data['u_email_verify']   = !empty($items['u_email_verify']);
                    
                    break;
                case 'atta':
                    $data['dirname']      = htmlspecialchars($items['dirname'] ?? '');
                    $data['path_style']   = htmlspecialchars($items['path_style'] ?? '');
                    $data['include']      = htmlspecialchars($items['include'] ?? '');
                    $data['exclude']      = htmlspecialchars($items['exclude'] ?? '');
                    $data['single_limit'] = htmlspecialchars($items['single_limit'] ?? '');
                    $data['total_limit']  = htmlspecialchars($items['total_limit'] ?? '');
                    $data['nums']         = htmlspecialchars($items['nums'] ?? '');
                    $data['storage_type'] = htmlspecialchars($items['storage_type'] ?? '');
                    $data['Ftp']['ip']        = htmlspecialchars($items['Ftp']['ip'] ?? '');
                    $data['Ftp']['port']      = htmlspecialchars($items['Ftp']['port'] ?? '');
                    $data['Ftp']['pasv']      = htmlspecialchars($items['Ftp']['pasv'] ?? '');
                    $data['Ftp']['username']  = htmlspecialchars($items['Ftp']['username'] ?? '');
                    $data['Ftp']['password']  = htmlspecialchars($items['Ftp']['password'] ?? '');
                    $data['Ftp']['domain']    = htmlspecialchars($items['Ftp']['domain'] ?? '');
                    $data['Qiniu']['accessKey']   = htmlspecialchars($items['Qiniu']['accessKey'] ?? '');
                    $data['Qiniu']['secretKey']   = htmlspecialchars($items['Qiniu']['secretKey'] ?? '');
                    $data['Qiniu']['bucket']      = htmlspecialchars($items['Qiniu']['bucket'] ?? '');
                    $data['Qiniu']['domain']      = htmlspecialchars($items['Qiniu']['domain'] ?? '');
                    $data['Oss']['accessKeyId']     = htmlspecialchars($items['Oss']['accessKeyId'] ?? '');
                    $data['Oss']['accessKeySecret'] = htmlspecialchars($items['Oss']['accessKeySecret'] ?? '');
                    $data['Oss']['local']     = htmlspecialchars($items['Oss']['local'] ?? '');
                    $data['Oss']['bucket']    = htmlspecialchars($items['Oss']['bucket'] ?? '');
                    $data['Oss']['endpoint']  = htmlspecialchars($items['Oss']['endpoint'] ?? '');
                    $data['Oss']['domain']    = htmlspecialchars($items['Oss']['domain'] ?? '');
                    $data['Cos']['secretId']  = htmlspecialchars($items['Cos']['secretId'] ?? '');
                    $data['Cos']['secretKey'] = htmlspecialchars($items['Cos']['secretKey'] ?? '');
                    $data['Cos']['bucket']    = htmlspecialchars($items['Cos']['bucket'] ?? '');
                    $data['Cos']['region']    = htmlspecialchars($items['Cos']['region'] ?? '');
                    $data['Cos']['domain']    = htmlspecialchars($items['Cos']['domain'] ?? '');

                    break;
                case 'pic':
                    $data['pic_thumb_type']   = !empty($items['pic_thumb_type']); 
                    $data['pic_thumb_width']  = htmlspecialchars($items['pic_thumb_width'] ?? '');
                    $data['pic_thumb_percent']= htmlspecialchars($items['pic_thumb_percent'] ?? '');
                    $data['pic_mark_type']    = !empty($items['pic_mark_type']); 
                    $data['pic_mark_style']   = htmlspecialchars($items['pic_mark_style'] ?? '');
                    $data['pic_thumb_text']   = htmlspecialchars($items['pic_thumb_text'] ?? '');
                    $data['pic_thumb_size']   = htmlspecialchars($items['pic_thumb_size'] ?? '');
                    $data['pic_thumb_color']  = htmlspecialchars($items['pic_thumb_color'] ?? '');
                    $data['pic_thumb_touming']= htmlspecialchars($items['pic_thumb_touming'] ?? '');
                    $data['pic_thumb_img']    = htmlspecialchars($items['pic_thumb_img'] ?? '');
                    $data['pic_mark_weizhi']  = htmlspecialchars($items['pic_mark_weizhi'] ?? ''); 

                    break;
                case 'menu':
                    $data['data']             = Util::filterUrlPath($items['data'] ?? '');
                    $data['accordion']        = !empty($items['accordion']);
                    $data['collapse']         = !empty($items['collapse']);
                    $data['control']          = !empty($items['control']);
                    $data['controlWidth']     = (int)$items['controlWidth'] ?? 500;
                    $data['controlHeight']    = $items['controlHeight'] ?? '100%';
                    $data['select']           = (int)$items['select'] ?? 0;
                    $data['async']            = true;
                    break;
                case 'tab':
                    $data['enable']           = true;
                    $data['keepState']        = !empty($items['keepState']);
                    $data['preload']          = !empty($items['preload']);
                    $data['session']          = !empty($items['session']);
                    $data['max']              = Util::filterNum($items['max'] ?? '30');
                    $data['index']['id']      = Util::filterNum($items['index']['id'] ?? '0');
                    $data['index']['href']    = Util::filterUrlPath($items['index']['href'] ?? '');
                    $data['index']['title']   = htmlspecialchars($items['index']['title'] ?? '首页');
                    break;
                 
                case 'api':
                    $data['smtp']['type']               = !empty($items['smtp']['type']); 
                    $data['smtp']['ip']                 = htmlspecialchars($items['smtp']['ip'] ?? ''); 
                    $data['smtp']['port']               = htmlspecialchars($items['smtp']['port'] ?? '');
                    $data['smtp']['username']           = htmlspecialchars($items['smtp']['username'] ?? '');
                    $data['smtp']['password']           = htmlspecialchars($items['smtp']['password'] ?? '');
                    $data['smtp']['secure']             = htmlspecialchars($items['smtp']['secure'] ?? '');
                    $data['smtp']['from']               = htmlspecialchars($items['smtp']['from'] ?? '');
                    $data['smtp']['test']               = htmlspecialchars($items['smtp']['test'] ?? ''); 

                    $data['login']['type']                  = !empty($items['login']['type']);
                    $data['login']['wechat']['appid']         = htmlspecialchars($items['login']['wechat']['appid'] ?? '');
                    $data['login']['wechat']['appkey']        = htmlspecialchars($items['login']['wechat']['appkey'] ?? '');
                    $data['login']['wechat']['callback']      = htmlspecialchars($items['login']['wechat']['callback'] ?? '');
                    $data['login']['qq']['appid']             = htmlspecialchars($items['login']['qq']['appid'] ?? '');
                    $data['login']['qq']['appkey']            = htmlspecialchars($items['login']['qq']['appkey'] ?? '');
                    $data['login']['qq']['callback']          = htmlspecialchars($items['login']['qq']['callback'] ?? '');

                    $data['sms']['type']                    = htmlspecialchars($items['sms']['type'] ?? '');
                    $data['sms']['aliyun']['access_key_id']       = htmlspecialchars($items['sms']['aliyun']['access_key_id'] ?? '');
                    $data['sms']['aliyun']['access_key_secret']   = htmlspecialchars($items['sms']['aliyun']['access_key_secret'] ?? '');
                    $data['sms']['aliyun']['sign_name']           = htmlspecialchars($items['sms']['aliyun']['sign_name'] ?? '');

                    $data['sms']['qcloud']['sdk_app_id']      = htmlspecialchars($items['sms']['qcloud']['sdk_app_id'] ?? '');
                    $data['sms']['qcloud']['secret_id']       = htmlspecialchars($items['sms']['qcloud']['secret_id'] ?? '');
                    $data['sms']['qcloud']['secret_key']      = htmlspecialchars($items['sms']['qcloud']['secret_key'] ?? '');
                    $data['sms']['qcloud']['sign_name']       = htmlspecialchars($items['sms']['qcloud']['sign_name'] ?? ''); 

                    break;
                case 'theme':
                    $data['defaultColor']         = Util::filterNum($items['defaultColor'] ?? '2');
                    $data['defaultMenu']          = $items['defaultMenu'];
                    $data['defaultHeader']        = $items['defaultHeader'];
                    $data['defaultAside']         = $items['defaultAside'];

                    $data['allowCustom']          = !empty($items['allowCustom']);
                    $data['banner']               = !empty($items['banner']);
                    $data['footer']               = !empty($items['footer']);

                    $data['message']                = false;
                    $data['keepLoad']               = "500";
                    $data['autoHead']               = false;
                    $color = [
                        [
                            'id'    =>'1',
                            'color' =>'#36b368',
                            'second'=>'#f0f9eb'
                        ],
                        [
                            'id'    =>'2',
                            'color' =>'#2d8cf0',
                            'second'=>'#ecf5ff'
                        ],
                        [
                            'id'    =>'3',
                            'color' =>'#f6ad55',
                            'second'=>'#fdf6ec'
                        ],
                        [
                            'id'    =>'4',
                            'color' =>'#f56c6c',
                            'second'=>'#fef0f0'
                        ],
                        [
                            'id'    =>'5',
                            'color' =>'#3963bc',
                            'second'=>'#ecf5ff'
                        ]
                    ];
                    $data['colors']                 = $color; 

                    break;
                case 'colors':
                    foreach ($jsondata['colors'] as $index => $item) {
                        if (!isset($items[$index])) {
                            $jsondata['colors'][$index] = $item;
                            continue;
                        }
                        $data_item = $items[$index];
                        $data[$index]['id'] = $index + 1;
                        $data[$index]['color'] = $this->filterColor($data_item['color'] ?? '');
                        $data[$index]['second'] = $this->filterColor($data_item['second'] ?? '');
                    }
                    break;

            }  
            $output = array_merge($jsondata,$data);
            unset($data);
            if(empty($jsondata)){
                $obj->insert(
                    [
                        'plugin'=> $request->plugin,
                        'name'  => 'system_'.$section,
                        'value' =>json_encode($output)
                    ]
                );
            }else{
                $obj->update([
                    'value' => json_encode($output)
                ]);
            }
             
        }
        return $this->json(0);
    }

    /**
     * 颜色检查
     * @param string $color
     * @return string
     * @throws BusinessException
     */
    protected function filterColor(string $color): string
    {
        if (!preg_match('/\#[a-zA-Z]6/', $color)) {
            throw new BusinessException('参数错误');
        }
        return $color;
    }

}

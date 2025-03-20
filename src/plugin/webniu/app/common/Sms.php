<?php

namespace plugin\webniu\app\common;

use Overtrue\EasySms\Exceptions\InvalidArgumentException;
use Overtrue\EasySms\Exceptions\NoGatewayAvailableException;
use Overtrue\EasySms\Strategies\OrderStrategy;
use Overtrue\EasySms\EasySms;
use RuntimeException;
use support\exception\BusinessException;
use plugin\webniu\app\model\SmsTemp;

/**
 * Sms
 */
class Sms
{
 
    /**
     * 发送短信
     * @param string|array $to
     * @param array $message
     * @param array $gateways
     * @return array
     * @throws BusinessException
     * @throws InvalidArgumentException
     * @throws NoGatewayAvailableException
     */
    public static function send($to, array $message, array $gateways = []): array
    {
        $sms = static::getSender();
        return $sms->send($to, $message, $gateways);
    }

    /**
     * 按照标签发送
     * @param $tagName
     * @param $to
     * @param array $data
     * @param array $gateways
     * @return array
     * @throws InvalidArgumentException
     * @throws NoGatewayAvailableException
     */
    public static function sendByTag($to, $tabname, array $data = [], array $gateways = []): array
    {
        $options        = static::getConfig();
        if($options['sms']['type']==0){
            throw new BusinessException('无可用短信接口');
        }
        $available[]    = $options['sms']['type'];
        $SmsTemp        = SmsTemp::where('name',$tabname)->select('template_id','sign')->first();
         
        if (!$SmsTemp) {
            throw new RuntimeException("短信模板名称 $tabname 不存在");
        }
        $newConfig = [
            // HTTP 请求的超时时间（秒）
            'timeout' => 5.0,
            // 默认发送配置
            'default' => [
                // 网关调用策略，默认：顺序调用
                'strategy' => \Overtrue\EasySms\Strategies\OrderStrategy::class,
                // 默认可用的发送网关
                'gateways' => $available,
            ],
            // 可用的网关配置
            'gateways' => [
                'errorlog' => [
                    'file' => runtime_path() . '/logs/easy-sms.log',
                ],
                $options['sms']['type']=>$options['sms'][$options['sms']['type']]
            ],
        ];
        $sms = new EasySms($newConfig);
        return $sms->send($to, [
            // 不同的厂商有不同的模版id
            'template'  => $SmsTemp->template_id,
            'data'      => $data,
        ] , $gateways);
    }

    /**
     * Get Sms
     * @param array $config
     * @return EasySms
     * @throws BusinessException
     */
    public static function getSender(array $config = []): EasySms
    {
        if (!class_exists(EasySms::class)) {
            throw new BusinessException('请执行 composer require overtrue/easy-sms 并重启');
        } 
        $options = static::getConfig();
        if($options['sms']['type']==0){
            throw new BusinessException('无可用短信接口');
        }
         
        $def_gat[]      = $options['sms']['type'];
         
        $config = [
            // HTTP 请求的超时时间（秒）
            'timeout' => 5.0,
            // 默认发送配置
            'default' => [
                // 网关调用策略，默认：顺序调用
                'strategy' => \Overtrue\EasySms\Strategies\OrderStrategy::class,
                // 默认可用的发送网关
                'gateways' => $def_gat,
            ],
            // 可用的网关配置
            'gateways' => [
                'errorlog' => [
                    'file' => runtime_path() . '/logs/easy-sms.log',
                ],
                $options['sms']['type']=>$options['sms'][$options['sms']['type']]
            ],
        ];
        
        if (!$config) {
            throw new BusinessException('未设置SMS配置');
        }
        return new EasySms($config);
    }

    /**
     * 获取配置
     * @return array
     */
    public static function getConfig(): array
    {
        $config = options(['api']); 
        return $config['api'];
    }

}
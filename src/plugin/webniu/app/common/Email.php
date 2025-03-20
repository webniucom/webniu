<?php

namespace plugin\webniu\app\common;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP; 
use plugin\webniu\app\model\EmailTemp;
use support\exception\BusinessException;

class Email
{
 
    /**
     * @param $from
     * @param $to
     * @param $subject
     * @param $content
     * @return void
     * @throws Exception|BusinessException
     */
    public static function send($from, $to, $subject, $content)
    {
        $mailer = static::getMailer();
        call_user_func_array([$mailer, 'setFrom'], (array)$from);
        call_user_func_array([$mailer, 'addAddress'], (array)$to);
        $mailer->Subject = "=?UTF-8?B?".base64_encode($subject)."?=";
        $mailer->isHTML(true);
        $mailer->Body = $content;
        $mailer->send();
    }

    /**
     * 按照模版发送
     * @param string|array $to
     * @param $templateName
     * @param array $templateData
     * @return void
     * @throws BusinessException
     * @throws Exception
     */
    public static function sendByTemplate($to, $templateName, array $templateData = [])
    {
        $emailTemplate = EmailTemp::where('name',$templateName)->select('subject', 'content')->first();
        if (!$emailTemplate) {
            throw new BusinessException('模版不存在');
        }
        $subject = $emailTemplate['subject'];
        $content = $emailTemplate['content'];
        if ($templateData) {
            $search = [];
            foreach ($templateData as $key => $value) {
                $search[] = '{' . $key . '}';
            }
            $content = str_replace($search, array_values($templateData), $content);
        }
        $config = static::getConfig();
        static::send($config['smtp']['from'] ?? '', $to, $subject, $content);
    }

    /**
     * Get Mailer
     * @return PHPMailer
     * @throws BusinessException
     */
    public static function getMailer(): PHPMailer
    {
        if (!class_exists(PHPMailer::class)) {
            throw new BusinessException('请先安装依赖，执行 composer require phpmailer/phpmailer 并重启');
        }
        $config = static::getConfig();
        if (!$config || $config['smtp']['ip']=='') {
            throw new BusinessException('未设置邮件配置');
        }
        $mailer = new PHPMailer();
        $mailer->SMTPDebug = false;
        $mailer->isSMTP();
        $mailer->Host = $config['smtp']['ip'];
        $mailer->SMTPAuth = true;
        $mailer->CharSet = 'UTF-8';
        $mailer->Username = $config['smtp']['username'];
        $mailer->Password = $config['smtp']['password'];
        $map = [
            'ssl' => PHPMailer::ENCRYPTION_SMTPS,
            'tls' => PHPMailer::ENCRYPTION_STARTTLS,
        ];
        $mailer->SMTPSecure = $map[$config['smtp']['secure']] ?? '';
        $mailer->Port = $config['smtp']['port'];
        return $mailer;
    }

    /**
     * 获取配置
     * @return array|null
     */
    public static function getConfig()
    {
        $config = options(['api']); 
        return $config['api'];
    }

}
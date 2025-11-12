<?php

/**
 * composer require alibabacloud/client ~1.5
 */

namespace App\Repositorys;

use DB;
use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;

class SmsRepository
{
    private $config;

    public function __construct()
    {
        $this->config = Config('common.sms');
    }

    /**
     * Send Message
     * @param string $params['type'] default
     * @param int $params['phone']
     * @param string $params['code']
     * @return array
     */
    public function send($params)
    {
        AlibabaCloud::accessKeyClient($this->config['aliyun']['accessKeyId'], $this->config['aliyun']['accessSecret'])->regionId($this->config['aliyun']['RegionId'])->asDefaultClient();

        $type = isset($params['type']) ? $params['type'] : 'default';

        try {
            switch ($type) {
                case 'default':
                    $options = $this->getDefaultOptions($params);
                    break;
            }
            $query = AlibabaCloud::rpc();
            $query->product('Dysmsapi');
            $query->version($this->config['aliyun']['version']);
            $query->action('SendSms');
            $query->method('POST');
            $query->host($this->config['aliyun']['host']);
            $query->options($options);
            $result = $query->request();
            $result = $result->toArray();
            if (!isset($result['Message']) || $result['Message'] != 'OK') {
                $log = '';
                $log .= "type: " . $type . "\n";
                $log .= "phone: " . $params['phone'] . "\n";
                $log .= "result: " . json_encode($result);
                logWrite($log, 'sms');
                return arrayFailed('发送失败');
            }
            $data = ['phone' => $params['phone'], 'content' => $options['log_content']];
            DB::table('sms_log')->insert($data);
            return arraySuccess();
        } catch (\Throwable $th) {
            $log = '';
            $log .= "type: " . $type . "\n";
            $log .= "phone: " . $params['phone'] . "\n";
            $log .= "errorMessage: " . $th->getMessage();
            logWrite($log, 'sms');
            return arrayFailed();
        }

    }

    /**
     * 获取默认短信模板
     * 您的验证码1234，该验证码5分钟内有效，请勿泄漏于他人！
     * Config('sms.template')['default']['tpl_code']
     * @param string $params['phone']
     * @param string $params['code']
     * @return array
     */
    private function getDefaultOptions($params)
    {
        $options = [
            'query' => [
                'RegionId' => $this->config['aliyun']['RegionId'],
                'SignName' => $this->config['signature'],
                'PhoneNumbers' => $params['phone'],
                'TemplateCode' => $this->config['template']['default']['tpl_code'],
                'TemplateParam' => "{'code':'" . $params['code'] . "'}",
            ],
            'log_content' => '您的验证码' . $params['code'] . '，该验证码5分钟内有效，请勿泄漏于他人！',
        ];
        return $options;
    }
}

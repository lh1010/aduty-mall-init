<?php

namespace App\Jobs;

use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class PushJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $timeout = 600;

    protected $params;

    public function __construct($params = [])
    {
        $this->params = $params;
    }

    public function handle()
    {
        $type = isset($this->params['type']) ? $this->params['type'] : '';
        switch ($type) {
            // 新内容 公众号信息推送
            case 'newContent_wxmp':
                $this->newContent_wxmp();
                break;
            default:
                dd('参数错误');
                break;
        }
    }

    /**
     * 新内容 公众号信息推送
     */
    public function newContent_wxmp()
    {
        $params = $this->params['params'];
        $url = $this->params['url'];
        $res = curl_post($url, $params);
        logWrite('push_res: ' . json_encode($res), 'newContent_wxmp');
    }
}

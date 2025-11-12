<?php

namespace App\Repositorys;

use DB;
use Carbon\Carbon;

class PushRepository
{
    /**
     * 新内容 公众号信息推送
     * @param int $params['post_id']
     */
    public function newContent_wxmp($params = [])
    {
        set_time_limit(0);
        $template_id = 'uAG8zSzVszqNbqEvszxVRKo0AOBMZFrm-4XN5I_Q3lo';

        try {
            // MySQL5.7 groupBy报错
            DB::statement('SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode,\'ONLY_FULL_GROUP_BY\',\'\'))');

            $post = DB::table('post')
                ->select(['post.*', 'user.nickname as user_nickname'])
                ->where('post.id', $params['post_id'])
                ->leftJoin('user', 'user.id', 'post.user_id')
                ->first();
            if (empty($post)) return jsonFailed('内容不存在');

            // 要推送的用户
            $users = DB::table('user')
                ->select(['user.*', 'city.shortname as city_name'])
                ->leftJoin('city', 'city.id', 'user.city_id')
                ->where('user.status', 1)
                ->where('user.wxmp_openid', '<>', '')
                ->groupBy('user.wxmp_openid')
                ->get()->toArray();

            $wxmp_config = Config('common.wxmp');
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $wxmp_config['appid'] . '&secret=' . $wxmp_config['secret'];
            $res = curl_get($url);
            $res_token = json_decode($res, 1);
            logWrite('res_token: ' . json_encode($res), 'newContent_wxmp');

            $url = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token=' . $res_token['access_token'] . '&next_openid=';
            $res = curl_get($url);
            $res = json_decode($res, 1);

            $url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $res_token['access_token'];

            foreach ($users as $key => $value) {
                if ($value->city_name != $post->city_name) {
                    continue;
                }

                $touser = $value->wxmp_openid;
                $date = date('Y-m-d H:i:s', time());
                $title = $post->title;
                if (mb_strlen($title, 'UTF-8') > 16) {
                    $title = mb_substr($title, 0, 16, 'UTF-8') . '...';
                }

                $params = [
                    'touser' => $touser,
                    'template_id' => $template_id,
                    'url' => Config('common.app_url') . '/h5/#/pages/post/show?id=' . $post->id,
                    'data' => [
                        'thing15' => [
                            'value' => $title, // 工单名称
                        ],
                        // 'amount53' => [
                        //     'value' => !empty($post->price_str) ? $post->price_str : '未知', // 服务金额
                        // ],
                        'time14' => [
                            'value' => $date, // 派单时间
                        ],
                        'thing27' => [
                            'value' => !empty($post->city_name) ? $post->city_name . ' ' . $post->address : '未知', // 派单范围
                        ],
                        'thing13' => [
                            'value' => $post->user_nickname, // 派单人
                        ],
                    ],
                ];
                $params = json_encode($params);

                $data = [
                    'type' => 'newContent_wxmp',
                    'params' => $params,
                    'url' => $url,
                ];
                app(\App\Jobs\PushJob::class)->dispatch($data);
            }

            return jsonSuccess();
        } catch (\Throwable $th) {
            logWrite($th->getMessage(), 'newContent_wxmp');
            return jsonFailed($th->getMessage());
        }
    }

    /**
     * 查看联系方式 公众号信息推送
     * @param int $params['post_id'] 查看内容ID
     * @param int $params['user_id'] 查看者ID
     */
    public function getContact_wxmp($params = [])
    {
        set_time_limit(0);
        try {
            $post = DB::table('post')->where('id', $params['post_id'])->first();
            if (empty($post)) return jsonFailed('内容不存在');
            $loginUser = DB::table('user')->where('id', $post->user_id)->first();
            if (empty($loginUser)) return jsonFailed('发布者不存在');
            if (empty($loginUser->wxmp_openid)) return jsonFailed('发布者未关注公众号');
            $user = DB::table('user')->where('id', $params['user_id'])->first();
            if (empty($user)) return jsonFailed('查看者不存在');
            //dd($user);

            $wxmp_config = Config('common.wxmp');
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $wxmp_config['appid'] . '&secret=' . $wxmp_config['secret'];
            $res = curl_get($url);
            $res_token = json_decode($res, 1);
            //logWrite('res_token: ' . json_encode($res), 'getContact_wxmp');

            $url = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token=' . $res_token['access_token'] . '&next_openid=';
            $res = curl_get($url);
            $res = json_decode($res, 1);

            $url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $res_token['access_token'];
            $wxmp_openid = $loginUser->wxmp_openid;
            $date = date('Y-m-d', time());
            $params = [
                'touser' => $wxmp_openid,
                'template_id' => 'JRWgZJCE_mYFu-zDkEICtU6d09hTrrj262NZKIPEcPk',
                'url' => Config('common.app_url') . '/h5/#/pages/account/index',
                'data' => [
                    'thing1' => [
                        'value' => $user->nickname,
                    ],
                    'time2' => [
                        'value' => $date,
                    ],
                    'thing3' => [
                        'value' => '成功查看联系方式',
                    ],
                    'thing4' => [
                        'value' => $post->title,
                    ],
                ],
            ];
            $params = json_encode($params);

            $data = [
                'params' => $params,
                'url' => $url,
            ];
            app(\App\Jobs\WxmpPushJob::class)->dispatch($data);
            return jsonSuccess();
        } catch (\Throwable $th) {
            logWrite($th->getMessage(), 'getContact_wxmp');
            return jsonFailed('微信推送接口异常' . $th->getMessage());
        }
    }
}

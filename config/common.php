<?php

$res = file_exists(config_path() . '/readfile/common.php') ? json_decode(include 'readfile/common.php', 1) : [];

$app_url = isset($res['app_url']) ? $res['app_url'] : env('APP_URL', '');

return [

    'app_name' => isset($res['app_name']) ? $res['app_name'] : env('APP_NAME', ''),
    'app_url' => $app_url,
    'copyright' => isset($res['copyright']) ? $res['copyright'] : '© ' . date('Y') . ' ' . env('APP_NAME', ''),
    'beian' => isset($res['beian']) ? $res['beian'] : '豫ICP备0000000000号-1',
    'version' => isset($res['version']) ? $res['version'] : '1.0.1',
    'font_path' => isset($res['font_path']) ? $res['font_path'] : '/data/wwwroot/app.com/public/static/fonts/msyh.ttc',
    'sms_code' => 6666, // 超级验证码 测试用

    // 上传设置
    'upload' => [
        'path' => 'lh100', // 指定上传路径
        'max_size' => 0, // 上传文件最大值 单位M 0=不限制
        'file_types' => [], // 支持上传的文件类型 []=不限制
    ],

    // 客服信息
    'contact' => [
        'weixin' => isset($res['contact']['weixin']) ? $res['contact']['weixin'] : 'linghaokeji100',
        'email' => isset($res['contact']['email']) ? $res['contact']['email'] : '610392592@qq.com',
        'qq' => isset($res['contact']['qq']) ? $res['contact']['qq'] : '610392592',
        'phone' => isset($res['contact']['phone']) ? $res['contact']['phone'] : '17337198120',
        'telphone' => isset($res['contact']['telphone']) ? $res['contact']['telphone'] : '0000-0000000',
    ],

    // 图片信息
    'image' => [
        'loading' => $app_url . '/static/images/loading/8.gif',
        'lazy' => $app_url . '/static/images/lazy.png',
        'default' => $app_url . '/static/default/images/default.png',
        'noresult' => $app_url . '/static/images/noresult.png',
        'user_avatar' => $app_url . '/static/images/user_avatar.png',
        'product_cover' => $app_url . '/static/images/product_cover.png',
        'invite' => $app_url . '/static/images/invite.png',
	],

    // 安卓APP
    'android' => [
        'new_version' => [
            'app_version' => '1.0.1',
            'app_security' => 'ZGIzN^WMzZG!VkY@2FkMDl@mNWIz!ZDR#lYTczOGFjZDQ5M2Q^',
            'download_url' => $app_url . '/app/1.0.1.006.apk',
        ],
        'version_list' => [
            '1.0.0' => [
                'app_security' => 'ZGIzN^WMzZG!VkY@2FkMDl@mNWIz!ZDR#lYTczOGFjZDQ5M2Q^',
                'update_type' => 0, // 0=不更新 1=推荐更新 2=强制更新
            ],
            '1.0.1' => [
                'app_security' => 'ZGIzN^WMzZG!VkY@2FkMDl@mNWIz!ZDR#lYTczOGFjZDQ5M2Q^',
                'update_type' => 0,
            ],
        ],
    ],

    // IOS APP
    'ios' => [
        'new_version' => [
            'app_version' => '1.0.1',
            'app_security' => 'ZGIzN^WMzZG!VkY@2FkMDl@mNWIz!ZDR#lYTczOGFjZDQ5M2Q^',
            'download_url' => $app_url . '/app/1.0.1.001.apk',
        ],
        'version_list' => [
            '1.0.1' => [
                'app_security' => 'ZGIzN^WMzZG!VkY@2FkMDl@mNWIz!ZDR#lYTczOGFjZDQ5M2Q^',
                'update_type' => 0, // 0=不更新 1=推荐更新 2=强制更新
            ],
        ],
    ],

    // 电脑网站设置
    'pc' => [
        'app_name' => isset($res['pc']['app_name']) ? $res['pc']['app_name'] : env('APP_NAME', ''),
        'app_logo' => isset($res['pc']['app_logo']) ? $res['pc']['app_logo'] : '',
        'app_version' => isset($res['pc']['app_version']) ? $res['pc']['app_version'] : '1.0.1',
        'index_title' => isset($res['pc']['index_title']) ? $res['pc']['index_title'] : env('APP_NAME', ''),
        'index_keywords' => isset($res['pc']['index_keywords']) ? $res['pc']['index_keywords'] : env('APP_NAME', ''),
        'index_description' => isset($res['pc']['index_description']) ? $res['pc']['index_description'] : env('APP_NAME', ''),
    ],

    // 微信小程序设置
    'wxapp' => [
        'appid' => isset($res['wxapp']['appid']) ? $res['wxapp']['appid'] : '',
		'secret' => isset($res['wxapp']['secret']) ? $res['wxapp']['secret'] : '',
        'qrcode' => isset($res['wxapp']['qrcode']) ? $res['wxapp']['qrcode'] : '',
        'audit_status' => isset($res['wxapp']['audit_status']) ? $res['wxapp']['audit_status'] : 0,
        'ios_pay_status' => 0,
        'app_name' => isset($res['wxapp']['app_name']) ? $res['wxapp']['app_name'] : env('APP_NAME', ''),
        'app_logo' => isset($res['wxapp']['app_logo']) ? $res['wxapp']['app_logo'] : '',
        'app_version' => isset($res['wxapp']['app_version']) ? $res['wxapp']['app_version'] : '',
    ],

    // 公众号设置
    'wxmp' => [
        'appid' => isset($res['wxmp']['appid']) ? $res['wxmp']['appid'] : '',
    	'secret' => isset($res['wxmp']['secret']) ? $res['wxmp']['secret'] : '',
        'qrcode' => isset($res['wxmp']['qrcode']) ? $res['wxmp']['qrcode'] : '',
    ],

    /**
     * 阿里云OSS文件存储服务
     */
    'oss' => [
        'status' => false,
        'accessKeyId' => '',
        'accessKeySecret' => '',
        'endpoint' => 'http://oss-cn-beijing.aliyuncs.com',
        'bucket' => 'lh1010',
        'url' => 'https://lh1010.oss-cn-beijing.aliyuncs.com',
    ],

    'sms' => [
        // 阿里云短信服务
        'aliyun' => [
            'accessKeyId' => isset($res['sms']['aliyun']['accessKeyId']) ? $res['sms']['aliyun']['accessKeyId'] : '',
            'accessSecret' => isset($res['sms']['aliyun']['accessSecret']) ? $res['sms']['aliyun']['accessSecret'] : '',
            'RegionId' => 'cn-hangzhou',
            'host' => 'dysmsapi.aliyuncs.com',
            'version' => '2017-05-25'
        ],

        // 短信签名
        'signature' => isset($res['sms']['signature']) ? $res['sms']['signature'] : '',

        // 短信模板
        'template' => [
            'default' => [
                'tpl_code' => isset($res['sms']['template']['default']['tpl_code']) ? $res['sms']['template']['default']['tpl_code'] : 'SMS_178755341',
                'name' => '通用验证码',
                'content' => '您的验证码${code}，该验证码5分钟内有效，请勿泄漏于他人！',
                'type' => '验证码'
            ]
        ]
    ],

    // 微信支付
    'weixinpay' => [
        // 商户号ID
        'mchid' => isset($res['weixinpay']['mchid']) ? $res['weixinpay']['mchid'] : '',
        // 商户号API密钥
        'apikey' => isset($res['weixinpay']['apikey']) ? $res['weixinpay']['apikey'] : '',
    ],

    // 支付宝支付
    'alipay' => [
        'appid' => isset($res['alipay']['appid']) ? $res['alipay']['appid'] : '',
        // 商户私钥
        'rsaPrivateKey' => isset($res['alipay']['rsaPrivateKey']) ? $res['alipay']['rsaPrivateKey'] : '',
        // 支付宝公钥
        'alipayPublicKey' => isset($res['alipay']['alipayPublicKey']) ? $res['alipay']['alipayPublicKey'] : '',
    ],

    // View
    'view' => [
        'tpl_folder' => 'default', // 模板文件夹

        // 文章板块
        'article' => [
            'tpl_list' => 'article.list',
            'tpl_show' => 'article.show'
        ],

        // 专题板块
        'subject' => [
            'tpl_list' => 'subject.list',
            'tpl_show' => 'subject.show'
        ],
    ],

    'adver' => [
        'client' => [
            '电脑网站', 'h5', '微信小程序',
        ],
    ],

    // 自定义字段
    'cusfield' => [
        'type' => ['输入框', '单选项', '多选项', '文本框', '单图上传', '多图上传'],
    ],

    // 专题
    'subject' => [
        'field_type' => ['文本', '富文本'],
    ],

    // 文章
    'article' => [
        'status' => [
            1 => '开启',
            0 => '关闭',
        ],

        'category_status' => [
            1 => '开启',
            0 => '关闭',
        ],
    ],

    // 用户
    'user' => [
        'status' => [
            1 => '开启',
            0 => '关闭',
        ],

        'realname_auth_status' => [
            0 => '未认证',
            1 => '审核中',
            2 => '审核失败',
            3 => '审核成功',
        ],

        'company_auth_status' => [
            0 => '未认证',
            1 => '审核中',
            2 => '审核失败',
            3 => '审核成功',
        ],

        // 新用户赠送金币
        'register_init_gold' => 2,

        // 邀请有礼 赠送上级用户金币
        'invite_user_gold' => 2,

        // 签到赠送金币
        'qiandao_gold' => 1,
    ],

    // 提现
    'withdrawal' => [
        'min' => 100,
        'max' => 5000,
        'today_count' => 1,
        'rate' => 0.03,
        'status' => [
            0 => '审核中',
            1 => '审核成功',
            2 => '审核失败',
        ],
    ],

    // 钱包最大额
    'max_wallet' => 10000,

    // 支付方式
    'payment_way_array' => [
        'weixinpay_jsapi_wxapp' => '微信支付',
        'weixinpay_jsapi_wxmp' => '微信支付',
        'weixinpay_native' => '微信支付',
        'weixinpay_h5' => '微信支付',
        'alipay_pc' => '支付宝支付',
        'alipay_wap' => '支付宝支付',
        'alipay_jsapi' => '支付宝支付',
        'wallet' => '钱包支付',
    ],

    // 金币价格
    // 1金币=1元
    'gold_prices' => [
        ['gold' => 50, 'price' => 50],
        ['gold' => 100, 'price' => 100],
        ['gold' => 200, 'price' => 200],
        ['gold' => 500, 'price' => 500],
        ['gold' => 1000, 'price' => 1000],
        ['gold' => 2000, 'price' => 2000],
    ],

    // VIP价格
    'vip_prices' => [
        ['month' => 1, 'date' => '1个月', 'gold' => 100],
        ['month' => 2, 'date' => '2个月', 'gold' => 200],
        ['month' => 3, 'date' => '3个月', 'gold' => 300],
        ['month' => 6, 'date' => '6个月', 'gold' => 600],
        ['month' => 12, 'date' => '12个月', 'gold' => 1200],
        ['month' => 24, 'date' => '24个月', 'gold' => 2400],
    ],

    // 推广海报图
    'poster' => '/static/images/hb_bg/1.png',

    // 团队
    'team' => [
        // 下级收益比例
        'rate' => 0.02,
    ],

    // 商城
    'mall' => [
        'status' => [
            1 => '开启',
            0 => '关闭',
        ],

        'product_status' => [
            0 => '审核中',
            1 => '已审核',
            2 => '审核失败',
        ],

        'updown_status' => [
            1 => '上架',
            0 => '下架',
        ],

        // 订单状态
        'order_status' => [
            -10 => '已取消',
            0 => '待付款',
            10 => '待发货',
            20 => '待收货',
            30 => '已完成',
        ],

        // 属性
        'product_attribute_type' => ['输入', '选项'],

        // 发货时效
        'shipment_time' => ['24小时内发货', '48小时内发货', '大于48小时发货'],

        // 物流方式
        'transport_way' => ['无需物流', '需要物流'],

        // 物流公司
        'shipping_company' => [
            ['name' => '顺丰快递'],
            ['name' => '圆通快读'],
        ],
    ],

    // 后台设置
    'admin' => [
        'super_admin_id' => 1, // 超级管理员ID
    ],

    // 腾讯地图
    'tmap' => [
        'key' => '',
    ],

];

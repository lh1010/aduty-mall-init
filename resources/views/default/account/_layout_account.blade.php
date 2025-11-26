<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=0.5, maximum-scale=2.0, user-scalable=no" />
<title>@yield('title', '')</title>
<meta name="keywords" content="@yield('keywords', '')">
<meta name="description" content="@yield('description', '')">
<link rel="shortcut icon" href="/favicon.ico?v={{Config('common.pc.version')}}" />
<link rel="stylesheet" type="text/css" href="/static/default/fonts/iconfont/iconfont.css?v={{Config('common.pc.version')}}" />
<link rel="stylesheet" type="text/css" href="/static/default/plugins/layui/css/layui.css?v={{Config('common.pc.version')}}" />
<link rel="stylesheet" type="text/css" href="/static/default/style/base.css?v={{Config('common.pc.version')}}" />
<link rel="stylesheet" type="text/css" href="/static/default/style/style.css?v={{Config('common.pc.version')}}" />
<link rel="stylesheet" type="text/css" href="/static/default/style/account.css?v={{Config('common.pc.version')}}" />
@yield('pagecss')
</head>
<body>
<input type="hidden" id="user_token" value="{{Cookie::get('user_token')}}">
@include(Config('common.view.tpl_folder') . '.shared._head_top')
<div class="account_top_nav">
  <div class="container">
    <div class="account_logo">
      <a href="/account"><img src="/static/default/images/account_logo.png"></a>
    </div>
    <div class="bd1">
      <a href="/account" class="a1">用户中心</a>
      <a href="/" class="a2">返回平台首页</a>
    </div>
    <ul class="navitems">
      <li class="li1"><a href="/account">首页</a></li>
      <li class="fold">
        <div class="dl">
          <div class="dt"><span>账户设置</span><i class="iconfont"></i></div>
          <div class="dd">
            <a href="/account/user_info"><span>个人信息</span></a>
            <a href="/account/user_password"><span>修改密码</span></a>
            <a href="/account/user_contact"><span>联系方式</span></a>
            <a href="/account/realname_auth"><span>实名认证</span></a>
            <a href="/account/company_auth"><span>企业认证</span></a>
          </div>
        </div>
      </li>
      <li class="fold">
        <div class="dl">
          <div class="dt"><span>交易与资产</span><i class="iconfont"></i></div>
          <div class="dd">
            <a href="/account/order_list"><span>我的订单</span></a>
            <a href="/account/wallet"><span>我的钱包</span></a>
            <a href="/account/collect_product"><span>收藏的商品</span></a>
          </div>
        </div>
      </li>
    </ul>
  </div>
</div>
@yield('content')
@include(Config('common.view.tpl_folder') . '.shared._foot')
<script type="text/javascript" src="/static/default/plugins/jquery/3.3.1/jquery.js?v={{Config('common.pc.version')}}"></script>
<script type="text/javascript" src="/static/default/plugins/layer/2.4/layer.js?v={{Config('common.pc.version')}}"></script>
<script type="text/javascript" src="/static/default/plugins/layui/layui.js?v={{Config('common.pc.version')}}"></script>
<script type="text/javascript" src="/static/default/plugins/jquery.lazyload.js?v=1.0.1"></script>
<script type="text/javascript" src="/static/default/script/common.js?v={{Config('common.pc.version')}}"></script>
@yield('pagejs')
</body>
</html>

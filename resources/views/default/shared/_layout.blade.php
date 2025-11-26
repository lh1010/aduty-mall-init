<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=0.5, maximum-scale=2.0, user-scalable=yes" />
<title>@yield('title', '')</title>
<meta name="keywords" content="@yield('keywords', '')">
<meta name="description" content="@yield('description', '')">
<link rel="shortcut icon" href="/favicon.ico?v={{Config('common.pc.version')}}" />
<link rel="stylesheet" type="text/css" href="/static/default/fonts/iconfont/iconfont.css?v={{Config('common.pc.version')}}" />
<link rel="stylesheet" type="text/css" href="/static/default/plugins/layui/css/layui.css?v={{Config('common.pc.version')}}" />
<link rel="stylesheet" type="text/css" href="/static/default/style/base.css?v={{Config('common.pc.version')}}" />
<link rel="stylesheet" type="text/css" href="/static/default/style/style.css?v={{Config('common.pc.version')}}" />
@yield('pagecss')
</head>
<body>
<input type="hidden" id="user_token" value="{{Cookie::get('user_token')}}">
@include(Config('common.view.tpl_folder') . '.shared._head_top')
@include(Config('common.view.tpl_folder') . '.shared._head')
@include(Config('common.view.tpl_folder') . '.shared._menu')
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

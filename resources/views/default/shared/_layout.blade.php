<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=0.5, maximum-scale=2.0, user-scalable=no" />
<title>@yield('title', Config('app.name'))</title>
<meta name="keywords" content="@yield('keywords', '')">
<meta name="description" content="@yield('description', '')">
<link rel="shortcut icon" href="/favicon.ico?v={{Config('common.version')}}" />
<link rel="stylesheet" type="text/css" href="/static/default/style/style.css?v={{Config('common.version')}}" />
@yield('pagecss')
</head>
<body>
@include('default.shared._head')
@yield('content')
@include('default.shared._foot')
<script type="text/javascript" src="/static/plugins/jquery/3.3.1/jquery.js"></script>
<script type="text/javascript" src="/static/plugins/Bootstrap/5.0.1/js/bootstrap.js"></script>
<script type="text/javascript" src="/static/plugins/layer/2.4/layer.js"></script>
<script type="text/javascript" src="/static/default/script/common.js?v={{Config('common.version')}}"></script>
@yield('pagejs')
</body>
</html>

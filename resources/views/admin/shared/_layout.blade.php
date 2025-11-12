<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title>后台管理</title>
<link rel="shortcut icon" href="/favicon.ico?v={{Config('common.version')}}" />
<link rel="stylesheet" type="text/css" href="/static/admin/plugins/Bootstrap/5.0.1/css/bootstrap.css" />
<link rel="stylesheet" type="text/css" href="/static/admin/style/style.css?v={{Config('common.version')}}" />
@yield('pagecss')
</head>
<body>
@yield('content')
<script type="text/javascript" src="/static/admin/plugins/jquery/3.3.1/jquery.js"></script>
<script type="text/javascript" src="/static/admin/plugins/popper.min.js"></script>
<script type="text/javascript" src="/static/admin/plugins/Bootstrap/5.0.1/js/bootstrap.js"></script>
<script type="text/javascript" src="/static/admin/plugins/layer/2.4/layer.js"></script>
<script type="text/javascript" src="/static/admin/script/common.js?v={{Config('common.version')}}"></script>
@yield('pagejs')
</body>
</html>

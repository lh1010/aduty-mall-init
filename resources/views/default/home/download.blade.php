<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=0.5, maximum-scale=2.0, user-scalable=no" />
<title>下载APP {{Config('common.app_name')}}</title>
<link rel="shortcut icon" href="/favicon.ico">
<style type="text/css">
* {
  margin: 0;
  padding: 0;
  outline: none;
}
a {
  text-decoration: none;
}
a:hover {
  text-decoration: none !important;
  color: #00B38A;
}
a:link {
  text-decoration: none !important;
}
a:visited {
  text-decoration: none !important;
}
html, body {
  background-color: #F5F6FA !important;
  height: 100%;
  font-size: 14px;
  //font-family: Arial, Helvetica, sans-serif;
  color: #333;
}
.down {
  text-align: center;
  padding-top: 100px;
}
.down .logo {
  width: 90px;
  border-radius: 8px;
}
.down .name {
  margin-top: 14px;
  letter-spacing: 1px;
  font-size: 16px;
  font-weight: 600;
}
.down .actions {
  margin-top: 30px;
}
.down .btn {
  background: #24AA42;
  color: #fff;
  border-radius: 5px;
  display: block;
  margin: 0 auto;
  margin-bottom: 14px;
  width: 160px;
  height: 38px;
  line-height: 38px;
  letter-spacing: 1px;
}
.abox {
  margin-top: 30px;
}
.abox a {
  color: #0d6efd;
}
.abox .item {
  margin-bottom: 8px;
}
.bbox {
  font-size: 12px;
  color: #999;
  margin-top: 50px;
}
</style>
</head>
<body>
<div class="down">
  <div class="container">
    <a href="{{Config('common.app_url')}}" target="_blank"><img class="logo" src="/static/images/logo2.png" /></a>
    <div class="name">{{Config('common.app_name')}}</div>
    <div class="actions">
      <a class="btn" href="javascript:down('android');">安卓APP下载</a>
      <a class="btn" href="javascript:down('ios');">苹果APP下载</a>
    </div>
    <div class="abox">
      <div class="item">暂不下载，访问H5</div>
      <div class="item"><a href="{{Config('common.app_url')}}/h5">{{Config('common.app_url')}}/h5</a></div>
    </div>
    <div class="bbox">平台内容均为演示之用，不构成实际服务或产品承诺</div>
  </div>
</div>
</body>
<script type="text/javascript" src="/static/plugins/jquery/3.3.1/jquery.js"></script>
<script type="text/javascript" src="/static/plugins/layer/2.4/layer.js"></script>
<script>
function down(client) {
  if (is_wx()) {
    layer.alert('请使用普通浏览器打开该链接下载~');
    return false;
  }
  if (client == 'android') {
    window.location.href = "{{Config('common.android')['new_version']['download_url']}}";
  }
  if (client == 'ios') {
    layer.alert('即将开启~');
  }
}

function is_wx() {
  // 获取 User Agent
  var userAgent = navigator.userAgent.toLowerCase();
  // 判断是否在微信中打开
  if (userAgent.indexOf('micromessenger') !== -1) {
    return true;
  } else {
    return false;
  }
}
</script>
<script>
var _hmt = _hmt || [];
(function() {
  var hm = document.createElement("script");
  hm.src = "https://hm.baidu.com/hm.js?15d4ac1aa1639b31a06c39b74acc0874";
  var s = document.getElementsByTagName("script")[0];
  s.parentNode.insertBefore(hm, s);
})();
</script>
</html>

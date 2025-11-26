@extends(Config('common.view.tpl_folder') . '.shared._layout')
@section('title', '密码登录' . ' ' . Config('common.pc.app_name'))
@section('keywords', '密码登录' . ' ' . Config('common.pc.app_name'))
@section('description', '密码登录' . ' ' . Config('common.pc.app_name'))
@section('pagecss')
<link rel="stylesheet" type="text/css" href="/static/default/style/account.css?v={{Config('common.pc.version')}}" />
<style type="text/css">
.foot {
  margin-top: 0;
}
</style>
@endsection
@section('content')
<div class="account_login">
  <div class="container box">
    <div class="form_box">
      <form class="layui-form" id="form" method="post" action="/api/account/login_password" autocomplete="off">
        <div class="title">密码登录</div>
        <ul class="u1">
          <li class="l1">登录手机：</li>
          <li class="l2"><input type="text" name="phone" placeholder="请输入手机号码"></li>
        </ul>
        <ul class="u1">
          <li class="l1">登录密码：</li>
          <li class="l2"><input type="password" name="password" placeholder="请输入登录密码"></li>
        </ul>
        <div class="d2">
          登录/注册代表同意本平台 <a href="/help/show/100000" target="_blank">用户协议</a> <a href="/help/show/100001" target="_blank">隐私协议</a>
        </div>
        <div class="dbtn"><input type="submit" value="立即登录"></div>
      </form>
      <div class="d1">
        <a class="a1" href="/login">手机验证码登录</a>
        <a class="a2" href="/register">立即注册</a>
      </div>
    </div>
  </div>
</div>
@endsection
@section('pagejs')
@include('default.shared._jquery_validation')
<script type="text/javascript">
$("#form").validate({
  submitHandler: function() {
    var load = layer.load();
    $("#form").ajaxSubmit(function(res) {
      layer.close(load);
      if (res.code == 200) {
        layer.msg('登录成功...', {time: 1500}, function() {window.location.href = '/'});
      } else if (res.code == 400) {
        layer.msg(res.message);
      } else {
        layer.msg('登录失败');
      }
    });
  }
});
</script>
@endsection

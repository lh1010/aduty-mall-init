@extends(Config('common.view.tpl_folder') . '.shared._layout')
@section('title', '用户注册' . ' ' . Config('common.pc.app_name'))
@section('keywords', '用户注册' . ' ' . Config('common.pc.app_name'))
@section('description', '用户注册' . ' ' . Config('common.pc.app_name'))
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
      <form class="layui-form" id="form" method="post" action="/api/account/register" autocomplete="off">
        <div class="title">用户注册</div>
        <ul class="u1">
          <li class="l1">昵称：</li>
          <li class="l2"><input type="text" name="nickname" placeholder="请输入昵称"></li>
        </ul>
        <ul class="u1">
          <li class="l1">手机：</li>
          <li class="l2"><input type="text" name="phone" id="phone" placeholder="请输入手机号码"></li>
        </ul>
        <ul class="u1 code">
          <li class="l1">验证码：</li>
          <li class="l2" id="send_code">
            <input type="text" name="code" placeholder="请输入验证码">
            <a href="javascript:void(0);" onclick="sendCode();">获取验证码</a>
          </li>
        </ul>
        <ul class="u1">
          <li class="l1">登录密码：</li>
          <li class="l2"><input type="password" name="password" placeholder="请输入登录密码"></li>
        </ul>
        <ul class="u1">
          <li class="l1">确认密码：</li>
          <li class="l2"><input type="password" name="password_confirm" placeholder="请输入确认密码"></li>
        </ul>
        <div class="d2">
          登录/注册代表同意本平台 <a href="/help/show/100000" target="_blank">用户协议</a> <a href="/help/show/100001" target="_blank">隐私协议</a>
        </div>
        <div class="dbtn"><input type="submit" value="立即注册"></div>
      </form>
      <div class="d1">
        <a class="a1" href="/login">手机验证码登录</a>
        <a class="a2" href="/login_password">密码登录</a>
      </div>

    </div>
  </div>
</div>
@endsection
@section('pagejs')
@include(Config('common.view.tpl_folder') . '.shared._jquery_validation')
<script type="text/javascript" src="/static/default/script/account.js?v={{Config('common.pc.version')}}"></script>
<script type="text/javascript">
$("#form").validate({
  submitHandler: function() {
    var load = layer.load();
    $("#form").ajaxSubmit(function(res) {
      layer.close(load);
      if (res.code == 200) {
        layer.msg('注册成功...', {time: 1500}, function() {window.location.href = '/'});
      } else if (res.code == 400) {
        layer.msg(res.message);
      } else {
        layer.msg('注册失败');
      }
    });
  }
});
</script>
@endsection

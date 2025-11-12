@extends('admin.shared._layout')
@section('pagecss')
<style>
body {
  display: -ms-flexbox;
  display: flex;
  -ms-flex-align: center;
  align-items: center;
  padding-top: 40px;
}
#form {
  width: 100%;
  max-width: 330px;
  padding: 15px;
  margin: auto;
}
#form .stitle {
  font-size: 22px;
}
.verification-code {
  margin-left: 5px;
  width: 100px;
  height: 38px;
}
.foot {
  color: #999;
  font-size: 12px;
}
</style>
@endsection
@section('content')
<form class="text-center" id="form" action="" method="post">
  @csrf
  <a href=""><img class="mb-4" src="/static/admin/images/logo.png" width="65" height="65"></a>
  <div class="stitle mb-4">后台登录</div>
  <input type="text" name="username" class="form-control mb-2" placeholder="用户名">
  <input type="password" name="password" class="form-control" placeholder="密码">
  <div class="input-group mt-2 d-none">
    <input type="text" class="form-control" placeholder="验证码">
    <a href=""><img class="form-control verification-code" src="images/logo.png"></a>
  </div>
  <div class="d-grid gap-2 mt-4">
    <button class="btn btn-primary" type="submit">登录</button>
  </div>
  <p class="foot mt-4">©{{date('Y')}} {{Config('common.app_name')}}</p>
</form>
@endsection
@section('pagejs')
@include('admin.shared._jquery_validation')
<script type="text/javascript">
$("#form").validate({
  submitHandler:function(){
    var load = layer.load();
    $("#form").ajaxSubmit(function(res) {
      layer.close(load);
      if (res.code == 200) {
        layer.msg('登录成功...', {time: 1500}, function() {window.location.href = '/admin'});
      } else if (res.code == 400) {
        layer.msg(res.message);
      } else {
        layer.msg('登录失败');
      }
    });
  }
});
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
@endsection

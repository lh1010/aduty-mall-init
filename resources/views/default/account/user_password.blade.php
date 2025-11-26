@extends(Config('common.view.tpl_folder') . '.account._layout_account')
@section('title', '修改密码 ' . Config('common.pc.app_name'))
@section('content')
<div class="account clearfix">
  <div class="container">
    @include(Config('common.view.tpl_folder') . '.account._left_menu')
    <div class="account_main">
      <div class="am_unify_box">
        <div class="top_nav">
          <a class="item {{ Request()->route()->getActionMethod() == 'user_info' ? 'on' : '' }}" href="/account/user_info">我的资料</a>
          <a class="item {{ Request()->route()->getActionMethod() == 'user_password' ? 'on' : '' }}" href="/account/user_password">修改密码</a>
          <a class="item {{ Request()->route()->getActionMethod() == 'user_contact' ? 'on' : '' }}" href="/account/user_contact">联系方式</a>
        </div>
        <form class="layui-form" autocomplete="off">
          @if(!empty($user->password))
          <div class="layui-form-item">
            <label class="layui-form-label"><span class="text-danger">*</span> 旧密码</label>
            <div class="layui-input-block">
              <input type="password" name="password_old" class="layui-input" required  lay-verify="required" >
            </div>
          </div>
          <div class="layui-form-item">
            <label class="layui-form-label"><span class="text-danger">*</span> 新密码</label>
            <div class="layui-input-block">
              <input type="password" name="password" class="layui-input" required  lay-verify="required" >
            </div>
          </div>
          <div class="layui-form-item">
            <label class="layui-form-label"><span class="text-danger">*</span> 确认新密码</label>
            <div class="layui-input-block">
              <input type="password" name="password_confirm" class="layui-input" required  lay-verify="required" >
            </div>
          </div>
          @else
          <div class="layui-form-item">
            <label class="layui-form-label"><span class="color_danger">*</span> 登录密码</label>
            <div class="layui-input-block">
              <input type="password" name="password" class="layui-input" required  lay-verify="required" >
            </div>
          </div>
          <div class="layui-form-item">
            <label class="layui-form-label"><span class="color_danger">*</span> 确认密码</label>
            <div class="layui-input-block">
              <input type="password" name="password_confirm" class="layui-input" required  lay-verify="required" >
            </div>
          </div>
          @endif
          <div class="layui-form-item">
            <div class="layui-input-block">
              <button class="layui-btn" lay-submit lay-filter="formSub">更新信息</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
@section('pagejs')
<script type="text/javascript">
layui.use('form', function() {
  var form = layui.form;
  form.on('submit(formSub)', function(data) {
    var load = layer.load();
    var data = data.field;
    data.user_token = $('#user_token').val();
    $.ajax({
      url: '/api/account/updateUserPassword',
      type: 'post',
      data: data,
      success: function(res) {
        layer.close(load);
        if (res.code == 200) {
          layer.msg('更新成功', {time: 1500}, function() {
            window.location.reload();
          });
        } else if (res.code == 400) {
          layer.msg(res.message);
        } else if (res.code == 401) {
          goLogin();
        } else {
          layer.msg('操作失败');
        }
      }
    })
    return false;
  });
});
</script>
@endsection

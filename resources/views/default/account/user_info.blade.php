@extends(Config('common.view.tpl_folder') . '.account._layout_account')
@section('title', '我的信息 ' . Config('common.pc.app_name'))
@section('content')
<div class="account clearfix">
  <div class="container">
    @include(Config('common.view.tpl_folder') . '.account._left_menu')
    <div class="account_main">
      <div class="am_unify_box">
        <div class="top_nav">
          <a class="item {{ Request()->route()->getActionMethod() == 'user_info' ? 'on' : '' }}" href="/account/user_info">我的信息</a>
          <a class="item {{ Request()->route()->getActionMethod() == 'user_password' ? 'on' : '' }}" href="/account/user_password">修改密码</a>
          <a class="item {{ Request()->route()->getActionMethod() == 'user_contact' ? 'on' : '' }}" href="/account/user_contact">联系方式</a>
        </div>
        <form class="layui-form" autocomplete="off">
          <div class="layui-form-item">
            <label class="layui-form-label">用户ID</label>
            <div class="layui-input-block">
              <div class="layui-form-mid layui-word-aux">{{$user->id}}</div>
            </div>
          </div>
          <div class="layui-form-item">
            <label class="layui-form-label">登录手机</label>
            <div class="layui-input-block">
              <div class="layui-form-mid layui-word-aux">{{$user->phone}}</div>
            </div>
          </div>
          <div class="layui-form-item">
            <label class="layui-form-label"><span class="text-danger">*</span> 昵称</label>
            <div class="layui-input-block">
              <input type="text" name="nickname" required  lay-verify="required" placeholder="请输入昵称" class="layui-input" value="{{$user->nickname}}" >
            </div>
          </div>
          <div class="layui-form-item">
            <label class="layui-form-label"><span class="text-danger">*</span> 性别</label>
            <div class="layui-input-block">
              <input type="radio" name="sex" value="男" title="男" @if($user->sex == '男') checked @endif>
              <input type="radio" name="sex" value="女" title="女" @if($user->sex == '女') checked @endif>
            </div>
          </div>
          <div class="layui-form-item">
            <label class="layui-form-label">头像</label>
            <div class="layui-input-block">
              <div class="layui-form-mid layui-word-aux">
                @if(!empty($user->avatar))
                <div class="luckFU uploaded" data-url="/api/upload?type=user" data-name="avatar" style="width: 80px; height: 80px;"><i class="luckFU_remove iconfont" href="javascript:void(0);" onclick="luckFU_delImage()"></i><img src="{{$user->avatar}}"><input type="hidden" name="avatar" value="{{$user->avatar}}"></div>
                @else
                <div class="luckFU" data-url="/api/upload?type=user" data-name="avatar" style="width: 80px; height: 80px;"></div>
                @endif
              </div>
            </div>
          </div>
          <div class="layui-form-item layui-form-text">
            <label class="layui-form-label">个人简介</label>
            <div class="layui-input-block">
              <textarea name="description" placeholder="请输入个人简介" class="layui-textarea">{{$user->description}}</textarea>
            </div>
          </div>
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
@include(Config('common.view.tpl_folder') . '.shared._jquery_validation')
<script type="text/javascript" src="/static/default/plugins/luck.file.upload.js"></script>
<script type="text/javascript">
layui.use('form', function() {
  var form = layui.form;
  form.on('submit(formSub)', function(data) {
    var load = layer.load();
    var data = data.field;
    data.user_token = $('#user_token').val();
    $.ajax({
      url: '/api/account/updateUser',
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

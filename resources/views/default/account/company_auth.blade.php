@extends(Config('common.view.tpl_folder') . '.account._layout_account')
@section('title', '企业认证 ' . Config('common.pc.app_name'))
@section('pagecss')
<link rel="stylesheet" type="text/css" href="/static/default/style/account.css?v={{Config('common.pc.version')}}" />
@endsection
@section('content')
<div class="account clearfix realname_auth">
  <div class="container">
    @include(Config('common.view.tpl_folder') . '.account._left_menu')
    <div class="account_main">
      <div class="am_unify_box">
        <div class="top_nav">
          <a class="item {{ Request()->route()->getActionMethod() == 'realname_auth' ? 'on' : '' }}" href="/account/realname_auth">实名认证</a>
          <a class="item {{ Request()->route()->getActionMethod() == 'company_auth' ? 'on' : '' }}" href="/account/company_auth">企业认证</a>
        </div>
        @if($user->company_auth == 0)
        <form class="layui-form" autocomplete="off">
          <div class="layui-form-item">
            <label class="layui-form-label">企业全称</label>
            <div class="layui-input-block">
              <input type="text" name="company_name" class="layui-input" placeholder="请输入企业全称" value="" >
            </div>
          </div>
          <div class="layui-form-item">
            <label class="layui-form-label">信用代码</label>
            <div class="layui-input-block">
              <input type="text" name="social_credit_code" class="layui-input" placeholder="请输入信用代码" value="" >
            </div>
          </div>
          <div class="layui-form-item">
            <label class="layui-form-label">营业执照</label>
            <div class="layui-input-block">
              <div class="layui-form-mid layui-word-aux">
                <div class="luckFU" data-url="/api/upload" data-name="business_license" style="width: 100px; height: 100px;"></div>
              </div>
            </div>
          </div>
          <div class="layui-form-item">
            <div class="layui-input-block">
              <button class="layui-btn" lay-submit lay-filter="formSub">提交信息</button>
            </div>
          </div>
        </form>
        @endif
        @if($user->company_auth != 0)
        <div>
          @if($user->company_auth == 1)
          <div class="audit_result">
            <img src="/static/default/images/审核中.png">
            <p class="message">企业认证信息审核中</p>
          </div>
          @endif
          @if($user->company_auth == 2)
          <div class="audit_result">
            <img src="/static/default/images/审核失败.png">
            <p class="message">失败原因：{{ ($user->company_auth_log && $user->company_auth_log->message) ? $user->company_auth_log->message : '无失败原因' }}</p>
            <div class="btns"><button type="button" class="layui-btn layui-btn-normal" onclick="companyAuthReset();">重新审核</button></div>
          </div>
          @endif
          @if($user->company_auth == 3)
          <div class="audit_result">
            <img src="/static/default/images/已审核.png">
            <p class="message">已成功企业认证</p>
          </div>
          @endif
        </div>
        @endif
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
      url: '/api/account/companyAuth',
      type: 'post',
      data: data,
      success: function(res) {
        layer.close(load);
        if (res.code == 200) {
          layer.msg('提交成功', {time: 1500}, function() {
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

// 重新审核
function companyAuthReset() {
  var layer_confirm = layer.confirm('确认重新审核？', function() {
    layer.close(layer_confirm);
    var load = layer.load();
    var data = {};
    data.user_token = $('#user_token').val();
    $.ajax({
      url: '/api/account/companyAuthReset',
      type: 'post',
      data: data,
      success: function(res) {
        layer.close(load);
        if (res.code == 200) {
          layer.msg('操作成功', {time: 1500}, function() {
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
  })
}
</script>
@endsection

@extends(Config('common.view.tpl_folder') . '.account._layout_account')
@section('title', '实名认证 ' . Config('common.pc.app_name'))
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
        @if($user->realname_auth == 0)
        <form class="layui-form" autocomplete="off">
          <div class="layui-form-item">
            <label class="layui-form-label">真实姓名</label>
            <div class="layui-input-block">
              <input type="text" name="realname" class="layui-input" value="" >
            </div>
          </div>
          <div class="layui-form-item">
            <label class="layui-form-label">身份证号</label>
            <div class="layui-input-block">
              <input type="text" name="idcard" class="layui-input" value="" >
            </div>
          </div>
          <div class="layui-form-item">
            <label class="layui-form-label">身份证照片</label>
            <div class="layui-input-block">
              <div class="idcard_images">
                <div class="items">
                  <div class="item item1">
                    <input type="hidden" name="idcard_img1" />
                    <div class="img"><img src="/static/default/images/identity_card1.png"></div>
                    <div class="btn">上传正面照</div>
                  </div>
                  <div class="item item2">
                    <input type="hidden" name="idcard_img2" />
                    <div class="img"><img src="/static/default/images/identity_card2.png"></div>
                    <div class="btn">上传反面照</div>
                  </div>
                </div>
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
        @if($user->realname_auth != 0)
        <div>
          @if($user->realname_auth == 1)
          <div class="audit_result">
            <img src="/static/default/images/审核中.png">
            <p class="message">实名认证信息审核中</p>
          </div>
          @endif
          @if($user->realname_auth == 2)
          <div class="audit_result">
            <img src="/static/default/images/审核失败.png">
            <p class="message">失败原因：{{ ($user->realname_auth_log && $user->realname_auth_log->message) ? $user->realname_auth_log->message : '无失败原因' }}</p>
            <div class="btns"><button type="button" class="layui-btn layui-btn-normal" onclick="realnameAuthReset();">重新审核</button></div>
          </div>
          @endif
          @if($user->realname_auth == 3)
          <div class="audit_result">
            <img src="/static/default/images/已审核.png">
            <p class="message">已成功实名认证</p>
          </div>
          @endif
        </div>
        @endif
      </div>
    </div>
  </div>
</div>

<form action="/api/upload" method="post" id="upload_form" enctype="multipart/form-data" autocomplete="off" class="none">
  <input type="hidden" name="user_token" value="{{Cookie::get('user_token')}}">
  <input type="file" name="file" id="upload_file" />
</form>
@endsection
@section('pagejs')
@include(Config('common.view.tpl_folder') . '.shared._jquery_validation')
<script type="text/javascript">
layui.use('form', function() {
  var form = layui.form;
  form.on('submit(formSub)', function(data) {
    var load = layer.load();
    var data = data.field;
    data.user_token = $('#user_token').val();
    $.ajax({
      url: '/api/account/realnameAuth',
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
function realnameAuthReset() {
  var layer_confirm = layer.confirm('确认重新审核？', function() {
    layer.close(layer_confirm);
    var load = layer.load();
    var data = {};
    data.user_token = $('#user_token').val();
    $.ajax({
      url: '/api/account/realnameAuthReset',
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

$(document).ready(function() {
  var ident = '';

  $('.idcard_images .item1').click(function() {
    ident = 'item1';
    $('#upload_file').val('');
    $('#upload_file').click();
  })

  $('.idcard_images .item2').click(function() {
    ident = 'item2';
    $('#upload_file').val('');
    $('#upload_file').click();
  })

  $('#upload_file').change(function(event) {
    var load = layer.load();
    $("#upload_form").ajaxSubmit(function(res) {
      layer.close(load);
      if (res.code == 200) {
        if (ident == 'item1') {
          $('.item1 img').attr('src', res.data.path);
          $('input[name="idcard_img1"]').val(res.data.path);
        }
        if (ident == 'item2') {
          $('.item2 img').attr('src', res.data.path);
          $('input[name="idcard_img2"]').val(res.data.path);
        }
      } else if (res.code == 400) {
        layer.msg(res.message);
      } else if (res.code == 401) {
        goLogin();
      } else {
        layer.msg('操作失败');
      }
    });
  })
})
</script>
@endsection

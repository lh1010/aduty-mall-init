<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title>创建地址</title>
<link rel="shortcut icon" href="/favicon.ico?v={{Config('common.pc.version')}}" />
<link rel="stylesheet" type="text/css" href="/static/default/fonts/iconfont/iconfont.css?v={{Config('common.pc.version')}}" />
<link rel="stylesheet" type="text/css" href="/static/default/plugins/layui/css/layui.css?v={{Config('common.pc.version')}}" />
<link rel="stylesheet" type="text/css" href="/static/default/style/base.css?v={{Config('common.pc.version')}}" />
<link rel="stylesheet" type="text/css" href="/static/default/style/style.css?v={{Config('common.pc.version')}}" />
<link rel="stylesheet" type="text/css" href="/static/default/style/address.css?v={{Config('common.pc.version')}}" />
<style type="text/css">
html, body {
  max-width: 520px; 
  min-width: 520px;
  background-color: #fff !important; 
  margin: 0 auto;
}
</style>
</head>
<body>
<input type="hidden" id="user_token" value="{{Cookie::get('user_token')}}">
<div class="popup-address">
  <div class="box">
    <form class="layui-form" autocomplete="off">
      <div class="layui-form-item">
        <label class="layui-form-label">收货人姓名</label>
        <div class="layui-input-block">
          <input type="text" name="name" placeholder="请输入" class="layui-input">
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">收货地址</label>
        <div class="layui-input-inline province_id" style="width: 110px;">
          <select lay-filter="select-city" name="province_id">
            <option value="">请选择省</option>
            @foreach($citys as $key => $value)
            <option value="{{$value->id}}">{{$value->name}}</option>
            @endforeach
          </select>
        </div>
        <div class="layui-input-inline city_id none" style="width: 110px;">
          <select lay-filter="select-city" name="city_id">
            <option value="">请选择市</option>
          </select>
        </div>
        <div class="layui-input-inline district_id none" style="width: 110px;">
          <select lay-filter="select-city" name="district_id">
            <option value="">请选择区</option>
          </select>
        </div>
      </div> 
      <div class="layui-form-item">
        <label class="layui-form-label">详细地址</label>
        <div class="layui-input-block">
          <textarea name="detailed_address" placeholder="请输入内容" class="layui-textarea"></textarea>
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">手机号</label>
        <div class="layui-input-block">
          <input type="text" name="phone" placeholder="请输入" class="layui-input">
        </div>
      </div>
      <div class="layui-form-item">
        <div class="layui-input-block">
          <button class="layui-btn layui-btn-luck" lay-submit lay-filter="formSub">立即提交</button>
        </div>
      </div>
    </form>
  </div>
</div>
<script type="text/javascript" src="/static/default/plugins/jquery/3.3.1/jquery.js?v={{Config('common.pc.version')}}"></script>
<script type="text/javascript" src="/static/default/plugins/layer/2.4/layer.js?v={{Config('common.pc.version')}}"></script>
<script type="text/javascript" src="/static/default/plugins/layui/layui.js?v={{Config('common.pc.version')}}"></script>
<script type="text/javascript" src="/static/default/plugins/jquery.lazyload.js?v=1.0.1"></script>
<script type="text/javascript" src="/static/default/script/common.js?v={{Config('common.pc.version')}}"></script>
@include(Config('common.view.tpl_folder') . '.shared._jquery_validation')
<script type="text/javascript">
layui.use('form', function() {
  var form = layui.form;
  form.on('submit(formSub)', function(data) {
    var load = layer.load();
    var data = data.field;
    data.user_token = $('#user_token').val();
    $.ajax({
      url: '/api/address/store',
      type: 'post',
      data: data,
      success: function(res) {
        layer.close(load);
        if (res.code == 200) {
          layer.msg('操作成功', {time: 1500}, function() {
            window.parent.location.reload();
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

layui.use(function(){
  var form = layui.form;
  var layer = layui.layer;
  // select 事件
  form.on('select(select-city)', function(data) {
    var elem = data.elem; // 获得 select 原始 DOM 对象
    var value = data.value; // 获得被选中的值
    var name = elem.name;
    if (name == 'province_id') {
      $(".city_id").hide();
      $("select[name='city_id']").html('<option value="">请选择</option>');
      $(".district_id").hide();
      $("select[name='district_id']").html('<option value="">请选择</option>');
      form.render('select');
    }
    if (name == 'city_id') {
      $(".district_id").hide();
      $("select[name='district_id']").html('<option value="">请选择</option>');
      form.render('select');
    }
    if (name == 'district_id' || value == '') {
      return false;
    }
    var str = '';
    var load = layer.load();
    $.ajax({
      url: '/api/common/getCityList',
      type: 'post',
      data: { pid: value },
      success: function(data) {
        layer.close(load);
        if (data.code == 200) {
          var str = '<option value="">请选择</option>';
          for (var i = data.data.length - 1; i >= 0; i--) {
            str += '<option value="'+data.data[i].id+'">'+data.data[i].name+'</option>';
          }
          if (name == 'province_id') {
            $(".city_id").show();
            $("select[name='city_id']").html(str);
          }
          if (name == 'city_id') {
            $(".district_id").show();
            $("select[name='district_id']").html(str);
          }
          // 重新渲染 select 组件
          form.render('select');
        } else {
          layer.msg('操作异常');
          return false;
        }
      }
    })
  });
});

function validateCity()
{
  if ($("select[name='province_id']").val() == '' || $("select[name='city_id']").val() == '' || $("select[name='district_id']").val() == '') {
    layer.msg('收货地址不能为空');
    return true;
  }
}
</script>
</body>
</html>

@extends('admin.shared._layout')
@section('content')
<div class="main">
  <div class="bg-white p-4">
    <ol class="breadcrumb">
      <li class="breadcrumb-item">角色管理</li>
      <li class="breadcrumb-item active">创建</li>
    </ol>
    <div class="breadcrumb-stitle">
      <a href="javascript:history.go(-1);"><i class="iconfont goback"></i></a>
      <span class="txt">创建</span>
    </div>
  </div>
  <ul class="form-message m-4 p-4"></ul>
  <form class="mt-4" id="form" action="/admin/auth/role_store" method="post" autocomplete="off">
    @csrf
    <div class="m-4 pagebox">
      <div class="row mb-3">
        <label class="col-2 col-form-label text-end"><span class="text-danger">*</span> 角色名：</label>
        <div class="col-8">
          <input class="form-control" name="name" placeholder="">
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-2 col-form-label text-end">描述：</label>
        <div class="col-8">
          <textarea class="form-control" name="description"></textarea>
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-2 col-form-label text-end">状态：</label>
        <div class="col-auto">
          <select class="form-select" name="status">
            <option value="1">开启</option>
            <option value="0">关闭</option>
          </select>
        </div>
      </div>
    </div>
    <div class="form-foot-blank"></div>
    <div class="form-foot">
      <div class="box">
        <button type="submit" class="btn btn-primary">提交</button>
      </div>
    </div>
  </form>
</div>
@endsection
@section('pagejs')
<script type="text/javascript" src="/static/plugins/luck.file.upload.js"></script>
<script type="text/javascript" src="/static/plugins/ejecttime/WdatePicker.js"></script>
@include('admin.shared._jquery_validation')
<script type="text/javascript">
$("#form").validate({
  rules: {
    name: {required: true},
  },
  messages: {
    name: '角色名不能为空',
  },
  submitHandler: function() {
    layer.confirm('确认提交？', function() {
      layer.closeAll();
      var load = layer.load();
      $("#form").ajaxSubmit(function(res) {
        layer.close(load);
        if (res.code == 401) {
          goLogin(); return false;
        }
        if (res.code == 200) {
          layer.msg('提交成功', { time: 1500 }, function () { window.location.href = '/admin/auth/role_list'; });
        } else if (res.code == 400) {
          layer.msg(res.message); return false;
        } else {
          layer.msg('操作失败'); return false;
        }
      });
    });
  }
});
</script>
@endsection
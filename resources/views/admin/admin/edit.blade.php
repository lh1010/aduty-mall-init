@extends('admin.shared._layout')
@section('content')
<div class="main">
  <div class="bg-white p-4">
    <ol class="breadcrumb">
      <li class="breadcrumb-item">管理员列表</li>
      <li class="breadcrumb-item active">编辑</li>
    </ol>
    <div class="breadcrumb-stitle">
      <a href="javascript:history.go(-1);"><i class="iconfont goback"></i></a>
      <span class="txt">编辑</span>
    </div>
  </div>

  <ul class="form-message m-4 p-4"></ul>
  <form class="mt-4" id="form" action="/admin/admin/update" method="post">
    @csrf
    <input type="hidden" name="id" value="{{$admin->id}}" />
    <div class="m-4 pagebox">
      <div class="row mb-3 mt-3">
        <label class="col-2 col-form-label text-end"><span class="text-danger">*</span> 账号：</label>
        <div class="col-8">
          <input class="form-control" name="username" value="{{$admin->username}}" placeholder="">
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-2 col-form-label text-end"><i class="iconfont iconfont-question" data-bs-toggle="tooltip" title="" data-bs-original-title="留空为不修改"></i> 密码：</label>
        <div class="col-8">
          <input class="form-control" name="password" type="password" placeholder="">
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-2 col-form-label text-end">姓名：</label>
        <div class="col-8">
          <input class="form-control" name="realname" value="{{$admin->realname}}" placeholder="">
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-2 col-form-label text-end">邮箱：</label>
        <div class="col-8">
          <input class="form-control" name="email" value="{{$admin->email}}" placeholder="">
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-2 col-form-label text-end">手机：</label>
        <div class="col-8">
          <input class="form-control" name="phone" value="{{$admin->phone}}" placeholder="">
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-2 col-form-label text-end">备注：</label>
        <div class="col-8">
          <textarea class="form-control" rows="3" name="remark">{{$admin->remark}}</textarea>
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-2 col-form-label text-end">状态：</label>
        <div class="col-auto">
          <select class="form-select" name="status">
            <option value="1" @if($admin->status == 1) selected @endif>开启</option>
            <option value="0" @if($admin->status == 0) selected @endif>关闭</option>
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
@include('admin.shared._jquery_validation')
<script type="text/javascript">
$("#form").validate({
  rules: {
    username: { required: true },
  },
  messages: {
    username: { required: '用户名不能为空' },
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
          layer.msg('提交成功', { time: 1500 }, function () { window.location.href = '/admin/admin/list'; });
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
@extends('admin.shared._layout')
@section('content')
<div class="main">
  <div class="bg-white p-4">
    <ol class="breadcrumb">
      <li class="breadcrumb-item">用户中心</li>
      <li class="breadcrumb-item">用户列表</li>
      <li class="breadcrumb-item active">编辑</li>
    </ol>
    <div class="breadcrumb-stitle">
      <a href="javascript:history.go(-1);"><i class="iconfont goback"></i></a>
      <span class="txt">编辑</span>
    </div>
  </div>

  <ul class="form-message m-4 p-4"></ul>
  <form class="mt-4" id="form" action="/admin/user/update" method="post" autocomplete="off">
    @csrf
    <input type="hidden" name="id" value="{{$user->id}}">
    <div class="m-4 pagebox">
      <ul class="nav nav-tabs mb-4 form-nav-tabs">
        <li class="nav-item"><a class="nav-link active" href="javascript:void(0);" data-id="#a">基础信息</a></li>
        <li class="nav-item"><a class="nav-link" href="javascript:void(0);" data-id="#b">联系方式</a></li>
      </ul>
      <div class="form-tab" id="a">
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end"><i class="iconfont luck-icon-question" data-bs-toggle="tooltip" data-bs-original-title="为空自动生成"></i> 昵称：</label>
          <div class="col-8">
            <input class="form-control" name="nickname" value="{{$user->nickname}}">
          </div>
        </div>
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end"><span class="text-danger">*</span> 手机：</label>
          <div class="col-8">
            <input class="form-control" name="phone" value="{{$user->phone}}">
          </div>
        </div>
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end"><i class="iconfont luck-icon-question" data-bs-toggle="tooltip" data-bs-original-title="留空为不修改"></i> 密码：</label>
          <div class="col-8">
            <input class="form-control" name="password" value="">
          </div>
        </div>
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">头像：</label>
          <div class="col-auto">
            @if($user && !empty($user->avatar))
            <div class="luckFU uploaded" style="width: 80px; height: 80px;" data-name="avatar" data-url="/admin/upload">
              <i class="luckFU_remove iconfont" href="javascript:void(0);"></i>
              <img src="{{$user->avatar}}">
              <input type="hidden" name="avatar" value="{{$user->avatar}}">
            </div>
            @else
            <div class="luckFU" style="width: 80px; height: 80px;" data-name="avatar" data-url="/admin/upload">
              <input type="hidden" name="avatar" value="">
            </div>
            @endif
          </div>
        </div>
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">性别：</label>
          <div class="col-auto">
            <select class="form-select" name="sex">
              <option value="男" @if($user->sex == '男') selected @endif>男</option>
              <option value="女" @if($user->sex == '女') selected @endif>女</option>
            </select>
          </div>
        </div>
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">介绍：</label>
          <div class="col-8">
            <textarea class="form-control" rows="3" name="description">{{$user->description}}</textarea>
          </div>
        </div>
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">状态：</label>
          <div class="col-auto">
            <select class="form-select" name="status">
              @foreach(Config('common.user.status') as $key => $value)
              <option value="{{$key}}" @if($user->status == $key) selected @endif>{{$value}}</option>
              @endforeach
            </select>
          </div>
        </div>
      </div>
      <div class="form-tab" id="b">
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">微信：</label>
          <div class="col-4">
            <input class="form-control" name="contact[weixin]" value="{{$user->contact->weixin}}" placeholder="">
          </div>
        </div>
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">手机：</label>
          <div class="col-4">
            <input class="form-control" name="contact[phone]" value="{{$user->contact->phone}}" placeholder="">
          </div>
        </div>
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">电话：</label>
          <div class="col-4">
            <input class="form-control" name="contact[telphone]" value="{{$user->contact->telphone}}" placeholder="">
          </div>
        </div>
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">Q Q：</label>
          <div class="col-4">
            <input class="form-control" name="contact[qq]" value="{{$user->contact->qq}}" placeholder="">
          </div>
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
<script type="text/javascript" src="/static/admin/plugins/luck.file.upload.js"></script>
<script type="text/javascript">
$("#form").validate({
  rules: {
    phone: { required: true },
  },
  messages: {
    phone: { required: '手机不能为空' },
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
          layer.msg('提交成功', { time: 1500 }, function () { window.location.href = '/admin/user/list{!! Request()->get("prevPageParams") ? "?" . urldecode(Request()->get("prevPageParams")) : '' !!}'; });
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

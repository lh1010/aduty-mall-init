@extends('admin.shared._layout')
@section('content')
<div class="main">
  <div class="bg-white p-4">
    <ol class="breadcrumb">
      <li class="breadcrumb-item">地区管理</li>
      <li class="breadcrumb-item active">编辑</li>
    </ol>
    <div class="breadcrumb-stitle">
      <a href="javascript:history.go(-1);"><i class="iconfont goback"></i></a>
      <span class="txt">编辑</span>
    </div>
  </div>
  <ul class="form-message m-4 p-4"></ul>
  <form class="mt-4" id="form" action="/admin/city/update" method="post" autocomplete="off">
    @csrf
    <input type="hidden" name="id" value="{{$city->id}}">
    <div class="m-4 pagebox">
      <div class="row mb-3">
        <label class="col-2 col-form-label text-end"><span class="text-danger">*</span> 简称：</label>
        <div class="col-8">
          <input class="form-control" name="shortname" value="{{$city->shortname}}">
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-2 col-form-label text-end"><span class="text-danger">*</span> 名称：</label>
        <div class="col-8">
          <input class="form-control" name="name" value="{{$city->name}}">
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-2 col-form-label text-end"><span class="text-danger">*</span> 拼音：</label>
        <div class="col-8">
          <input class="form-control" name="pinyin" value="{{$city->pinyin}}">
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-2 col-form-label text-end"><span class="text-danger">*</span> 首字母：</label>
        <div class="col-8">
          <input class="form-control" name="first" value="{{$city->first}}">
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-2 col-form-label text-end">邮政编码：</label>
        <div class="col-8">
          <input class="form-control" name="zip" value="{{$city->zip}}">
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-2 col-form-label text-end">长途区号：</label>
        <div class="col-8">
          <input class="form-control" name="code" value="{{$city->code}}">
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-2 col-form-label text-end">经度：</label>
        <div class="col-8">
          <input class="form-control" name="lng" value="{{$city->lng}}">
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-2 col-form-label text-end">纬度：</label>
        <div class="col-8">
          <input class="form-control" name="lat" value="{{$city->lat}}">
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-2 col-form-label text-end">排序：</label>
        <div class="col-8">
          <input class="form-control" name="sort" value="{{$city->sort}}">
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-2 col-form-label text-end">状态：</label>
        <div class="col-auto">
          <select class="form-select" name="status">
            <option value="1" @if($city->status == '1') selected @endif>开启</option>
            <option value="0" @if($city->status == '0') selected @endif>关闭</option>
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
<script type="text/javascript" src="/static/admin/plugins/luck.file.upload.js"></script>
<script type="text/javascript">
$("#form").validate({
  rules: {
    nickname: { required: true },
    phone: { required: true },
  },
  messages: {
    nickname: { required: '昵称不能为空' },
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
          layer.msg('提交成功', { time: 1500 }, function () { window.location.href = '/admin/city/list{!! Request()->get("prevPageParams") ? "?" . urldecode(Request()->get("prevPageParams")) : '' !!}'; });
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
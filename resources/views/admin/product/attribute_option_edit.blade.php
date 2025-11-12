@extends('admin.shared._layout')
@section('content')
<div class="main">
  <div class="bg-white p-4">
    <ol class="breadcrumb">
      <li class="breadcrumb-item">选项值</li>
      <li class="breadcrumb-item active">编辑</li>
    </ol>
    <div class="breadcrumb-stitle">
      <a href="javascript:history.go(-1);"><i class="iconfont goback"></i></a>
      <span class="txt">编辑</span>
    </div>
  </div>
  <ul class="form-message m-4 p-4"></ul>
  <form class="mt-4" id="form" action="/admin/product/attribute_option_update" method="post" autocomplete="off">
    @csrf
    <input type="hidden" name="id" value="{{$option->id}}">
    <div class="m-4 pagebox">
      <div class="row mb-3">
        <label class="col-2 col-form-label text-end"><span class="text-danger">*</span> 选项值：</label>
        <div class="col-8">
          <input class="form-control" name="option" value="{{$option->option}}" placeholder="">
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-2 col-form-label text-end">所属属性：</label>
        <div class="col-auto">
          <input type="text" readonly="" class="form-control-plaintext" value="{{$attribute->name}}" />
          <input type="hidden" name="attribute_id" value="{{$attribute->id}}" />
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-2 col-form-label text-end">排序：</label>
        <div class="col-auto">
          <input class="form-control" name="sort" value="{{$option->sort}}" placeholder="">
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-2 col-form-label text-end">状态：</label>
        <div class="col-auto">
          <select class="form-select" name="status">
            <option value="1" @if($option->status == 1) selected @endif>开启</option>
            <option value="0" @if($option->status == 0) selected @endif>关闭</option>
          </select>
        </div>
      </div>
    </div>
    <div class="form-foot-blank"></div>
    <div class="form-foot">
      <div class="box">
        <button type="submit" class="btn btn-primary">提交信息</button>
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
    option: {required: true},
  },
  messages: {
    option: '选项值不能为空',
  },
  submitHandler: function() {
    layer.confirm('确认提交？', function() {
      layer.closeAll();
      var load = layer.load();
      $("#form").ajaxSubmit(function(res) {
        layer.close(load);
        if (res.code == 200) {
          layer.msg('操作成功', { time: 1500 }, function () { window.location.href = '/admin/product/attribute_option_list?attribute_id={{Request()->attribute_id}}'; });
        } else if (res.code == 400) {
          layer.msg(res.message);
          return false;
        } else if (res.code == 401) {
          goLogin();
          return false;
        } else {
          layer.msg('操作失败');
          return false;
        }
      });
    });
  }
});
</script>
@endsection
@extends('admin.shared._layout')
@section('pagecss')
<style type="text/css">
html, body {
  min-width: 100%;
  max-width: 100%;
  background: #fff;
}
.main {
  padding: 20px;
}
</style>
@endsection
@section('content')
<div class="main">
  <form class="mt-4" id="form" action="" method="post" autocomplete="off">
    @csrf
    <div class="row mb-3">
      <label class="col-2 col-form-label text-end">模板名称：</label>
      <div class="col-auto">
        <input type="text" readonly="" class="form-control-plaintext" value="{{$res['name']}}">
      </div>
    </div>
    <div class="row mb-3">
      <label class="col-2 col-form-label text-end">模板内容：</label>
      <div class="col-auto">
        <div class="form-control-plaintext">{{$res['content']}}</div>
      </div>
    </div>
    <div class="row mb-3">
      <label class="col-2 col-form-label text-end">模板类型：</label>
      <div class="col-auto">
        <input type="text" readonly="" class="form-control-plaintext" value="{{$res['type']}}">
      </div>
    </div>
    <div class="row mb-3">
      <label class="col-2 col-form-label text-end"><i class="iconfont iconfont-question" data-bs-toggle="tooltip" title="请在三方短信平台找到该值"></i> code：</label>
      <div class="col-8">
        <input class="form-control" name="sms[template][{{Request()->id}}][tpl_code]" value="{{$res['tpl_code']}}">
      </div>
    </div>
    <div class="row mb-3">
      <label class="col-2 col-form-label text-end"></label>
      <div class="col-8">
        <button type="submit" class="btn btn-primary">提交信息</button>
      </div>
    </div>
  </form>
</div>
@endsection
@section('pagejs')
@include('admin.shared._jquery_validation')
<script type="text/javascript">
 $("#form").validate({
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
          window.parent.location.reload();
          var index = parent.layer.getFrameIndex(window.name);
          parent.layer.close(index);
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

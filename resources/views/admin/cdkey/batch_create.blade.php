@extends('admin.shared._layout')
@section('content')
<div class="main">
  <div class="bg-white p-4">
    <ol class="breadcrumb">
      <li class="breadcrumb-item">卡密管理</li>
      <li class="breadcrumb-item active">批量新增</li>
    </ol>
    <div class="breadcrumb-stitle">
      <a href="javascript:history.go(-1);"><i class="iconfont goback"></i></a>
      <span class="txt">批量新增</span>
    </div>
  </div>
  <ul class="form-message m-4 p-4"></ul>
  <form class="mt-4" id="form" action="/admin/cdkey/batch_store" method="post" autocomplete="off">
    @csrf
    <div class="m-4 pagebox">
      <div class="row mb-3">
        <label class="col-2 col-form-label text-end">
          <i class="iconfont iconfont-question" data-bs-toggle="tooltip" title="" data-bs-original-title="以逗号分割内容，如：aaa,bbb,ccc"></i>
          批量卡密：
        </label>
        <div class="col-8">
          <textarea class="form-control" name="keys" placeholder=""></textarea>
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-2 col-form-label text-end">兑换金币：</label>
        <div class="col-2">
          <input class="form-control" name="gold" value="10" placeholder="">
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-2 col-form-label text-end"><i class="iconfont iconfont-question" data-bs-toggle="tooltip" title="" data-bs-original-title="过期时间，留空为永久"></i> 过期时间：</label>
        <div class="col-2">
          <input class="form-control" name="end_date" value="" onclick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:ss'})" placeholder="过期时间，留空为永久">
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-2 col-form-label text-end"><i class="iconfont iconfont-question" data-bs-toggle="tooltip" title="" data-bs-original-title="指定用户ID，0为不限制"></i> 指定用户：</label>
        <div class="col-2">
          <input class="form-control" name="assign_user_id" value="0" placeholder="指定用户ID，0为不限制">
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-2 col-form-label text-end">备注：</label>
        <div class="col-8">
          <textarea class="form-control" name="remark" placeholder=""></textarea>
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
@include('admin.shared._jquery_validation')
<script type="text/javascript" src="/static/admin/plugins/luck.file.upload.js"></script>
<script type="text/javascript" src="/static/admin/plugins/ejecttime/WdatePicker.js"></script>
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
        } else if (res.code == 200) {
          layer.msg('提交成功', { time: 1500 }, function () { window.location.href = '/admin/cdkey/list'; });
        } else if (res.code == 400) {
          layer.alert(res.message);
        } else {
          layer.msg('操作失败');
        }
      });
    });
  }
});

function randomKey() {
  var str = randomStr();
  $('input[name="key"]').val(str);
}
</script>
@endsection

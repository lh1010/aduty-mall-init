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
  padding-bottom: 30px;
}
</style>
@endsection
@section('content')
<div class="main">
  <form class="mt-3" id="form" action="" method="post" autocomplete="off">
    @csrf
    <input type="hidden" name="id" value="{{$order->id}}">
    <div class="row mb-3">
      <label class="col-4 col-form-label text-end">订单编号：</label>
      <div class="col-auto">
        <div class="form-control-plaintext">{{$order->number}}</div>
      </div>
    </div>
    <div class="row mb-3">
      <label class="col-4 col-form-label text-end">物流公司：</label>
      <div class="col-auto">
        <select class="form-select" name="shipping_company">
          @foreach(Config('common.mall.shipping_company') as $value)
          <option value="{{$value['name']}}">{{$value['name']}}</option>
          @endforeach
        </select>
      </div>
    </div>
    <div class="row mb-3">
      <label class="col-4 col-form-label text-end">物流单号：</label>
      <div class="col-auto">
        <input class="form-control" name="tracking_number" value="" placeholder="">
      </div>
    </div>
    <div class="row mt-4 mb-3">
      <label class="col-4 col-form-label text-end"></label>
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
    layer.confirm('确认发货？', function() {
      layer.closeAll();
      var load = layer.load();
      $("#form").ajaxSubmit(function(res) {
        layer.close(load);
        if (res.code == 401) {
          goLogin(); return false;
        } else if (res.code == 200) {
          layer.msg('操作成功', { time: 1500 }, function () {
            window.parent.location.reload();
            var index = parent.layer.getFrameIndex(window.name);
            parent.layer.close(index);
          });
        } else if (res.code == 400) {
          layer.alert(res.message); return false;
        } else {
          layer.msg('操作失败'); return false;
        }
      });
    });
  }
});
</script>
@endsection

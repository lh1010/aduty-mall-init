@extends('admin.shared._layout')
@section('content')
<div class="main">
	<div class="bg-white p-4">
	  <div class="breadcrumb-stitle">支付设置</div>
	</div>
	<div class="pagetopnav mx-4 mt-4">
		<div class="items">
			<a class="item {{ in_array(Request()->route()->getActionMethod(), ['payment_weixinpay']) ? 'on' : '' }}" href="/admin/set/payment_weixinpay">微信支付</a>
			<a class="item {{ in_array(Request()->route()->getActionMethod(), ['payment_alipay']) ? 'on' : '' }}" href="/admin/set/payment_alipay">支付宝支付</a>
		</div>
	</div>
	<ul class="form-message m-4 p-4"></ul>
  <form class="mt-4" id="form" action="" method="post" autocomplete="off">
    @csrf
    <div class="m-4 pagebox">
      <div class="mt-4">
      	<div class="row mb-3">
          <label class="col-2 col-form-label text-end">商户号ID：</label>
          <div class="col-8">
            <input class="form-control" name="alipay[appid]" value="{{Config('common.alipay.appid')}}">
          </div>
        </div>
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">商户私钥：</label>
          <div class="col-8">
            <input class="form-control" name="alipay[rsaPrivateKey]" value="{{Config('common.alipay.rsaPrivateKey')}}">
          </div>
        </div>
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">支付宝公钥：</label>
          <div class="col-8">
            <input class="form-control" name="alipay[alipayPublicKey]" value="{{Config('common.alipay.alipayPublicKey')}}">
          </div>
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
<script type="text/javascript" src="/static/plugins/luck.file.upload.js"></script>
<script type="text/javascript">
$("#form").validate({
  submitHandler: function() {
    layer.confirm('确认提交？', function() {
      layer.closeAll();
      var load = layer.load();
      $("#form").ajaxSubmit(function(res) {
        layer.close(load);
        if (res.code == 200) {
          layer.msg('操作成功', { time: 1500 }, function () { window.location.reload(); });
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
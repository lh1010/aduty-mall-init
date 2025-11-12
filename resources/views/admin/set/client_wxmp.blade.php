@extends('admin.shared._layout')
@section('content')
<div class="main">
	<div class="bg-white p-4">
	  <div class="breadcrumb-stitle">应用配置</div>
	</div>
	<div class="pagetopnav mx-4 mt-4">
		<div class="items">
			<a class="item {{ in_array(Request()->route()->getActionMethod(), ['client_pc']) ? 'on' : '' }}" href="/admin/set/client_pc">电脑网站</a>
			<a class="item {{ in_array(Request()->route()->getActionMethod(), ['client_wxapp']) ? 'on' : '' }}" href="/admin/set/client_wxapp">微信小程序</a>
			<a class="item {{ in_array(Request()->route()->getActionMethod(), ['client_wxmp']) ? 'on' : '' }}" href="/admin/set/client_wxmp">微信公众号</a>
		</div>
	</div>
	<ul class="form-message m-4 p-4"></ul>
  <form class="mt-4" id="form" action="" method="post" autocomplete="off">
    @csrf
    <div class="m-4 pagebox">
      <div class="mt-4">
      	<div class="row mb-3">
		      <label class="col-2 col-form-label text-end">appid：</label>
		      <div class="col-8">
		        <input class="form-control" name="wxmp[appid]" value="{{Config('common.wxmp.appid')}}">
		      </div>
		    </div>
		    <div class="row mb-3">
		      <label class="col-2 col-form-label text-end">secret：</label>
		      <div class="col-8">
		        <input class="form-control" name="wxmp[secret]" value="{{Config('common.wxmp.secret')}}">
		      </div>
		    </div>
		    <div class="row mb-3">
		      <label class="col-2 col-form-label text-end"><i class="iconfont iconfont-question" data-bs-toggle="tooltip" title="" data-bs-original-title="公众号二维码"></i> 二维码：</label>
		      <div class="col-auto">
		        @if(Config('common.wxmp.qrcode'))
		        <div class="luckFU uploaded" style="width: 80px; height: 80px;" data-name="wxmp[qrcode]" data-url="/api/upload"><i class="luckFU_remove iconfont" href="javascript:void(0);"></i><img src="{{Config('common.wxmp.qrcode')}}"><input type="hidden" name="wxmp[qrcode]" value="{{Config('common.wxmp.qrcode')}}"></div>
		        @else
		        <div class="luckFU" style="width: 80px; height: 80px;" data-name="wxmp[qrcode]" data-url="/api/upload">
		          <input type="hidden" name="wxmp[qrcode]" value="">
		        </div>
		        @endif
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
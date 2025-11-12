@extends('admin.shared._layout')
@section('content')
<div class="main">
	<div class="bg-white p-4">
	  <div class="breadcrumb-stitle">系统设置</div>
	</div>
	<ul class="form-message m-4 p-4"></ul>
  <form class="mt-4" id="form" action="" method="post" autocomplete="off">
    @csrf
    <div class="m-4 pagebox">
    	<div><b>基础信息</b></div>
      <div class="mt-4">
        <div class="row mb-3">
		      <label class="col-2 col-form-label text-end">系统名字：</label>
		      <div class="col-8">
		        <input class="form-control" name="app_name" value="{{Config('common.app_name')}}">
		      </div>
		    </div>
		    <div class="row mb-3">
		      <label class="col-2 col-form-label text-end">系统域名：</label>
		      <div class="col-8">
		        <input class="form-control" name="app_url" value="{{Config('common.app_url')}}">
		      </div>
		    </div>
		    <div class="row mb-3">
		      <label class="col-2 col-form-label text-end">版权声明：</label>
		      <div class="col-8">
		        <input class="form-control" name="copyright" value="{{Config('common.copyright')}}">
		      </div>
		    </div>
		    <div class="row mb-3">
		      <label class="col-2 col-form-label text-end">备案信息：</label>
		      <div class="col-8">
		        <input class="form-control" name="beian" value="{{Config('common.beian')}}">
		      </div>
		    </div>
		    <div class="row mb-3">
		      <label class="col-2 col-form-label text-end">当前版本：</label>
		      <div class="col-8">
		        <input class="form-control" name="version" value="{{Config('common.version')}}">
		      </div>
		    </div>
		    <div class="row mb-3">
		      <label class="col-2 col-form-label text-end"><i class="iconfont iconfont-question" data-bs-toggle="tooltip" title="" data-bs-original-title="使用字体位置"></i> 字体位置：</label>
		      <div class="col-8">
		        <input class="form-control" name="font_path" value="{{Config('common.font_path')}}">
		      </div>
		    </div>
      </div>
    </div>
    <div class="m-4 pagebox">
    	<div><b>客服信息</b></div>
      <div class="mt-4">
        <div class="row mb-3">
		      <label class="col-2 col-form-label text-end">手机：</label>
		      <div class="col-8">
		        <input class="form-control" name="contact[phone]" value="{{Config('common.contact.phone')}}">
		      </div>
		    </div>
		    <div class="row mb-3">
		      <label class="col-2 col-form-label text-end">微信：</label>
		      <div class="col-8">
		        <input class="form-control" name="contact[weixin]" value="{{Config('common.contact.weixin')}}">
		      </div>
		    </div>
		    <div class="row mb-3">
		      <label class="col-2 col-form-label text-end">邮箱：</label>
		      <div class="col-8">
		        <input class="form-control" name="contact[email]" value="{{Config('common.contact.email')}}">
		      </div>
		    </div>
		    <div class="row mb-3">
		      <label class="col-2 col-form-label text-end">Q Q：</label>
		      <div class="col-8">
		        <input class="form-control" name="contact[qq]" value="{{Config('common.contact.qq')}}">
		      </div>
		    </div>
		    <div class="row mb-3">
		      <label class="col-2 col-form-label text-end">电话：</label>
		      <div class="col-8">
		        <input class="form-control" name="contact[telphone]" value="{{Config('common.contact.telphone')}}">
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
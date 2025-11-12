@extends('admin.shared._layout')
@section('content')
<div class="main">
	<div class="bg-white p-4">
	  <div class="breadcrumb-stitle">应用配置</div>
	</div>
	<div class="pagetopnav mx-4 mt-4">
		<div class="items">
			<a class="item {{ in_array(Request()->route()->getActionMethod(), ['sms']) ? 'on' : '' }}" href="/admin/set/sms">阿里云短信服务</a>
			<a class="item {{ in_array(Request()->route()->getActionMethod(), ['sms_template']) ? 'on' : '' }}" href="/admin/set/sms_template">短信模板</a>
		</div>
	</div>
	<div class="mx-4 mt-4">
		<div class="pagebox">
			<table class="table text-center mt-3">
		    <thead>
		      <tr>
		        <th>模板CODE</th>
		        <th>模板名称</th>
		        <th>模板内容</th>
		        <th>模板类型</th>
		        <th>操作</th>
		      </tr>
		    </thead>
		    <tbody>
		      @foreach(Config('common.sms.template') as $key => $value)
		      <tr>
		        <td>{{$value['tpl_code']}}</td>
		        <td>{{$value['name']}}</td>
		        <td>{{$value['content']}}</td>
		        <td>{{$value['type']}}</td>
		        <td>
		          <button class="btn btn-primary btn-sm" onclick="layerOpen('/admin/set/set_sms_template_code?id={{$key}}', '设置code', '620px', '460px');">设置code</button>
		        </td>
		      </tr>
		      @endforeach
		    </tbody>
		  </table>
		</div>
	</div>
</div>
@endsection
@section('pagejs')
@include('admin.shared._jquery_validation')
<script type="text/javascript" src="/static/plugins/luck.file.upload.js"></script>
<script type="text/javascript">  </script>
@endsection
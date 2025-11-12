@extends('admin.shared._layout')
@section('content')
<div class="main">
  <div class="bg-white p-4">
    <ol class="breadcrumb">
      <li class="breadcrumb-item">文章中心</li>
      <li class="breadcrumb-item active">编辑分类</li>
    </ol>
    <div class="breadcrumb-stitle">
      <a href="javascript:history.go(-1);"><i class="iconfont goback"></i></a>
      <span class="txt">编辑分类</span>
    </div>
  </div>

  <ul class="form-message m-4 p-4"></ul>
  <form class="mt-4" id="form" action="/admin/article/category_update" method="post" autocomplete="off">
    @csrf
    <input type="hidden" name="id" value="{{$category->id}}">
    <div class="m-4 pagebox">
      <div class="row mb-3">
        <label class="col-2 col-form-label text-end">分类名字：</label>
        <div class="col-8">
          <input class="form-control" name="name" value="{{$category->name}}" placeholder="">
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-2 col-form-label text-end">上级分类：</label>
        <div class="col-auto">
          <select class="form-select" name="parent_id">
            <option value="0">顶级分类</option>
            @foreach($categorys as $value)
            <option value="{{$value->id}}" @if($category->parent_id == $value->id) selected @endif>@php for($i = 1; $i < $value->level; $i++) {echo '&nbsp;&nbsp;';} @endphp {{$value->name}}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-2 col-form-label text-end">封面图：</label>
        <div class="col-auto">
          @if($category && !empty($category->cover))
          <div class="luckFU uploaded" style="width: 90px; height: 90px;" data-name="cover" data-url="/admin/upload">
            <i class="luckFU_remove iconfont" href="javascript:void(0);"></i>
            <img src="{{$category->cover}}">
            <input type="hidden" name="cover" value="{{$category->cover}}">
          </div>
          @else
          <div class="luckFU" style="width: 90px; height: 90px;" data-name="cover" data-url="/admin/upload">
            <input type="hidden" name="cover" value="">
          </div>
          @endif
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-2 col-form-label text-end">简介：</label>
        <div class="col-8">
          <textarea class="form-control" name="description" rows="3">{{$category->description}}</textarea>
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-2 col-form-label text-end">状态：</label>
        <div class="col-auto">
          <select class="form-select" name="status">
            <option value="1" @if($category->status == 1) selected @endif>开启</option>
            <option value="0" @if($category->status == 0) selected @endif>关闭</option>
          </select>
        </div>
      </div>
    </div>

    <div class="m-4 pagebox">
      <div><b>SEO优化</b></div>
      <div class="mt-4">
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end"><i class="iconfont luck-icon-question" data-bs-toggle="tooltip" title="" data-bs-original-title="为空使用ID"></i> 分类链接：</label>
          <div class="col-8">
            <input class="form-control" name="url" value="{{$category->url}}" placeholder="为空使用ID">
          </div>
        </div>
      </div>
      <div class="mt-4">
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">seo title：</label>
          <div class="col-8">
            <input class="form-control" name="seo_title" value="{{$category->seo_title}}" placeholder="">
          </div>
        </div>
      </div>
      <div class="mt-4">
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">seo keywords：</label>
          <div class="col-8">
            <input class="form-control" name="seo_keywords" value="{{$category->seo_keywords}}" placeholder="">
          </div>
        </div>
      </div>
      <div class="mt-4">
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">seo description：</label>
          <div class="col-8">
            <input class="form-control" name="seo_description" value="{{$category->seo_description}}" placeholder="">
          </div>
        </div>
      </div>
    </div>

    <div class="m-4 pagebox">
      <div><b>分类设置</b></div>
      <div class="mt-4">
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end"><i class="iconfont luck-icon-question" data-bs-toggle="tooltip" title="" data-bs-original-title="为空使用默认模版"></i> 模版列表：</label>
          <div class="col-8">
            <input class="form-control" name="tpl_list" value="{{$category->tpl_list}}">
          </div>
        </div>
      </div>
      <div class="mt-4">
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end"><i class="iconfont luck-icon-question" data-bs-toggle="tooltip" title="" data-bs-original-title="为空使用默认模版"></i> 模版详情：</label>
          <div class="col-8">
            <input class="form-control" name="tpl_show" value="{{$category->tpl_show}}">
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
<script type="text/javascript" src="/static/admin/plugins/luck.file.upload.js"></script>
<script type="text/javascript">
$("#form").validate({
  rules: {
    name: {required: true},
  },
  messages: {
    name: '名字不能为空',
  },
  submitHandler: function() {
    layer.confirm('确认提交？', function() {
      layer.closeAll();
      var load = layer.load();
      $("#form").ajaxSubmit(function(res) {
        layer.close(load);
        if (res.code == 200) {
          layer.msg('操作成功', { time: 1500 }, function () { window.location.href = '/admin/article/category_list'; });
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
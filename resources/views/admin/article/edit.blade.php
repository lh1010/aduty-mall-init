@extends('admin.shared._layout')
@section('content')
<div class="main">
  <div class="bg-white p-4">
    <ol class="breadcrumb">
      <li class="breadcrumb-item">文章中心</li>
      <li class="breadcrumb-item active">编辑文章</li>
    </ol>
    <div class="breadcrumb-stitle">
      <a href="javascript:history.go(-1);"><i class="iconfont goback"></i></a>
      <span class="txt">编辑文章</span>
    </div>
  </div>
  
  <ul class="form-message m-4 p-4"></ul>
  <form class="mt-4" id="form" action="/admin/article/update" method="post" autocomplete="off">
    @csrf
    <input type="hidden" name="id" value="{{$article->id}}">
    <div class="m-4 pagebox">
      <div class="mt-4">
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end"><span class="text-danger">*</span> 标题：</label>
          <div class="col-8">
            <input class="form-control" name="title" value="{{$article->title}}" placeholder="">
          </div>
        </div>
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">短标题：</label>
          <div class="col-8">
            <input class="form-control" name="shorttitle" value="{{$article->shorttitle}}" placeholder="">
          </div>
        </div>
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">分类：</label>
          <div class="col-auto">
            <select class="form-select" name="category_id">
              <option value="">请选择</option>
              @foreach($categorys as $value)
              <option value="{{$value->id}}" @if($value->id == $article->category_id) selected @endif>@php for($i = 1; $i < $value->level; $i++) {echo '&nbsp;&nbsp;';} @endphp {{$value->name}}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">封面图：</label>
          <div class="col-auto">
            @if($article && !empty($article->cover))
            <div class="luckFU uploaded" style="width: 90px; height: 90px;" data-name="cover" data-url="/admin/upload">
              <i class="luckFU_remove iconfont" href="javascript:void(0);"></i>
              <img src="{{$article->cover}}">
              <input type="hidden" name="cover" value="{{$article->cover}}">
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
            <textarea class="form-control" name="description">{{$article->description}}</textarea>
          </div>
        </div> 
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">内容：</label>
          <div class="col-8">
            <div id="editormd" class="editormd"><textarea name="content">{{$article->content_markdown}}</textarea></div>
          </div>
        </div>
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end"><i class="iconfont iconfont-question" data-bs-toggle="tooltip" title="" data-bs-original-title="数字类型；排序值越大，排序越高"></i> 排序：</label>
          <div class="col-2">
            <input class="form-control" name="sort" value="{{$article->sort}}" placeholder="">
          </div>
        </div>
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">点击数：</label>
          <div class="col-2">
            <input class="form-control" name="click" value="{{$article->click}}" placeholder="">
          </div>
        </div>
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">创建时间：</label>
          <div class="col-2">
            <input class="form-control" name="created_at" value="{{$article->created_at}}" onclick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:ss'})" placeholder="">
          </div>
        </div>  
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">状态：</label>
          <div class="col-auto">
            <select class="form-select" name="status">
              <option value="1" @if($article->status == 1) selected @endif>开启</option>
              <option value="0" @if($article->status == 0) selected @endif>关闭</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <div class="m-4 pagebox">
      <div><b>SEO优化</b></div>
      <div class="mt-4">
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end"><i class="iconfont luck-icon-question" data-bs-toggle="tooltip" title="" data-bs-original-title="为空使用ID"></i> 链接：</label>
          <div class="col-8">
            <input class="form-control" name="url" value="{{$article->url}}" placeholder="为空使用ID">
          </div>
        </div>
      </div>
      <div class="mt-4">
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">seo title：</label>
          <div class="col-8">
            <input class="form-control" name="seo_title" value="{{$article->seo_title}}" placeholder="">
          </div>
        </div>
      </div>
      <div class="mt-4">
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">seo keywords：</label>
          <div class="col-8">
            <input class="form-control" name="seo_keywords" value="{{$article->seo_keywords}}" placeholder="">
          </div>
        </div>
      </div>
      <div class="mt-4">
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">seo description：</label>
          <div class="col-8">
            <input class="form-control" name="seo_description" value="{{$article->seo_description}}" placeholder="">
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
@include('admin.shared._editormd')
@include('admin.shared._jquery_validation')
<script type="text/javascript" src="/static/admin/plugins/luck.file.upload.js"></script>
<script type="text/javascript" src="/static/admin/plugins/ejecttime/WdatePicker.js"></script>
<script type="text/javascript">
$("#form").validate({
  rules: {
    title: {required: true},
  },
  messages: {
    title: '标题不能为空',
  },
  submitHandler: function() {
    layer.confirm('确认提交？', function() {
      layer.closeAll();
      var load = layer.load();
      $("#form").ajaxSubmit(function(res) {
        layer.close(load);
        if (res.code == 200) {
          layer.msg('操作成功', { time: 1500 }, function () { window.location.href = '/admin/article/list'; });
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
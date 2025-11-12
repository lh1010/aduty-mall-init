@extends('admin.shared._layout')
@section('content')
<div class="main">
  <div class="bg-white p-4">
    <ol class="breadcrumb">
      <li class="breadcrumb-item">文章列表</li>
      <li class="breadcrumb-item active">新增</li>
    </ol>
    <div class="breadcrumb-stitle">
      <a href="javascript:history.go(-1);"><i class="iconfont goback"></i></a>
      <span class="txt">新增</span>
    </div>
  </div>
  <ul class="form-message m-4 p-4"></ul>
  <form class="mt-4" id="form" action="/admin/tbname/store" method="post" autocomplete="off">
    @csrf
    <div class="m-4 pagebox">
      <div class="mt-4">
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end"><span class="text-danger">*</span> 标题：</label>
          <div class="col-8">
            <input class="form-control" name="title" placeholder="">
          </div>
        </div>
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">分类：</label>
          <div class="col-auto">
            <select class="form-select" name="category_id">
              <option value="">请选择</option>
              <option value="分类">分类</option>
              <option value="分类">分类</option>
            </select>
          </div>
        </div>
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">封面图：</label>
          <div class="col-auto">
            <div class="luckFU" style="width: 90px; height: 90px;" data-name="avatar" data-url="/admin/upload">
              <input type="hidden" name="avatar" value="">
            </div>
          </div>
        </div>
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">活动费用：</label>
          <div class="col-8">
            <div class="form-check form-check-inline pt-2">
              <input class="form-check-input" type="radio" name="cost_type" id="付费" value="付费" checked>
              <label class="form-check-label" for="付费">付费</label>
            </div>
            <div class="form-check form-check-inline pt-2">
              <input class="form-check-input" type="radio" name="cost_type" id="免费" value="免费">
              <label class="form-check-label" for="免费">免费</label>
            </div>
          </div>
        </div>
        <div class="row mb-3" id="price">
          <label class="col-2 col-form-label text-end">活动费用：</label>
          <div class="col-2">
            <div class="input-group">
              <span class="input-group-text">￥</span>
              <input type="text" class="form-control" name="price" value="" />
              <span class="input-group-text">元</span>
            </div>
          </div>
        </div>
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">简介：</label>
          <div class="col-8">
            <textarea class="form-control" name="description" rows="3"></textarea>
          </div>
        </div>
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">内容：</label>
          <div class="col-8">
            <div id="editormd" class="editormd"><textarea name="content"></textarea></div>
          </div>
        </div>
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">内容：</label>
          <div class="col-8">
            <script id="myEditor" name="content" type="text/plain"></script>
          </div>
        </div>
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end"><i class="iconfont luck-icon-question" data-bs-toggle="tooltip" title="" data-bs-original-title="数字类型；排序值越大，排序越高"></i> 排序：</label>
          <div class="col-2">
            <input class="form-control" name="sort" value="0" placeholder="">
          </div>
        </div>
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">创建时间：</label>
          <div class="col-2">
            <input class="form-control" name="created_at" value="" onclick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:ss'})" placeholder="">
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
    </div>

    <div class="m-4 pagebox">
      <div><b>SEO优化</b></div>
      <div class="mt-4">
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end"><i class="iconfont luck-icon-question" data-bs-toggle="tooltip" title="" data-bs-original-title="为空使用ID"></i> 链接：</label>
          <div class="col-8">
            <input class="form-control" name="url" placeholder="为空使用ID">
          </div>
        </div>
      </div>
      <div class="mt-4">
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">seo title：</label>
          <div class="col-8">
            <input class="form-control" name="seo_title" placeholder="">
          </div>
        </div>
      </div>
      <div class="mt-4">
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">seo keywords：</label>
          <div class="col-8">
            <input class="form-control" name="seo_keywords" placeholder="">
          </div>
        </div>
      </div>
      <div class="mt-4">
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">seo description：</label>
          <div class="col-8">
            <input class="form-control" name="seo_description" placeholder="">
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
@include('admin.shared._UEditor')
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
          layer.msg('操作成功', { time: 1500 }, function () { window.location.href = '/admin/tbname/list'; });
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

@extends('admin.shared._layout')
@section('pagecss')
<style type="text/css">
.adver_values .item .item_box {
  border: 1px solid #eee;
  padding: 1rem;
  position: relative;
  border-radius: 5px;
}
.adver_values .item .item_box:hover {
  border: 1px solid #ddd;
}
.adver_values .item .item_box:hover .close:after {
  display: block;
}
.adver_values .item .close:after {
  position: absolute;
  top: -8px;
  right: -8px;
  content: '\e607';
  font-size: 1.125rem;
  color: #f4645f;
  cursor: pointer;
  display: none;
}
.adver_values .item .item_box .luckFU {
  width: 100px;
  height: 100px;
}

</style>
@endsection
@section('content')
<div class="main">
  <div class="bg-white p-4">
    <ol class="breadcrumb">
      <li class="breadcrumb-item">平台广告</li>
      <li class="breadcrumb-item active">编辑广告</li>
    </ol>
    <div class="breadcrumb-stitle">
      <a href="javascript:history.go(-1);"><i class="iconfont goback"></i></a>
      <span class="txt">编辑广告</span>
    </div>
  </div>

  <ul class="form-message m-4 p-4"></ul>
  <form class="mt-4" id="form" action="/admin/adver/update" method="post" autocomplete="off">
    @csrf
    <input type="hidden" name="id" value="{{$adver->id}}">
    <div class="m-4 pagebox">
      <div><b>基础信息</b></div>
      <div class="mt-4">
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end"><span class="text-danger">*</span> 名字：</label>
          <div class="col-8">
            <input class="form-control" name="name" value="{{$adver->name}}" placeholder="">
          </div>
        </div>
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end"><i class="iconfont luck-icon-question" data-bs-toggle="tooltip" title="" data-bs-original-title="客户端使用，调取参数的唯一标识" aria-label="客户端使用，调取参数的唯一标识"></i> <span class="text-danger">*</span> Code：</label>
          <div class="col-8">
            <input class="form-control" name="code" value="{{$adver->code}}" placeholder="">
          </div>
        </div>
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end"><span class="text-danger">*</span> 客户端：</label>
          <div class="col-auto">
            <select class="form-select" name="client">
              @foreach(Config('common.adver.client') as $value)
              <option value="{{$value}}" @if($adver->client == $value) selected @endif>{{$value}}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">备注：</label>
          <div class="col-8">
            <textarea class="form-control" name="remark">{{$adver->remark}}</textarea>
          </div>
        </div>
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">状态：</label>
          <div class="col-auto">
            <select class="form-select" name="status">
              <option value="1" @if($adver->status == 1) selected @endif>开启</option>
              <option value="0" @if($adver->status == '0') selected @endif>关闭</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <div class="m-4 pagebox">
      <div>
        <b>广告内容</b>
        <button type="button" class="btn btn-success btn-sm" onclick="addAdverValue();" style="margin-left: 10px;">新增广告内容</button>
      </div>
      <div class="mt-4">
        <div class="adver_values row">
          @foreach($adver->values as $value)
          <div class="col-4 item mb-3">
            <input type="hidden" name="value_ids[]" value="{{$value->id}}">
            <div class="item_box">
              <i class="iconfont close" onclick="delAdverValue(this);"></i>
              <div class="mb-3">
                <label class="form-label"><span class="text-danger">*</span> 广告标题</label>
                <input type="text" class="form-control" name="titles[]" value="{{$value->title}}">
              </div>
              <div class="mb-3">
                <label class="form-label">广告链接</label>
                <input type="text" class="form-control" name="urls[]" value="{{$value->url}}">
              </div>
              <div class="mb-3">
                <label class="form-label">打开方式</label>
                <select class="form-select" name="open_modes[]">
                  <option value="1" @if($value->open_mode == 1) selected @endif>新窗口打开/navigateTo模式</option>
                  <option value="2" @if($value->open_mode == 2) selected @endif>当前页打开/switchTab模式</option>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">广告排序</label>
                <input type="text" class="form-control" name="sorts[]" value="{{$value->sort}}">
              </div>
              <div class="">
                <label class="form-label">广告图片</label>
                @if(!empty($value->image))
                <div class="luckFU uploaded" data-name="images[]" data-url="/admin/upload">
                  <i class="luckFU_remove iconfont" href="javascript:void(0);" onclick="luckFU_delImage()"></i>
                  <img src="{{$value->image}}">
                  <input type="hidden" name="images[]" value="{{$value->image}}">
                </div>
                @else
                <div class="luckFU" data-url="/admin/upload" data-name="images[]"></div>
                @endif
              </div>
            </div>
          </div>
          @endforeach
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
<script type="text/javascript" src="/static/admin/plugins/luck.file.upload.js"></script>
@include('admin.shared._jquery_validation')
<script type="text/javascript">
$("#form").validate({
  rules: {
    name: {required: true},
    code: {required: true},
    client: {required: true},
  },
  messages: {
    name: '名字不能为空',
    code: 'code不能为空',
    client: '客户端不能为空',
  },
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
          layer.msg('提交成功', { time: 1500 }, function () { window.location.reload(); });
        } else if (res.code == 400) {
          layer.msg(res.message); return false;
        } else {
          layer.msg('操作失败'); return false;
        }
      });
    });
  }
});

function addAdverValue() {
  var str = '';
  str += '<div class="col-4 item mb-3">';
  str += '<div class="item_box">';
  str += '<i class="iconfont close" onclick="delAdverValue(this);"></i>';
  str += '<div class="mb-3">';
  str += '<label class="form-label"><span class="text-danger">*</span> 广告标题</label>';
  str += '<input type="text" class="form-control" name="titles[]">';
  str += '</div>';
  str += '<div class="mb-3">';
  str += '<label class="form-label">广告链接</label>';
  str += '<input type="text" class="form-control" name="urls[]">';
  str += '</div>';
  str += '<div class="mb-3">';
  str += '<label class="form-label">打开方式</label>';
  str += '<select class="form-select" name="open_modes[]">';
  str += '<option value="1">新窗口打开/navigateTo模式</option>';
  str += '<option value="2">当前页打开/switchTab模式</option>';
  str += '</select>';
  str += '</div>';
  str += '<div class="mb-3">';
  str += '<label class="form-label">广告排序</label>';
  str += '<input type="text" class="form-control" name="sorts[]">';
  str += '</div>';
  str += '<div class="">';
  str += '<label class="form-label">广告图片</label>';
  str += '<div class="luckFU" data-url="/admin/upload" data-name="images[]"></div>';
  str += '</div>';
  str += '</div>';
  str += '</div>';
  $('.adver_values').append(str);
}

function delAdverValue(thisNode) {
  $(thisNode).parents('.item').remove();
}
</script>
@endsection

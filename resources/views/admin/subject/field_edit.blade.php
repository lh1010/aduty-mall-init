@extends('admin.shared._layout')
@section('content')
<div class="main">
  <div class="bg-white p-4">
    <div class="breadcrumb-stitle">
      <a href="javascript:history.go(-1);"><i class="iconfont goback"></i></a>
      <span class="txt">编辑字段</span>
    </div>
    <div class="mt-3" style="font-size: 12px;">{{$subject->name}}</div>
  </div>

  <ul class="form-message m-4 p-4"></ul>
  <form class="mt-4" id="form" action="/admin/subject/field_update" method="post" autocomplete="off">
    @csrf
    <input type="hidden" name="id" value="{{$field->id}}">
    <div class="m-4 pagebox">
      <div class="row mb-3">
        <label class="col-2 col-form-label text-end"><span class="text-danger">*</span> 类型：</label>
        <div class="col-auto">
          <select class="form-select" name="type">
            @foreach(Config('common.subject.field_type') as $value)
            <option value="{{$value}}" @if($value == Request()->type) selected @endif>{{$value}}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-2 col-form-label text-end"><i class="iconfont luck-icon-question" data-bs-toggle="tooltip" title="" data-bs-original-title="建议使用英文"></i> <span class="text-danger">*</span> 键值：</label>
        <div class="col-auto">
          <input class="form-control" name="key" value="{{$field->key}}" placeholder="">
        </div>
      </div>
      @if(Request()->type == '文本')
      <div class="valuebox">
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">内容：</label>
          <div class="col-8">
            <textarea class="form-control" name="value">{{$field->value}}</textarea>
          </div>
        </div>
      </div>
      @endif
      @if(Request()->type == '富文本')
      <div class="valuebox">
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">内容：</label>
          <div class="col-8">
            <div id="editormd" class="editormd"><textarea name="value">{{$field->value_markdown}}</textarea></div>
          </div>
        </div>
      </div>
      @endif
      <div class="row mb-3">
        <label class="col-2 col-form-label text-end">说明注释：</label>
        <div class="col-8">
          <textarea class="form-control" name="description">{{$field->description}}</textarea>
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
<script type="text/javascript">
$("#form").validate({
  rules: {
    key: {required: true},
    type: {required: true},
  },
  messages: {
    key: '键值不能为空',
    type: '类型不能为空',
  },
  submitHandler: function() {
    layer.confirm('确认提交？', function() {
      layer.closeAll();
      var load = layer.load();
      $("#form").ajaxSubmit(function(res) {
        layer.close(load);
        if (res.code == 200) {
          layer.msg('操作成功', { time: 1500 }, function () { window.location.href = '/admin/subject/field_list?subject_id={{$subject->id}}'; });
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

$('select[name="type"]').change(function() {
  var url = '/admin/subject/field_edit?id={{$field->id}}&type=' + $(this).val();
  window.location.href = url;
})
</script>
@endsection
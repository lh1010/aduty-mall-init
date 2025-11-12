@extends('admin.shared._layout')
@section('pagecss')
<style>
#o_option .bd {
  border: 1px solid #eee;
  padding: 1rem;
}
#o_option .bd .stitle {
  border-bottom: 1px solid #eee;
  padding-bottom: 1rem;
  margin-bottom: 1rem;
}
#o_option .bd .items {
  margin-bottom: -5px;
}
#o_option .bd .item {
  background-color: #5cb85c;
  display: inline-block;
  padding: 6px 8px !important;
  margin-bottom: 5px;
  margin-right: 3px;
  cursor: pointer;
  position: relative;
  font-size: 12px;
  color: #fff;
  border-radius: 3px;
}
#o_option .bd .item:after {
  position: absolute;
  top: -8px;
  right: -3px;
  content: 'x';
  color: #ff0000;
  font-size: 13px;
  display: none;
}
#o_option .bd .item:hover:after {
  display: block;
}
</style>
@endsection
@section('content')
<div class="main">
  <div class="bg-white p-4">
    <div class="breadcrumb-stitle">
      <a href="javascript:history.go(-1);"><i class="iconfont goback"></i></a>
      <span class="txt">编辑字段</span>
    </div>
    <div class="mt-3" style="font-size: 12px;">{{$group->name}}</div>
  </div>

  <ul class="form-message m-4 p-4"></ul>
  <form class="mt-4" id="form" action="/admin/cusfield/update" method="post" autocomplete="off">
    @csrf
    <input type="hidden" name="id" value="{{$cusfield->id}}" >
    <div class="m-4 pagebox">
      <div class="row mb-3">
        <label class="col-2 col-form-label text-end"><span class="text-danger">*</span> 字段名：</label>
        <div class="col-8">
          <input class="form-control" name="name" value="{{$cusfield->name}}" placeholder="">
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-2 col-form-label text-end"><span class="text-danger">*</span> 类型：</label>
        <div class="col-auto">
          <select class="form-select" name="type">
            <option value="">请选择</option>
            @foreach(Config('common.cusfield.type') as $value)
            <option value="{{$value}}" @if($cusfield->type == $value) selected @endif>{{$value}}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="row mb-3" id="o_option" @if(!in_array($cusfield->type, ['多选项', '单选项'])) style="display: none;" @endif>
        <label class="col-2 col-form-label text-end">选项值：</label>
        <div class="col-8">
          <div class="input-group" style="width: 200px">
            <input type="text" class="form-control" id="o_option_input" placeholder="">
            <button class="btn btn-outline-secondary" type="button" onclick="addOption();">添加</button>
          </div>
          <div class="bd mt-3">
            <div class="stitle">已有可选项：</div>
            <div class="items">
              @foreach($cusfield->options as $value)
              <div class="item">
                {{$value}}
                <input type="hidden" name="options[]" value="{{$value}}">
              </div>
              @endforeach
            </div>
          </div>
        </div>
      </div>

      <div class="row mb-3">
        <label class="col-2 col-form-label text-end">排序：</label>
        <div class="col-auto">
          <input class="form-control" name="sort" value="{{$cusfield->sort}}" placeholder="">
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-2 col-form-label text-end">说明注释：</label>
        <div class="col-8">
          <textarea class="form-control" name="description" rows="3">{{$cusfield->description}}</textarea>
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-2 col-form-label text-end">必需：</label>
        <div class="col-auto">
          <select class="form-select" name="required">
            <option value="否" @if($cusfield->required == '否') selected @endif>否</option>
            <option value="是" @if($cusfield->required == '是') selected @endif>是</option>
          </select>
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-2 col-form-label text-end">状态：</label>
        <div class="col-auto">
          <select class="form-select" name="status">
            <option value="1" @if($cusfield->status == '1') selected @endif>开启</option>
            <option value="0" @if($cusfield->status == '0') selected @endif>关闭</option>
          </select>
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
    type: {required: true},
  },
  messages: {
    name: '字段名不能为空',
    type: '类型不能为空',
  },
  submitHandler: function() {
    layer.confirm('确认提交？', function() {
      layer.closeAll();
      var load = layer.load();
      $("#form").ajaxSubmit(function(res) {
        layer.close(load);
        if (res.code == 200) {
          layer.msg('操作成功', { time: 1500 }, function () { window.location.href = '/admin/cusfield/list?group_id={{$group->id}}'; });
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
  if (jQuery.inArray($(this).val(), ['多选项', '单选项']) != -1) {
    $('#o_option').show();
  } else {
    $('#o_option').hide();
  }
})

// 多选项 select
$('#o_option .items').on('click', '.item', function() {
  var thisNode = $(this);
  layer.confirm('确认移除？', function() {
    layer.closeAll();
    thisNode.remove();
  })
})
function addOption() {
  var option = $('#o_option_input').val();
  if (option == '') return false;
  var array = [];
  $('input[name="options[]"]').each(function() {
    array.push($(this).val());
  })
  if (array.indexOf(option) >= 0) {
    layer.msg('该选项已存在');
    return false;
  }
  var str = '';
  str += '<div class="item">';
  str += option;
  str += '<input type="hidden" name="options[]" value="' + option + '">';
  str += '</div>';               
  $('#o_option .items').append(str);       
}
</script>
@endsection
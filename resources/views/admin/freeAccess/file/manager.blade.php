<!DOCTYPE HTML>
<html>
<head>
<title>文件管理</title>
@include('admin.shared._head')
<style>
html, body {
  max-width: 827px;
  min-width: 0;
  margin: 0 auto;
  position: relative;
  background-color: #ffffff;
}
.file_sapce {
  padding: 18px;
}
.file_sapce_manager {
  padding: 0;
}
.file_sapce_top {
  border-bottom: 1px solid #eeeeee;
  padding-bottom: 12px;
  margin-bottom: 12px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.file_sapce_top .leftbox .btn {
  background-color: #e7e7e7;
}
.file_sapce_top i {
  color: #999;
}
.file_sapce_top #btn_upload {
  color: #fff;
  background-color: #0d6efd;
  border-color: #0d6efd;
}
.file_sapce_top #btn_upload i {
  color: #fff;
}
.file_sapce_top #btn_delete {
  color: #fff;
  background-color: #d9534f;
  border-color: #d43f3a;
}
.file_sapce_top #btn_delete i {
  color: #fff;
}
.file_sapce_box .items {
  display: flex;
  flex-wrap: wrap;
  margin-right: -15px;
  margin-bottom: -15px;
}
.file_sapce_box .item {
  margin-right: 15px;
  margin-bottom: 15px;
  cursor: pointer;
}
.file_sapce_box .item img {
  width: 100px;
  height: 100px;
  border: 1px solid #eee;
  padding: 5px;
  object-fit: contain;
}
.file_sapce_box .item img:hover {
  border: 1px solid rgba(48,137,220,0.5);
}
.file_sapce_box .item .filename {
  display: block;
  color: #666;
  font-size: 12px;
  width: 90%;
  margin: 5px auto 0;
  text-align: center;
  white-space: nowrap;
  overflow: hidden;
  text-overflow:ellipsis;
  cursor: pointer;
}
.file_sapce_box .item .filename .form-check {
  margin-bottom: 0;
}
.file_sapce_box .item .filename .form-check-label {
  cursor: pointer;
  display: flex;
  align-items: center;
}
.file_sapce_box .item .filename .form-check-label .form-check-input {
  margin-right: 3px;
}
.file_sapce_box .item .filename .form-check-label .txt {
  flex: 1;
  max-width: 80px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow:ellipsis;
}
.file_sapce_top .rightbox {
  display: flex;
  align-items: center;
}
</style>
</head>
<body>
<input type="hidden" id="use_ident" value="{{Request()->use_ident}}">
<div class="file_sapce">
  <div class="file_sapce_top">
    <div class="leftbox">
      @if(!empty($prev))<a href="{{$prev}}" class="btn btn-default btn-sm"><i class="iconfont">&#xe623;</i></a>@endif
      <a class="btn btn-default btn-sm" href="javascript:window.location.reload();"><i class="iconfont">&#xe68c;</i></a>
      <button class="btn btn-primary btn-sm" id="btn_upload"><i class="iconfont">&#xe665;</i></button>
      <button class="btn btn-default btn-sm" id="btn_folder"><i class="iconfont">&#xe604;</i></button>
      <button class="btn btn-danger btn-sm" id="btn_delete"><i class="iconfont">&#xe676;</i></button>
    </div>
    <div class="rightbox">
      <select class="form-select form-select-sm me-2" id="order">
        <option value="">默认排序</option>
        <option value="按名称" @if(Request()->order == '按名称') selected @endif>按名称</option>
        <option value="按时间" @if(Request()->order == '按时间') selected @endif>按时间</option>
      </select>
      <div class="input-group input-group-sm">
        <input type="text" class="form-control" placeholder="搜索内容" id="k" value="{{Request()->k}}" />
        <button class="btn btn-primary" type="button" onclick="doSearch()">搜索</button>
      </div>
    </div>
  </div>
  <div class="file_sapce_box">
    @if($res->total() > 0)
    <div class="items">
      @foreach($res as $key => $value)
      <div class="item" title="{{$value->name}}">
        @if($value->type == 'folder')
        <a href="{{$value->url}}"><img src="/static/admin/images/folder.png"></a>
        @endif
        @if($value->type == 'file')
        <img src="{{$value->url}}" class="file">
        @endif
        <div class="filename">
          <div class="form-check">
            <label class="form-check-label" for="f_{{$value->path}}">
              <input
                type="checkbox"
                class="form-check-input"
                name="filepath"
                data-type="{{$value->type}}"
                data-name="{{$value->name}}"
                data-path="{{$value->path}}"
                id="f_{{$value->path}}"
                value="{{$value->path}}"
              >
              <span class="txt">{{$value->name}}</span>
            </label>
          </div>
        </div>
      </div>
      @endforeach
    </div> 
    <div class="page my-3 pagination-sm">{{ $res->appends(Request()->all())->render() }}</div>
    @else
    <div class="noresult">
      <img src="/static/admin/images/noresult.png">
      <p>暂无内容</p>
    </div>
    @endif
  </div>
</div>
<form action="/admin/freeAccess/file/uploads" method="post" id="fm_upload" enctype="multipart/form-data" style="display:none">
  @csrf
  <input type="hidden" name="folder" value="{{Request()->folder}}">
  <input type="file" name="files[]" id="fm_upload_file" multiple />
</form>
@include('admin.shared._foot')
@include('admin.shared._jquery_validation')
<script type="text/javascript">
$(document).ready(function() {

  $('#btn_upload').click(function() {
    $('#fm_upload_file').click();
  })

  $('#btn_folder').popover({
    html: true,
    placement: 'bottom',
    trigger: 'click',
    sanitize: false,
    title: '新建文件夹',
    content: function() {
      return `
        <div class="input-group">
          <input type="text" name="new_folder" placeholder="文件夹" class="form-control">
          <button type="button" class="btn btn-outline-secondary" onclick="createFolder();"><i class="iconfont">&#xe600;</i></button>
        </div>
      `;
    }
  });

})

$('#fm_upload_file').change(function() {
  var layer_load = layer.load();
  $('#fm_upload').ajaxSubmit(function(res) {
    layer.close(layer_load);
    if (res.code == 200) {
      window.location.reload();
      return false;
    } else {
      layer.msg('上传失败');
      return false;
    }
  })
})

function createFolder() {
  var layer_load = layer.load();
  $.ajax({
    url: "/admin/freeAccess/file/createFolder",
    type: 'post',
    data: {
      folder: '{{Request()->folder}}',
      new_folder: $('input[name="new_folder"]').val(),
      _token: $('input[name="_token"]').val()
    },
    success: function(res) {
      layer.close(layer_load);
      if (res.code == 200) {
        window.location.reload();
        return false;
      } else {
        layer.msg(res.message);
        return false;
      }
    }
  })
}

$('#btn_delete').click(function() {
  var filepaths = [];
  $("input[name='filepath']:checked").each(function(i){
    var file = {
      name: $(this).data('name'),
      path: $(this).data('path'),
      type: $(this).data('type')
    };
    filepaths.push(file);
  });

  if (filepaths.length == 0) {
    layer.msg('请选择要删除文件');
    return false;
  }

  layer.confirm('确定删除？', function() {
    layer.closeAll();
    var load = layer.load();
    $.ajax({
      url: "/admin/freeAccess/file/delete",
      type: 'post',
      data: {
        folder: '{{Request()->folder}}',
        filepaths: filepaths,
        _token: $('input[name="_token"]').val()
      },
      success: function(res) {
        layer.close(load);
        if (res.code == 200) {
          window.location.reload();
        } else {
          layer.msg(res.message);
        }
      }
    })
  });
})

$('.file').click(function() {
  console.log($('#use_ident').val());
  if ($('#use_ident').val() == '' && parent.$('.fmr_current_ident').length < 1) return false;
  // 兼容 summernote 编辑器
  if ($('#use_ident').val() == 'summernote') {
    parent.$('.summernote_checked').summernote('insertImage', $(this).attr('src'));
    parent.$('.summernote_checked').removeClass('summernote_checked');
  } else {
    var input_name = parent.$('.fmr_current_ident').attr('data-name') != undefined ? parent.$('.fmr_current_ident').attr('data-name') : 'file';
    var str = '';
    str += '<input name="'+input_name+'" type="hidden" value="'+$(this).attr('src')+'" />';
    str += '<img src="'+$(this).attr('src')+'" />';
    str += '<i class="iconfont fmr_remove"></i>';
    parent.$('.fmr_current_ident').addClass('uploaded');
    parent.$('.fmr_current_ident').html(str);
  }
  var index = parent.layer.getFrameIndex(window.name);
  parent.layer.close(index);
})

$("#order").change(function() {
  setUrl('order', $(this).val());
})

function doSearch() {
  setUrl('k', $("#k").val());
}

function setUrl(key, value) {
  var url = removeUrlParam(['page', key]);
  if (value != '') {
    var reg = /\?/;
    reg.test(url) ? url += '&' + key + '=' + value : url += '?' + key + '=' + value;
  }
  window.location.href = url;
}

function removeUrlParam(params = []) {
  var url = window.location.href.split('?')[0] + '?';

  var sPageURL = decodeURIComponent(window.location.search.substring(1));
  var sURLVariables = sPageURL != '' ? sPageURL.split('&') : [];
  var sParameterName;
  var i;

  for (i = 0; i < sURLVariables.length; i++) {
    sParameterName = sURLVariables[i].split('=');
    if (!in_array(sParameterName[0], params)) {
      url = url + sParameterName[0] + '=' + sParameterName[1] + '&'
    }
  }

  return url.substring(0, url.length - 1);
}
</script>
</body>
</html>

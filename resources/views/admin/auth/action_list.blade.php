@extends('admin.shared._layout')
@section('pagecss')
<style>
.action {
  overflow: hidden;
}
.action_list {
  width: 30%;
  float: left;
  border: 1px solid #eee;
  border-radius: 3px;
  padding: 20px;
  box-shadow: 0 4px 8px rgba(3, 27, 78, .12);
}
.action_list .t {
  padding: 0 14px;
  cursor: pointer;
  overflow: hidden;
  border-radius: 3px;
  height: 32px;
  line-height: 32px;
}
.action_list .t:hover {
  background-color: #eee;
}
.action_list .t .icon_1, .action_list .t .icon_0 {
  margin-right: 8px;
  float: left;
}
.action_list .t .name {
  float: left;
  letter-spacing: 1px;
}
.action_list .t .types {
  float: right;
  margin-left: -5px;
  display: none;
}
.action_list .t .types i {
  margin-left: 5px;
}
.action_list .t .icon_1::before {
  content: '\e604';
}
.action_list .t0 .icon_1::before {
  content: '\e638';
}
.action_list .t .icon_2::before {
  content: '\e600';
}
.action_list .t .icon_3::before {
  content: '\e676';
}
.action_list .t1 {
  margin-left: 22px;
}
.action_list .t2 {
  margin-left: 44px;
}

.action_form {
  width: 67%;
  float: right;
  border: 1px solid #eee;
  position: fixed;
  right: 18px;
  top: 90px;
  border-radius: 3px;
  box-shadow: 0 4px 8px rgba(3, 27, 78, .12);
  padding-bottom: 20px;
}
#form_title {
  font-weight: 600;
  letter-spacing: 1px;
  text-align: center;
  padding-right: 80px;
  font-size: 16px;
}
#form_title i::before {
  content: '\e61a';
  margin-right: 5px;
}
</style>
@endsection
@section('content')
<div class="main">
  <div class="bg-white p-4">
    <div class="breadcrumb-stitle">权限管理</div>
  </div>
  <div class="action p-4">
    <div class="action_list bg-white">
      <div class="loading2"></div>
    </div>
    <div class="action_form bg-white">
      <div class="p-4" style="font-size: 12px;">暂不开启~</div>
      <form class="mt-4" id="form" action="/admin/auth/action_set" method="post" autocomplete="off">
        @csrf
        <input type="hidden" name="form_type" value="store" />
        <input type="hidden" name="id" value="" />
        <input type="hidden" name="parent_id" value="0" />
        <div class="mb-4" id="form_title"><span>权限信息</span></div>
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">权限名：</label>
          <div class="col-8">
            <input class="form-control" name="name" value="">
          </div>
        </div>
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">控制器：</label>
          <div class="col-8">
            <input class="form-control" name="controller" value="">
          </div>
        </div>
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end"><i class="iconfont iconfont-question" data-bs-toggle="tooltip" title="" data-bs-original-title="请使用逗号分割"></i> 方法：</label>
          <div class="col-8">
            <textarea class="form-control" name="actions" rows="3"></textarea>
          </div>
        </div>
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">链接：</label>
          <div class="col-8">
            <input class="form-control" name="url" value="">
          </div>
        </div>
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end"><i class="iconfont iconfont-question" data-bs-toggle="tooltip" title="" data-bs-original-title="数字类型，数值越大，排序越高"></i> 排序：</label>
          <div class="col-8">
            <input class="form-control" name="sort" value="">
          </div>
        </div>
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end"></label>
          <div class="col-8">
            <button type="submit" class="btn btn-primary">提交信息</button>
          </div>
        </div>
        
      </form>
    </div>
  </div>
</div>
@endsection
@section('pagejs')
@include('admin.shared._jquery_validation')
<script type="text/javascript">
$(document).ready(function() {
  // 初始化权限
  var load = layer.load();
  $.ajax({
    url: '/admin/auth/getActions',
    type: 'get',
    success: function(res) {
      layer.close(load);
      var str = '';
      str += '<div class="t t0" data-id="0" data-name="权限总览">';
      str += '<i class="iconfont icon_1"></i>';
      str += '<div class="name">权限总览</div>';
      str += '<div class="types"><i class="iconfont icon_2"></i></div>';
      str += '</div>';
      for (var i = 0; i < res.data.length; i++) {
        if (res.data[i].level == 1) {
          str += '<div class="t t1" data-id="' + res.data[i].id + '" data-name="' + res.data[i].name + '">';
          str += '<i class="iconfont icon_1"></i>';
          str += '<div class="name">' + res.data[i].name + '</div>';
          str += '<div class="types"><i class="iconfont icon_2"></i><i class="iconfont icon_3"></i></div>';
          str += '</div>';
        }
        if (res.data[i].level == 2) {
          str += '<div class="t t2" data-id="' + res.data[i].id + '" data-name="' + res.data[i].name + '">';
          str += '<div class="name">' + res.data[i].name + '</div>';
          str += '<div class="types"><i class="iconfont icon_3"></i></div>';
          str += '</div>';
        }
      }
      $('.action_list').html(str);
    }
  })

  $('.action_list').on('mouseover mouseout', '.t', function() {
     $(this).find('.types').toggle();
  })

  // 添加
  $('.action_list').on('click', '.icon_2', function() {
    var load = layer.load();
    var id = $(this).parents('.t').data('id');
    var name = $(this).parents('.t').data('name');
    var form_title_html = '添加子节点';
    if (id > 0) var form_title_html = '添加 ' + name + ' 子节点';
    $('#form_title span').html(form_title_html);
    $('input[name="form_type"]').val('store');
    $('input[name="parent_id"]').val(id);

    $('input[name="id"]').val('');
    $('input[name="name"]').val('');
    $('input[name="controller"]').val('');
    $('textarea[name="actions"]').val('');
    $('input[name="url"]').val('');
    $('input[name="sort"]').val('');
    layer.close(load);
    return false;
  })

  // 删除
  $('.action_list').on('click', '.icon_3', function() {
    var id = $(this).parents('.t').data('id');
    layer.confirm('确认操作？', function() {
      var load = layer.load();
      $.ajax({
        url: '/admin/auth/action_delete?id=' + id,
        type: 'get',
        success: function(res) {
          layer.close(load);
          if (res.code == 200) {
            layer.msg('操作成功', { time: 1500 }, function () { window.location.reload(); });
          } else if (res.code == 400) {
            layer.msg(res.message); return false;
          } else if (res.code == 401) {
            goLogin(); return false;
          } else {
            layer.msg('操作失败'); return false;
          }
        }
      })
    })
    return false;
  })

  // 编辑
  $('.action_list').on('click', '.t', function() {
    var id = $(this).data('id');
    var name = $(this).data('name');
    if (id == 0) return false;

    var form_title_html = '编辑 ' + name + ' 权限信息';
    $('#form_title span').html(form_title_html);
    var load = layer.load();
    $.ajax({
      url: '/admin/auth/getAction?id=' + id,
      type: 'get',
      success: function(res) {
        layer.close(load);
        if (res.code == 200) {
          $('input[name="form_type"]').val('update');
          $('input[name="parent_id"]').val(res.data.parent_id);
          $('input[name="id"]').val(res.data.id);
          $('input[name="name"]').val(res.data.name);
          $('input[name="controller"]').val(res.data.controller);
          $('textarea[name="actions"]').val(res.data.actions);
          $('input[name="url"]').val(res.data.url);
          $('input[name="sort"]').val(res.data.sort);
        } else if (res.code == 400) {
          layer.msg(res.message); return false;
        } else if (res.code == 401) {
          goLogin(); return false;
        } else {
          layer.msg('操作失败'); return false;
        }
      }
    })
    return false;
  })
});

$("#form").validate({
  submitHandler: function() {
    layer.confirm('确认提交？', function() {
      layer.closeAll();
      var load = layer.load();
      $("#form").ajaxSubmit(function(res) {
        layer.close(load);
        if (res.code == 200) {
          layer.msg('提交成功', { time: 1500 }, function () { window.location.reload(); });
        } else if (res.code == 400) {
          layer.msg(res.message); return false;
        } else if (res.code == 401) {
          goLogin(); return false;
        } else {
          layer.msg('操作失败'); return false;
        }
      });
    });
  }
});
</script>
@endsection
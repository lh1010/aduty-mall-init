@extends('admin.shared._layout')
@section('pagecss')
<style>
html, body {
  min-width: 100%;
  max-width: 100%;
}
.action_list2 {
  border: 1px solid #eee;
  border-radius: 3px;
  padding: 20px;
  width: 96%;
  margin: 0 auto !important;
}
.action_list2 .t {
  padding: 0 14px;
  cursor: pointer;
  overflow: hidden;
  border-radius: 3px;
  height: 32px;
  line-height: 32px;
}
.action_list2 .t:hover {
  background-color: #eee;
}
.action_list2 .t .icon_1 {
  margin-right: 8px;
  float: left;
}
.action_list2 .t .icon_1::before {
  content: '\e648';
}
.action_list2 .t.on .icon_1::before {
  content: '\e649';
  color: #0d6efd;
}
.action_list2 .t .name {
  float: left;
  letter-spacing: 1px;
}
.action_list2 .t1 {
  margin-left: 22px;
}
.action_list2 .t2 {
  margin-left: 44px;
}
</style>
@endsection
@section('content')
<div class="main">
  @csrf
  <div class="top_btns bg-white p-4 mb-3">
    <button class="btn btn-success btn-sm" onClick="check_all();">全选</button>
    <button class="btn btn-danger btn-sm" onClick="check_all_cancel();">取消全选</button>
    <button class="btn btn-primary btn-sm" onClick="submit();">提交</button>
  </div>
  <div class="action_list2 bg-white">
    <div class="t t0" data-id="0">
      <i class="iconfont icon_1"></i>
      <div class="name">权限总览</div>
      <div class="types"><i class="iconfont icon_2"></i></div>
    </div>
    @foreach($actions as $key => $value)
    <div class="t t{{$value->level}} @if($value->selected == 1) on @endif" data-id="{{$value->id}}">
      <i class="iconfont icon_1"></i>
      <div class="name">{{$value->name}}</div>
    </div>
    @endForeach
  </div>
</div>
@endsection
@section('pagejs')
<script type="text/javascript">
$('.action_list2').on('click', '.t', function() {
  var id = $(this).data('id');
  $(this).toggleClass('on');
  if (id == 0) {
    if ($(this).hasClass('on') == true) {
      $('.action_list2 .t').addClass('on');
    } else {
      $('.action_list2 .t').removeClass('on');
    }
  }
})

function check_all() {
  $('.action_list2 .t').addClass('on');
}

function check_all_cancel() {
  $('.action_list2 .t').removeClass('on');
}

function submit() {
  var action_ids = '';
  $('.action_list2 .t').each(function(index, value) {
    console.log($(value).hasClass('on'));
    if ($(value).hasClass('on') == true && $(value).data('id') > 0) {
      action_ids += $(value).data('id') + ',';
    }
  })
  if (action_ids != '') action_ids = action_ids.substr(0, action_ids.length - 1);

  layer.confirm('确认操作？', function() {
    var load = layer.load();
    $.ajax({
      url: '/admin/auth/set_role_to_action',
      type: 'post',
      data: {
        _token: $('input[name="_token"]').val(),
        role_id: {{Request()->role_id}},
        action_ids: action_ids,
      },
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
}
</script>
@endsection
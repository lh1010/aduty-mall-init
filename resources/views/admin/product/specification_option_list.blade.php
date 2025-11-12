@extends('admin.shared._layout')
@section('content')
<div class="main">
  <div class="bg-white p-4">
    <div class="breadcrumb-stitle">选项值</div>
    <div class="mt-3" style="font-size: 12px;">所属规格：{{$specification->name}}</div>
  </div>
  <div class="mx-4 mt-4">
    <div class="pagebox">
      <div class="overflow-hidden">
        <div class="float-start row g-2">
          <div class="col-auto" style="font-size: 12px; color: #999;">
            共有 {{$options->total()}} 条数据
          </div>
        </div>
        <div class="float-end row g-2">
          <div class="col-auto">
            <a class="btn btn-primary" href="/admin/product/specification_option_create?specification_id={{Request()->specification_id}}"><i class="iconfont luck-icon-jia"></i> 新增</a>
          </div>
          <div class="col-auto">
            <a class="btn btn-primary" href="javascript:window.location.reload();"><i class="iconfont luck-icon-refresh"></i></a>
          </div>
        </div>
      </div>
      @if($options->total() > 0)
      <table class="table text-center mt-3">
        <thead>
          <tr>
            <th>排序</th>
            <th>ID</th>
            <th>所属规格</th>
            <th>选项值</th>
            <th>状态</th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody>
          @foreach($options as $value)
          <tr>
            <td>{{$value->sort}}</td>
            <td>{{$value->id}}</td>
            <td>{{$value->specification_name}}</td>
            <td>{{$value->option}}</td>
            <td>{{Config('common.mall.status')[$value->status]}}</td>
            <td>
              <a class="btn btn-primary btn-sm" href="/admin/product/specification_option_edit?id={{$value->id}}&specification_id={{Request()->specification_id}}">编辑</a>
              <button type="button" class="btn btn-danger btn-sm" onClick="deleteAction({{$value->id}})">删除</button>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
      <div class="page my-3">{{ $options->appends(Request()->all())->render() }}</div>
      @else
      <div class="noresult">
        <img src="/static/admin/images/noresult.png">
        <p>暂无内容~</p>
      </div>
      @endif
    </div>
  </div>
</div>
@endsection
@section('pagejs')
<script type="text/javascript">
function deleteAction(id = '') {
  if (id == '') {
    var trs = $("tbody :checkbox:checked").parents("tr");
    if (trs.length <= 0) {
      layer.msg('请勾选需删除的数据', {icon: 5, time: 1500});
      return false;
    }
    var ids = '';
    for (var i = 0; i < trs.length; i++) {
      ids += trs[i].id + ',';
    }
    id = ids.substr(0, ids.length - 1);
  }
  var layer_confirm = layer.confirm('确认删除？', function(index) {
    layer.close(layer_confirm);
    var layer_load = layer.load();
    $.ajax({
      type: 'GET',
      url: '/admin/product/specification_option_delete?id=' + id,
      dataType: 'json',
      success: function(res) {
        layer.close(layer_load);
        if (res.code == 200) {
          layer.msg('操作成功', {time: 1500}, function() {window.location.reload();});
        } else if (res.code == 400) {
          layer.msg(res.message);
        } else {
          layer.msg('操作失败');
        }
      }
    });     
  });
}    
</script>
@endsection
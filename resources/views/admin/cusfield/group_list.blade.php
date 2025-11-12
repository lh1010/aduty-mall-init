@extends('admin.shared._layout')
@section('content')
<div class="main">
  <div class="bg-white p-4">
    <div class="breadcrumb-stitle">字段组</div>
  </div>

  <div class="m-4">
    <div class="pagebox">
      <div class="overflow-hidden">
        <div class="float-start row g-2">
          <div class="col-auto" style="font-size: 12px; color: #999;">
            共有 {{$groups->total()}} 条数据
          </div>
        </div>
        <div class="float-end row g-2">
          <div class="col-auto">
            <a class="btn btn-primary" href="/admin/cusfield/group_create"><i class="iconfont luck-icon-jia"></i> 新增</a>
          </div>
          <div class="col-auto">
            <a class="btn btn-primary" href="javascript:window.location.reload();"><i class="iconfont luck-icon-refresh"></i></a>
          </div>
        </div>
      </div>
      @if($groups->total() > 0)
      <table class="table text-center mt-3">
        <thead>
          <tr>
            <th>字段组名</th>
            <th>说明注释</th>
            <th>字段</th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody>
          @foreach($groups as $value)
          <tr>
            <td>{{$value->name}}</td>
            <td title="{{$value->description}}">{{ $value->description ? Str::limit($value->description, 32, '') : '无' }}</td>
            <td>
              <a href="javascript:void(0);" onclick="layerOpen('/admin/cusfield/list?group_id={{$value->id}}', '自定义字段');">{{$value->cusfield_count}}</a>
            </td>
            <td>
              <a class="btn btn-primary btn-sm" href="/admin/cusfield/group_edit?id={{$value->id}}">编辑</a>
              <div class="btn-group">
                <button class="btn btn-success btn-sm dropdown-toggle" data-bs-toggle="dropdown">更多</button>
                 <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="javascript:void(0);" onclick="layerOpen('/admin/cusfield/list?group_id={{$value->id}}', '自定义字段');">字段设置</a></li>
                    <li><a class="dropdown-item" href="javascript:void(0);" onclick="deleteAction({{$value->id}})">删除</a></li>
                  </ul>
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
      <div class="page my-3">{{ $groups->appends(Request()->all())->render() }}</div>
      @else
      <div class="noresult">
        <img src="/static/admin/images/noresult.png">
        <p>暂无内容</p>
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
  layer.confirm('确认删除？', function(index) {
    var load = layer.load();
    $.ajax({
      type: 'GET',
      url: '/admin/cusfield/group_delete?id=' + id,
      dataType: 'json',
      success: function(res) {
        layer.close(load);
        if (res.code == 200) {
          layer.msg('操作成功', { time: 1500 }, function() { window.location.reload(); });
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
      }
    });     
  });
}    
</script>
@endsection
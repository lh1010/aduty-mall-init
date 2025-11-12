@extends('admin.shared._layout')
@section('content')
<div class="main">
  <div class="bg-white p-4">
    <div class="breadcrumb-stitle">管理员列表</div>
  </div>

  <div class="mx-4 mt-4">
    <div class="pagebox">
      <div class="overflow-hidden">
        <div class="float-start row g-2">
          <div class="col-auto" style="font-size: 12px; color: #999;">
            共有 {{$admins->total()}} 条数据
          </div>
        </div>
        <div class="float-end row g-2">
          <div class="col-auto">
            <a class="btn btn-primary" href="/admin/admin/create"><i class="iconfont luck-icon-jia"></i> 新增</a>
          </div>
          <div class="col-auto">
            <a class="btn btn-primary" href="javascript:window.location.reload();"><i class="iconfont luck-icon-refresh"></i></a>
          </div>
        </div>
      </div>
      @if($admins->total() > 0)
      <table class="table text-center mt-3">
        <thead>
          <tr>
          	<th>ID</th>
            <th>账号</th>
            <th>姓名</th>
            <th>邮箱</th>
            <th>手机</th>
            <th>备注</th>
            <th>创建时间</th>
            <th>状态</th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody>
          @foreach($admins as $value)
          <tr>
          	<td>{{$value->id}}</td>
            <td>{{$value->username}}</td>
            <td>{{$value->realname}}</td>
            <td>{{$value->email}}</td>
            <td>{{$value->phone}}</td>
            <td title="{{$value->remark}}">{{ Str::limit($value->remark, 20) }}</td>
            <td>{{ Str::limit($value->created_at, 10, '') }}</td>
            <td>
              @if($value->status == 1)
              <span class="badge bg-success">开启</span>
              @else
              <span class="badge bg-danger">关闭</span>
              @endif
            </td>
            <td>
              <a class="btn btn-primary btn-sm" href="/admin/admin/edit?id={{$value->id}}">编辑</a>
              <a class="btn btn-danger btn-sm" href="javascript:void(0);" onclick="deleteAction({{$value->id}})">删除</a>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
      <div class="page my-3">{{ $admins->appends(Request()->all())->render() }}</div>
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
      url: '/admin/admin/delete?id=' + id,
      dataType: 'json',
      success: function(res) {
        layer.close(load);
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
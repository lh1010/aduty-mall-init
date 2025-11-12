@extends('admin.shared._layout')
@section('pagecss')
<style>
.cover {
  width: 60px;
  height: 60px;
}
</style>
@endsection
@section('content')
<div class="main">
  <div class="bg-white p-4">
    <ol class="breadcrumb">
      <li class="breadcrumb-item">文章中心</li>
      <li class="breadcrumb-item active">文章列表</li>
    </ol>
    <div class="breadcrumb-stitle">文章列表</div>
  </div>
  <div class="m-4">
    <div class="pagebox">
      <form class="row g-2">
        <div class="col-auto">
          <select class="form-select" name="category_id">
            <option value="">全部分类</option>
            <option value="分类">分类</option>
            <option value="分类">分类</option>
            <option value="分类">分类</option>
          </select>
        </div>
        <div class="col-auto">
          <input type="text" class="form-control" placeholder="输入搜索内容" name="k" value="{{Request()->k}}">
        </div>
        <div class="col-auto">
          <button type="submit" class="btn btn-primary">搜索</button>
        </div>
      </form>
    </div>
  </div>
  <div class="mx-4">
    <div class="pagebox">
      <div class="overflow-hidden">
        <div class="float-start row g-2">
          <div class="col-auto" style="font-size: 12px; color: #999;">
            共有 {{$results->total()}} 条数据
          </div>
        </div>
        <div class="float-end row g-2">
          <div class="col-auto">
            <a class="btn btn-primary" href="/admin/article/create"><i class="iconfont luck-icon-jia"></i> 新增</a>
          </div>
          <div class="col-auto">
            <a class="btn btn-primary" href="javascript:window.location.reload();"><i class="iconfont luck-icon-refresh"></i></a>
          </div>
        </div>
      </div>
      @if($results->total() > 0)
      <table class="table text-center mt-3">
        <thead>
          <tr>
            <th>ID</th>
            <th>封面图</th>
            <th>标题</th>
            <th>创建时间</th>
            <th>状态</th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody>
          @foreach($results as $value)
          <tr>
            <td>{{$value->id}}</td>
            <td>
              @if(!empty($value->cover))
              <img src="{{$value->cover}}" class="cover">
              @endif
            </td>
            <td>{{$value->title}}</td>
            <td>{{$value->created_at}}</td>
            <td>{{$value->status_show}}</td>
            <td>
              <a class="btn btn-primary btn-sm" href="/admin/tbname/edit?id={{$value->id}}{{ encodePrevPageParams(); }}">编辑</a>
              <div class="btn-group">
                <button class="btn btn-success btn-sm dropdown-toggle" data-bs-toggle="dropdown">更多</button>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="javascript:void(0);" onclick="layerOpen('/admin/tbname/audit?id={{$value->id}}', '审核', '800px', '80%')">审核</a></li>
                  <li><hr class="dropdown-divider"></li>
                  <li><a class="dropdown-item" href="javascript:void(0);" onclick="deleteAction({{$value->id}})">删除</a></li>
                </ul>
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
      <div class="page my-3">{{ $results->appends(Request()->all())->render() }}</div>
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
  var str = '确认删除？';
  var confirm = layer.confirm(str, function(index) {
    var load = layer.load();
    $.ajax({
      type: 'GET',
      url: '/admin/tbname/delete?id=' + id,
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

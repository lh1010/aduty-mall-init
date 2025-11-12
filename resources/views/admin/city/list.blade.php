@extends('admin.shared._layout')
@section('content')
<div class="main">
  <div class="bg-white p-4">
    <ol class="breadcrumb">
      <li class="breadcrumb-item">地区管理</li>
      <li class="breadcrumb-item active">
        @if(!empty($parent_city))
          @if($parent_city->level == 1) <span>城市</span> @endif
          @if($parent_city->level == 2) <span>县区</span> @endif
        @else
        <span>省份</span>
        @endif
      </li>
    </ol>
    <div class="breadcrumb-stitle">
      @if(!empty($parent_city))
        @if($parent_city->level == 1) <span>城市</span><span class="ms-2" style="color: #999">{{$parent_city->name}}</span> @endif
        @if($parent_city->level == 2) <span>县区</span><span class="ms-2" style="color: #999">{{$parent_city->name}}</span> @endif
      @else
      <span>省份</span>
      @endif
    </div>
  </div>
  <div class="m-4">
    <div class="pagebox">
      <form class="row g-2">
        <input type="hidden" name="pid" value="{{Request()->pid}}" />
        <div class="col-auto">
          <input type="text" class="form-control" placeholder="名称 / 编号" name="k" value="{{Request()->k}}">
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
            共有 {{$citys->total()}} 条数据
          </div>
        </div>
        <div class="float-end row g-2">
          <div class="col-auto">
            <a class="btn btn-primary" href="/admin/city/create?pid={{Request()->pid}}"><i class="iconfont luck-icon-jia"></i> 新增</a>
          </div>
          <div class="col-auto">
            <a class="btn btn-primary" href="javascript:window.location.reload();"><i class="iconfont luck-icon-refresh"></i></a>
          </div>
        </div>
      </div>
      @if($citys->total() > 0)
      <table class="table text-center mt-3" style="font-size: 12px;">
        <thead>
          <tr>
          	<th>编号</th>
            <th>简称</th>
            <th>名称</th>
            <th>拼音</th>
            <th>编码</th>
            <th>等级</th>
            <th>排序</th>
            <th>状态</th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody>
          @foreach($citys as $value)
          <tr>
          	<td>{{$value->id}}</td>
            <td>{{$value->shortname}}</td>
            <td>{{$value->name}}</td>
            <td>{{$value->pinyin}}</td>
            <td>{{$value->zip}}</td>
            <td>{{$value->level}}</td>
            <td>{{$value->sort}}</td>
            <td>{{$value->status}}</td>
            <td>
              @if(empty($parent_city) || $parent_city->level < 2)
              <a class="btn btn-primary btn-sm" href="javascript:void(0);" onclick="layerOpen('/admin/city/list?pid={{$value->id}}', '地区管理');">下级</a>
              @endif
              <a class="btn btn-primary btn-sm" href="/admin/city/edit?id={{$value->id}}{{ Request()->all() ? '&prevPageParams=' . urlencode(http_build_query(Request()->all())) : '' }}">编辑</a>
              <button type="button" class="btn btn-danger btn-sm" onClick="deleteAction({{$value->id}})">删除</button>
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
      <div class="page mt-4">{{ $citys->appends(Request()->all())->render() }}</div>
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
  layer.confirm('确认删除？', function(index) {
    var load = layer.load();
    $.ajax({
      type: 'GET',
      url: '/admin/city/delete?id=' + id,
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

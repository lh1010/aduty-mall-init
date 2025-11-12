@extends('admin.shared._layout')
@section('content')
<div class="main">
  <div class="bg-white p-4">
    <div class="breadcrumb-stitle">平台广告</div>
  </div>

  <div class="m-4">
    <div class="pagebox">
      <form class="row g-2">
        <div class="col-auto">
          <select class="form-select" name="client">
            <option value="">客户端</option>
            @foreach(Config('common.adver.client') as $value)
            <option value="{{$value}}" @if(Request()->client == $value) selected @endif>{{$value}}</option>
            @endforeach
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
            共有 {{$advers->total()}} 条数据
          </div>
        </div>
        <div class="float-end row g-2">
          <div class="col-auto">
            <a class="btn btn-primary" href="/admin/adver/create"><i class="iconfont luck-icon-jia"></i> 新增</a>
          </div>
          <div class="col-auto">
            <a class="btn btn-primary" href="javascript:window.location.reload();"><i class="iconfont luck-icon-refresh"></i></a>
          </div>
        </div>
      </div>
      @if($advers->total() > 0)
      <table class="table text-center mt-3">
        <thead>
          <tr>
            <th>ID</th>
            <th>客户端</th>
            <th>名字</th>
            <th>Code</th>
            <th>备注</th>
            <th>状态</th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody>
          @foreach($advers as $value)
          <tr>
            <td>{{$value->id}}</td>
            <td>{{$value->client}}</td>
            <td>{{$value->name}}</td>
            <td>{{$value->code}}</td>
            <td title="{{$value->remark}}">{{ Str::limit($value->remark, 30) }}</td>
            <td>
              @if($value->status == 1)
              <span class="badge bg-success">开启</span>
              @else
              <span class="badge bg-danger">关闭</span>
              @endif
            </td>
            <td>
              <a class="btn btn-primary btn-sm" href="/admin/adver/edit?id={{$value->id}}">编辑</a>
              <button type="button" class="btn btn-danger btn-sm" onClick="deleteAction({{$value->id}})">删除</button>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
      <div class="page my-3">{{ $advers->appends(Request()->all())->render() }}</div>
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
  var str = '确认删除？<br/>';
  str += '请确认该广告没有被使用';
  var confirm = layer.confirm(str, {btn: ['已确认，确认删除', '取消'], title: '重要提示'}, function(index) {
    var load = layer.load();
    $.ajax({
      type: 'GET',
      url: '/admin/adver/delete?id=' + id,
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

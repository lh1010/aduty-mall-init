@extends('admin.shared._layout')
@section('content')
<div class="main">
  <div class="bg-white p-4">
    <div class="breadcrumb-stitle">卡密管理</div>
  </div>
  <div class="m-4">
    <div class="pagebox">
      <form class="row g-2">
        <div class="col-auto">
          <input type="text" class="form-control" placeholder="ID / 备注" name="k" value="{{Request()->k}}">
        </div>
        <div class="col-auto">
          <select class="form-select" name="used_status">
            <option value="">使用情况</option>
            <option value="1" @if(Request()->used_status == 1) selected @endif>未使用</option>
            <option value="2" @if(Request()->used_status == 2) selected @endif>已使用</option>
          </select>
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
            共有 {{$cdkeys->total()}} 条数据
          </div>
        </div>
        <div class="float-end row g-2">
          <div class="col-auto">
            <a class="btn btn-primary" href="/admin/cdkey/create"><i class="iconfont luck-icon-jia"></i> 新增</a>
          </div>
          <div class="col-auto">
            <a class="btn btn-primary" href="/admin/cdkey/batch_create"><i class="iconfont luck-icon-jia"></i> 批量新增</a>
          </div>
          <div class="col-auto">
            <a class="btn btn-primary" href="javascript:window.location.reload();"><i class="iconfont luck-icon-refresh"></i></a>
          </div>
        </div>
      </div>
      @if($cdkeys->total() > 0)   
      <table class="table table-bordered table-hover text-center mt-3">
        <thead>
          <tr>
          	<th>ID</th>
            <th>卡密内容</th>
            <th>兑换金币</th>
            <th>有效期</th>
            <th>指定用户ID</th>
            <th>使用情况</th>
            <th>创建时间</th>
            <th>备注</th>
            <th>状态</th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody>
          @foreach($cdkeys as $value)
          <tr>
          	<td>{{$value->id}}</td>
            <td>{{$value->key}}</td>
            <td>{{$value->gold}}</td>
            <td>{{ $value->end_date ? $value->end_date . '到期' : '永久' }}</td>
            <td>{{ $value->assign_user_id ? $value->assign_user_id : '无' }}</td>
            <td>
              @if($value->used_status == 2)
              <div style="font-size: 12px;">
                <div>已使用</div>
                <div>使用用户ID：{{$value->used_user_id}}</div>
                <div>使用时间：{{$value->used_date}}</div>
              </div>
              @else
              未使用
              @endif
            </td>
            <td>{{ Str::limit($value->created_at, 10, '') }}</td>
            <td title="{{$value->remark}}">{{ Str::limit($value->remark, 10, '...') }}</td>
            <td>
              @if($value->status == '1')<span class="badge bg-success">开启</span>@endif
              @if($value->status == '0')<span class="badge bg-danger">关闭</span>@endif
            </td>
            <td>
              <a class="btn btn-primary btn-sm" href="/admin/cdkey/edit?id={{$value->id}}">编辑</a>
              <a class="btn btn-danger btn-sm" href="javascript:void(0);" onclick="deleteAction({{$value->id}})">删除</a>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
      <div class="page my-3">{{ $cdkeys->appends(Request()->all())->render() }}</div>
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
  layer.confirm(str, function(index) {
    var load = layer.load();
    $.ajax({
      type: 'GET',
      url: '/admin/cdkey/delete?id=' + id,
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

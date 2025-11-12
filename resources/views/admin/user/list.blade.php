@extends('admin.shared._layout')
@section('pagecss')
<style>
.avatar {
  width: 52px;
  height: 52px;
  border-radius: 50%;
  border: 1px solid #eee;
  padding: 3px;
  vertical-align: top;
  object-fit: cover;
}
</style>
@endsection
@section('content')
<div class="main">
  <div class="bg-white p-4">
    <ol class="breadcrumb">
      <li class="breadcrumb-item">用户中心</li>
      <li class="breadcrumb-item active">用户列表</li>
    </ol>
    <div class="breadcrumb-stitle">用户列表</div>
  </div>
  <div class="m-4">
    <div class="pagebox">
      <form class="row g-2">
        <div class="col-auto">
          <input type="text" class="form-control" placeholder="用户名 / ID / 手机号" name="k" value="{{Request()->k}}">
        </div>
        <div class="col-auto">
          <select class="form-select" name="realname_auth">
            <option value="">实名认证</option>
            @foreach(Config('common.user.realname_auth_status') as $key => $value)
            <option value="{{$key}}" @if(Request()->realname_auth == "$key") selected @endif>{{$value}}</option>
            @endforeach
          </select>
        </div>
        <div class="col-auto">
          <select class="form-select" name="company_auth">
            <option value="">企业认证</option>
            @foreach(Config('common.user.company_auth_status') as $key => $value)
            <option value="{{$key}}" @if(Request()->company_auth == "$key") selected @endif>{{$value}}</option>
            @endforeach
          </select>
        </div>
        <div class="col-auto">
          <select class="form-select" name="order">
            <option value="">排序方式</option>
            <option value="钱包最多" @if(Request()->order == "钱包最多") selected @endif>钱包最多</option>
            <option value="金币最多" @if(Request()->order == "金币最多") selected @endif>金币最多</option>
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
            共有 {{$users->total()}} 条数据
          </div>
        </div>
        <div class="float-end row g-2">
          <div class="col-auto">
            <a class="btn btn-primary" href="/admin/user/create"><i class="iconfont luck-icon-jia"></i> 新增</a>
          </div>
          <div class="col-auto">
            <a class="btn btn-primary" href="javascript:window.location.reload();"><i class="iconfont luck-icon-refresh"></i></a>
          </div>
        </div>
      </div>
      @if($users->total() > 0)
      <table class="table text-center mt-3" style="font-size: 12px;">
        <thead>
          <tr>
          	<th>ID</th>
            <th>昵称</th>
            <th>头像</th>
            <th>手机</th>
            <th>钱包</th>
            <th>金币</th>
            <th>注册时间</th>
            <th>注册客户端</th>
            <th>身份认证</th>
            <th>状态</th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody>
          @foreach($users as $value)
          <tr>
          	<td>{{$value->id}}</td>
            <td>{{$value->nickname}}</td>
            <td>
              <a href="{{$value->avatar}}" target="_blank">
                <img src="{{$value->avatar}}" class="avatar">
              </a>
            </td>
            <td>{{$value->phone}}</td>
            <td>
              <a href="javascript:void(0);" onclick="layerOpen('/admin/user/wallet?user_id={{$value->id}}', '钱包管理', '800px', '560px');">{{$value->wallet}}</a>
            </td>
            <td>
              <a href="javascript:void(0);" onclick="layerOpen('/admin/user/gold?user_id={{$value->id}}', '金币管理', '800px', '560px');">{{$value->gold}}</a>
            </td>
            <td>{{$value->created_at}}</td>
            <td>{{$value->register_client}}</td>
            <td style="font-size: 12px;">
              <div>实名认证：<a href="javascript:layerOpen('/admin/user/realname_auth?user_id={{$value->id}}', '实名认证');">{{Config('common.user.realname_auth_status')[$value->realname_auth]}}</a></div>
              <div>企业认证：<a href="javascript:layerOpen('/admin/user/company_auth?user_id={{$value->id}}', '企业认证');">{{Config('common.user.company_auth_status')[$value->company_auth]}}</a></div>
            </td>
            <td>{{$value->status_show}}</td>
            <td>
              <a class="btn btn-primary btn-sm" href="/admin/user/edit?id={{$value->id}}{{ Request()->all() ? '&prevPageParams=' . urlencode(http_build_query(Request()->all())) : '' }}">编辑</a>
              <div class="btn-group">
                <button class="btn btn-success btn-sm dropdown-toggle" data-bs-toggle="dropdown">更多</button>
                 <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="javascript:void(0);" onclick="layerOpen('/admin/user/wallet?user_id={{$value->id}}', '钱包管理', '800px', '560px');">钱包管理</a></li>
                  <li><a class="dropdown-item" href="javascript:void(0);" onclick="layerOpen('/admin/user/gold?user_id={{$value->id}}', '金币管理', '800px', '560px');">金币管理</a></li>
                </ul>
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
      <div class="page mt-4">{{ $users->appends(Request()->all())->render() }}</div>
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
      url: '/admin/job/delete?id=' + id,
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

@extends('admin.shared._layout')
@section('pagecss')
<style>
.cover {
  width: 70px;
  height: 70px;
  border-radius: 5px;
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
    <div class="breadcrumb-stitle">订单列表</div>
  </div>
  <div class="m-4">
    <div class="pagebox">
      <form class="row g-2">
        <div class="col-auto">
          <div class="input-group">
            <select class="form-select" name="kident">
              <option value="订单编号" @if(Request()->kident == '订单编号') selected @endif>订单编号</option>
              <option value="用户ID" @if(Request()->kident == '用户ID') selected @endif>用户ID</option>
              <option value="用户昵称" @if(Request()->kident == '用户昵称') selected @endif>用户昵称</option>
              <option value="收货人手机" @if(Request()->kident == '收货人手机') selected @endif>收货人手机</option>
              <option value="收货人姓名" @if(Request()->kident == '收货人姓名') selected @endif>收货人姓名</option>
              <option value="收货人手机" @if(Request()->kident == '收货人手机') selected @endif>收货人手机</option>
            </select>
            <input type="text" class="form-control" placeholder="请输入搜索内容" name="k" value="{{Request()->k}}">
          </div>
        </div>
        <div class="col-auto">
          <select class="form-select" name="status">
            <option value="">订单状态</option>
            @foreach(Config('common.mall.order_status') as $key => $value)
            <option value="{{$key}}" @if(Request()->status == "$key") selected @endif>{{$value}}</option>
            @endforeach
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
            共有 {{$orders->total()}} 条数据
          </div>
        </div>
        <div class="float-end row g-2">
          <div class="col-auto">
            <a class="btn btn-primary" href="javascript:window.location.reload();"><i class="iconfont luck-icon-refresh"></i></a>
          </div>
        </div>
      </div>
      @if($orders->total() > 0)
      <table class="table text-center mt-3" style="font-size: 12px;">
        <thead>
          <tr>
            <th>订单编号</th>
            <th>订单总价</th>
            <th>用户ID</th>
            <th>用户昵称</th>
            <th>收货人姓名</th>
            <th>收货人手机</th>
            <th width="200px">收货地址</th>
            <th>订单状态</th>
            <th>创建时间</th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody>
          @foreach($orders as $value)
          <tr>
            <td>{{$value->number}}</td>
            <td class="text-danger">¥{{$value->total_price}}</td>
            <td>{{$value->user_id}}</td>
            <td>{{$value->user_nickname}}</td>
            <td>{{$value->name}}</td>
            <td>{{$value->phone}}</td>
            <td>
              {{$value->province_name}} {{$value->city_name}} {{$value->district_name}} {{$value->detailed_address}}
            </td>
            <td>{{Config('common.mall.order_status')[$value->status]}}</td>
            <td>{{$value->created_at}}</td>
            <td>
              <a class="btn btn-primary btn-sm" href="/admin/order/show?id={{$value->id}}{{ encodePrevPageParams(); }}">详情</a>
              <div class="btn-group">
                <button class="btn btn-success btn-sm dropdown-toggle" data-bs-toggle="dropdown">更多</button>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="javascript:void(0);" onclick="cancelOrder({{$value->id}})">取消订单</a></li>
                  <li><a class="dropdown-item" href="javascript:void(0);" onclick="layerOpen('/admin/order/shipmentOrder?id={{$value->id}}', '订单发货', '800px', '80%')">订单发货</a></li>
                  <li><a class="dropdown-item" href="javascript:void(0);" onclick="receiveOrder({{$value->id}});">确认收货，完成订单</a></li>
                </ul>
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
      <div class="page my-3">{{ $orders->appends(Request()->all())->render() }}</div>
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
      url: '/admin/product/delete?id=' + id,
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

// 取消订单
function cancelOrder(id) {
  var confirm_str = '确认操作？';
  var confirm = layer.confirm(confirm_str, function() {
    layer.close(confirm);
    var load = layer.load();
    $.ajax({
      url: '/admin/order/cancelOrder',
      type: 'get',
      data: { id: id },
      success: function(res) {
        layer.close(load);
        if (res.code == 200) {
          layer.msg('操作成功', {time: 1500}, function() {
            window.location.reload();
          });
        } else if (res.code == 400) {
          layer.alert(res.message);
        } else if (res.code == 401) {
          goLogin();
        } else {
          layer.alert('操作失败');
        }
      }
    })
  });
}

// 确认收货
function receiveOrder(id = '') {
  var str = '确认操作？';
  var confirm = layer.confirm(str, function(index) {
    layer.close(confirm);
    var load = layer.load();
    $.ajax({
      type: 'GET',
      url: '/admin/order/receiveOrder?id=' + id,
      success: function(res) {
        layer.close(load);
        if (res.code == 200) {
          layer.msg('操作成功', {time: 1500}, function() {
            window.location.reload();
          });
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
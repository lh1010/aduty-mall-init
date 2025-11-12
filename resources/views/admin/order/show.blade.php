@extends('admin.shared._layout')
@section('pagecss')
<style>
.specifications .spcnitems {
  margin-left: -2px;
}
.specifications .spcnitem {
  color: #999;
  margin-left: 2px;
}
</style>
@endsection
@section('content')
<div class="main order_show">
  <div class="bg-white p-4">
    <ol class="breadcrumb">
      <li class="breadcrumb-item">订单列表</li>
      <li class="breadcrumb-item active">详情</li>
    </ol>
    <div class="breadcrumb-stitle">
      <a href="javascript:history.go(-1);"><i class="iconfont goback"></i></a>
      <span class="txt">详情</span>
    </div>
  </div>

  <div class="m-4">
    <div class="pagebox">
      <div class="box">
        <div class="row mb-3">
          <div class="col-3">
            <span class="span1">订单状态：</span>
            <span class="span2 text-danger"><b>{{Config('common.mall.order_status')[$order->status]}}</b></span>
          </div>
        </div>
        <div class="row mb-3">
          <div class="col">
            <button class="btn btn-sm btn-secondary" onclick="cancelOrder({{$order->id}})">取消订单</button>
            <button class="btn btn-sm btn-primary" onclick="layerOpen('/admin/order/shipmentOrder?id={{$order->id}}', '订单发货', '800px', '80%')">订单发货</button>
            <button class="btn btn-sm btn-success" onclick="receiveOrder({{$order->id}});">确认收货，完成订单</button>
          </div>
        </div>
      </div>
    </div>

    <div class="pagebox mt-4">
      <div class="mb-4"><b>订单日志</b></div>
      <div class="box">
        @foreach($order->logs as $value)
        <div class="row mb-1">
          <div class="col">{{$value->created_at}} {{$value->content}}</div>
        </div>
        @endforeach
        @if(!in_array($order->status, [-110, 30]))
        <div class="row mb-1">
          <div class="col">......</div>
        </div>
        @endif
      </div>
    </div>

    <div class="pagebox mt-4">
      <div class="mb-4"><b>订单信息</b></div>
      <div class="box">
        <div class="row mb-3">
          <div class="col-3">
            <span class="span1">订单编号：</span><span class="span2">{{$order->number}}</span>
          </div>
          <div class="col-3">
            <span class="span1">订单总价：</span><span class="span2 text-danger">¥{{$order->total_price}}</span>
          </div>
          <div class="col-3">
            <span class="span1">用户ID：</span><span class="span2">{{$order->user_id}}</span>
          </div>
          <div class="col-3">
            <span class="span1">用户昵称：</span><span class="span2">{{$order->user_nickname}}</span>
          </div>
        </div>
        <div class="row mb-3">
          <div class="col-3">
            <span class="span1">收货人姓名：</span><span class="span2">{{$order->name}}</span>
          </div>
          <div class="col-3">
            <span class="span1">收货人手机：</span><span class="span2">{{$order->phone}}</span>
          </div>
        </div>
        <div class="row mb-3">
          <div class="col">
            <span class="span1">收货地址：</span>
            <span class="span2">
              {{$order->province_name}} {{$order->city_name}} {{$order->district_name}} {{$order->detailed_address}}
            </span>
          </div>
        </div>
      </div>
    </div>

    @if(in_array($order->status, [20, 30]))
    <div class="pagebox mt-4">
      <div class="mb-4"><b>物流信息</b></div>
      <div class="box">
        <div class="row mb-3">
          <div class="col-3">
            <span class="span1">物流公司：</span><span class="span2">{{$order->shipping_company}}</span>
          </div>
          <div class="col-3">
            <span class="span1">物流单号：</span><span class="span2">{{$order->tracking_number}}</span>
          </div>
        </div>
      </div>
    </div>
    @endif

    <div class="snap_list pagebox mt-4">
      <div class="mb-4"><b>订单中的商品</b></div>
      <div class="items">
        <table class="table text-center mt-3" style="font-size: 12px;">
          <thead>
            <tr>
              <th>商品ID</th>
              <th>sku</th>
              <th>封面图</th>
              <th class="text-start">商品名字</th>
              <th>单价</th>
              <th>数量</th>
              <th>总价</th>
            </tr>
          </thead>
          <tbody>
            @foreach($order->snaps as $value)
            <tr>
              <td>{{$value->product_id}}</td>
              <td>{{$value->sku}}</td>
              <td><img class="cover" src="{{$value->cover}}" /></td>
              <td class="text-start">
                <div>{{$value->name}}</div>
                @if($value->specifications)
                <div class="specifications">
                  <div class="spcnitems">
                    @foreach($value->specifications as $value_spcn)
                    <span class="spcnitem">{{$value_spcn['specification_name']}}-{{$value_spcn['specification_option']}}</span>
                    @endforeach
                  </div>
                </div>
                @endif
              </td>
              <td class="text-danger">¥{{$value->price}}</td>
              <td>{{$value->count}}</td>
              <td class="text-danger">¥{{$value->price}}</td>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>
@endsection
@section('pagejs')
<script type="text/javascript">
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
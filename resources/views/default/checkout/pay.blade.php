@extends(Config('common.view.tpl_folder') . '.shared._layout')
@section('title', '收银台 ' . Config('common.pc.app_name'))
@section('keywords', '收银台 ' . Config('common.pc.app_name'))
@section('description', '收银台 ' . Config('common.pc.app_name'))
@section('pagecss')
<link rel="stylesheet" type="text/css" href="/static/default/style/checkout.css?v={{Config('common.pc.version')}}" />
@endsection
@section('content')
<input type="hidden" id="order_ids" value="{{Request()->order_ids}}">
<div class="checkout">
  <div class="container">
    <div class="nav">
      <span class="on">当前位置：</span><a href="/">首页</a><span>></span><span class="on">收银台</span>
    </div>
    <div class="paymain">
      <div class="pm_title">收银台</div>
      <div class="pm1">
        <div class="d1">结算总费用<span class="price text-price">{{$totalData['total_price']}}</span>元</div>
        <div class="d2">您的可用余额：<span class="price text-price">{{$user->wallet}}</span>元</div>
      </div>
      <div class="pm_orders">
        <div class="stitle">订单信息</div>
        @foreach($orders as $value)
        <div class="item">
          <span class="sp1">订单号:</span>
          <span class="sp2">{{$value->number}}</span>
          <a href="/account/order_show?id={{$value->id}}" target="_blank">查看详情</a>
        </div>
        @endforeach
      </div>
      <div class="payment">
        <div class="payment_top none"><span class="title">支付方式</span></div>
        <div class="payment_items">
          <div class="item on" data-name="alipay_pc">
            <i class="iconfont"></i>
            <img src="/static/default/images/pay_02.jpg">
          </div>
          <div class="item" data-name="weixinpay_native">
            <i class="iconfont"></i>
            <img src="/static/default/images/pay_01.jpg">
          </div>
          <div class="item" data-name="wallet">
            <i class="iconfont"></i>
            <img src="/static/default/images/pay_03.png">
          </div>
        </div>
        <div class="payment_btns"><button type="button" class="btn layui-btn layui-btn-danger" onclick="pay()">立即付款</button></div>
      </div>
    </div>
  </div>
</div>
@endsection
@section('pagejs')
<script type="text/javascript">
$(document).ready(function() {
  $('.payment_items .item').click(function() {
    $(this).addClass('on').siblings().removeClass("on");
  })
})

function pay() {
  var payment_way = $('.payment_items').find('.on').eq(0).data('name');

  layer.confirm('确认支付？', function() {
    layer.closeAll();
    var load = layer.load();

    $.ajax({
      url: '/api/payment/pay_order',
      type: 'post',
      data: {
        user_token: $('#user_token').val(),
        payment_way: payment_way,
        order_ids: $('#order_ids').val()
      },
      success: function(res) {
        layer.close(load);
        if (res.code == 200) {
          if (payment_way == 'alipay_pc') {
            window.location.href = res.data.url;
          }
          if (payment_way == 'weixinpay_native') {
            showPopup_weixinpay(res.data.qrcode);
          }
          if (payment_way == 'wallet') {
            layer.msg('支付成功...', {time: 1500}, function() {
              window.location.href = '/account/order_list';
            });
          }
        } else if (res.code == 400) {
          layer.msg(res.message);
        } else if (res.code == 401) {
          goLogin();
        } else {
          layer.msg('操作失败');
        }
      }
    })
  })
}

var popupIndex_weixinpay;
function showPopup_weixinpay(qrcode) {
  var str = '';
  str += '<div class="popup_weixinpay" id="popup_weixinpay">';
  str += '<div class="close iconfont" onclick="closePopup_weixinpay();"></div>';
  str += '<div class="title">微信扫码支付</div>';
  str += '<div class="tip">请使用微信扫码进行支付</div>';
  str += '<div class="qrcode"><img src="' + qrcode + '"></div>';
  str += '<div class="btns"><a type="button" class="layui-btn layui-btn-danger" href="/account/order">已完成支付</a></div>';
  str += '</div>';

  popupIndex_weixinpay = layer.open({
    type: 1,
    title: false,
    area:['auto'],
    closeBtn: 0,
    shadeClose: true,
    skin: 'popup_weixinpay_addition_class',
    content: str
  });
}
function closePopup_weixinpay() {
  layer.close(popupIndex_weixinpay);
}
</script>
@endsection

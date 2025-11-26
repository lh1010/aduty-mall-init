@extends(Config('common.view.tpl_folder') . '.account._layout_account')
@section('title', '钱包充值 ' . Config('common.pc.app_name'))
@section('content')
<div class="account clearfix">
  <div class="container">
    @include(Config('common.view.tpl_folder') . '.account._left_menu')
    <div class="account_main">
      <div class="am_unify_box">
        <div class="top">
          <span class="title"><a href="/account/wallet">我的钱包</a> > 钱包充值</span>
        </div>
        <div class="layui-form layui-form-pane wallet_pay_box">
          <div class="layui-form-item">
            <label class="layui-form-label">充值金额</label>
            <div class="layui-input-inline">
              <input type="text" name="price" placeholder="¥" autocomplete="off" class="layui-input">
            </div>
            <div class="layui-form-mid layui-word-aux">请输入要充值的金额</div>
          </div>
          <div class="payment">
            <div class="payment_top"><span class="title">支付方式</span></div>
            <div class="payment_items">
              <div class="item on" data-name="alipay_pc">
                <i class="iconfont"></i>
                <img src="/static/default/images/pay_02.jpg">
              </div>
              <div class="item" data-name="weixinpay_native">
                <i class="iconfont"></i>
                <img src="/static/default/images/pay_01.jpg">
              </div>
            </div>
            <div class="payment_btns"><button type="button" class="btn layui-btn layui-btn-danger" onclick="pay()">立即付款</button></div>
          </div>
        </div>
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
  var price = $('input[name="price"]').val();

  layer.confirm('确认支付？', function() {
    layer.closeAll();
    var load = layer.load();

    $.ajax({
      url: '/api/payment/pay_wallet',
      type: 'post',
      data: {
        user_token: $('#user_token').val(),
        payment_way: payment_way,
        price: price
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
            layer.msg('支付成功...', {time: 1500}, function() {window.location.href = '/account/gold'});
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
  str += '<div class="btns"><a type="button" class="layui-btn layui-btn-danger" href="/account/wallet">已完成支付</a></div>';
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

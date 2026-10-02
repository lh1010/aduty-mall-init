<template>
	<view class="page">
		<CustomTop topTitle="收银台"></CustomTop>
		<view class="paypage" v-if="!loading && paymentStatus == ''">
			<view class="pricebox">
				<span class="price">¥{{totalData.total_price}}</span>
			</view>
      <view class="pay_orders">
        <view class="stitle">订单信息</view>
        <view class="items">
          <view class="item" v-for="(item, index) in orders" :key="index" @click="uni.navigateTo({url: '/pages/order/show?id=' + item.id})">
            <span class="sp1">订单号：{{item.number}}</span>
            <span class="spa">查看详情</span>
          </view>
        </view>
      </view>
			<view class="payment">
				<view class="btop"><span class="txt">支付方式</span></view>
				<view class="items">
					<view class="item" :class="{ on: paymentWay == 'weixinpay' }" @click="paymentWay = 'weixinpay'">
						<view class="itembox">
							<view class="left">
								<image class="icon" src="@/static/images/icon_weixinpay.png" />
								<span class="txt">微信支付</span>
							</view>
							<i class="iconfont icon-yuanxingweixuanzhong"></i>
						</view>
					</view>
					<view class="item" :class="{ on: paymentWay == 'alipay_wap' }" @click="paymentWay = 'alipay_wap'">
						<view class="itembox">
							<view class="left">
								<image class="icon" src="@/static/images/icon_alipay.png" />
								<span class="txt">支付宝支付</span>
							</view>
							<i class="iconfont icon-yuanxingweixuanzhong"></i>
						</view>
					</view>
					<view class="item" :class="{ on: paymentWay == 'wallet' }" @click="paymentWay = 'wallet'">
						<view class="itembox">
							<view class="left">
								<image class="icon" src="@/static/images/icon_wallet.png" />
								<span class="txt">余额支付</span>
							</view>
							<i class="iconfont icon-yuanxingweixuanzhong"></i>
						</view>
					</view>
				</view>
			</view>

			<view class="foot_action">
				<view class="btn" @click="payFun">立即支付</view>
			</view>
		</view>

		<view class="result" v-if="paymentStatus == 'success'">
			<view class="txt"><i class="iconfont icon-yuanxingxuanzhong"></i>支付成功</view>
			<view class="btns">
				<button class="adutyBtn aduty-btn-default" @click="uni.navigateTo({url: '/pages/order/list'})">查看订单</button>
			</view>
		</view>
	</view>
</template>

<script setup>
import { ref } from 'vue';
import { onLoad, onShow } from '@dcloudio/uni-app';
import http from "@/utils/http.js";
import util from "@/utils/util.js";
import CustomTop from "@/components/CustomTop.vue";
import config from "@/utils/config.js";

const loading = ref(true);
const orderIds = ref('');
const orders = ref([]);
const totalData = ref({});
const paymentWay = ref('weixinpay');
const paymentStatus = ref('');

onLoad((options) => {
  uni.setNavigationBarTitle({ title: '收银台' });
  orderIds.value = options.order_ids;
});

onShow(() => {
  getOrderPayData();
});

const payFun = () => {
  if (config.env == 'dev') {
    uni.showModal({ content: '演示版不支持支付，正式版恢复此功能，可咨询正式版本。', showCancel: false });
    return;
  }
  uni.showModal({
    content: '确认支付？',
    success(res) {
      if (res.confirm) {
        uni.showLoading();
        let params = { order_ids: orderIds.value };
        let payWay = paymentWay.value;
        if (payWay == 'weixinpay') {
          if (util.isWx()) {
            payWay = 'weixinpay_jsapi_wxmp';
          } else {
            payWay = 'weixinpay_h5';
          }
        }
        params.payment_way = payWay;
        http.post('/payment/pay_order', params).then((res) => {
          uni.hideLoading();
          if (res.code == 400) {
            uni.showToast({ title: res.message, icon: 'none' });
            return;
          }
          if (payWay == 'alipay_wap') {
            document.querySelector('body').innerHTML = res.data;
            document.forms[0].submit();
          }
          if (payWay == 'weixinpay_h5') {
            window.location.href = res.data.url;
          }
          if (payWay == 'weixinpay_jsapi_wxmp') {
            payWxmp(res.data.jsApiParams);
          }
          if (payWay == 'weixinpay_jsapi_wxapp') {
            payWxapp(res.data.jsApiParams);
          }
          if (payWay == 'wallet') {
            uni.showToast({
              title: '支付成功',
              icon: 'success',
              duration: 1500,
              success: function() {
                paymentStatus.value = 'success';
              }
            });
          }
        });
      }
    }
  });
};

// h5
const payWxmp = (jsApiParams) => {
  const invoke = () => {
    WeixinJSBridge.invoke(
      'getBrandWCPayRequest', {
        appId: jsApiParams.appId,
        timeStamp: jsApiParams.timeStamp,
        nonceStr: jsApiParams.nonceStr,
        package: jsApiParams.package,
        signType: jsApiParams.signType,
        paySign: jsApiParams.paySign
      },
      function (res) {
        if (res.err_msg == "get_brand_wcpay_request:ok") {
          paymentStatus.value = 'success';
        } else if (res.err_msg == "get_brand_wcpay_request:cancel") {
          uni.showToast({ title: '取消支付', icon: 'none' });
        } else {
          uni.showToast({ title: '支付失败', icon: 'none' });
          console.log('payWxmp fail:' + JSON.stringify(res));
        }
      }
    );
  };
  if (typeof WeixinJSBridge === 'undefined') {
    document.addEventListener('WeixinJSBridgeReady', invoke, false);
  } else {
    invoke();
  }
};

// wxapp
const payWxapp = (jsApiParams) => {
  uni.requestPayment({
    provider: 'wxpay',
    timeStamp: jsApiParams.timeStamp,
    nonceStr: jsApiParams.nonceStr,
    package: jsApiParams.package,
    signType: jsApiParams.signType,
    paySign: jsApiParams.paySign,
    success: function (resSuccess) {
      paymentStatus.value = 'success';
    },
    fail: function (resFail) {
      if (resFail.errMsg == 'requestPayment:fail cancel') {
        uni.showToast({ title: '取消支付', icon: 'none' });
      } else {
        uni.showToast({ title: '支付失败', icon: 'none' });
      }
      console.log('payWxapp fail:' + JSON.stringify(resFail));
    }
  });
};

const getOrderPayData = () => {
  uni.showLoading();
  http.post('/order/getOrderPayData', { order_ids: orderIds.value }).then(res => {
    loading.value = false;
    uni.hideLoading();
    orders.value = res.data.orders;
    totalData.value = res.data.totalData;
  });
};
</script>

<style>
@import url("pay.css");
.payment {
  background-color: #fff;
  border-radius: 2px;
  margin-top: 30rpx;
	font-size: 14px;
}
.payment .btop {
	padding: 30rpx;
	border-bottom: 1px solid #f5f5f5;
	font-weight: bold;
}
.payment .item {
	width: 100%;
	height: 120rpx;
	line-height: 120rpx;
	overflow: hidden;
	border-bottom: 1px solid #f5f5f5;
}
.payment .itembox {
	padding: 0 30rpx;
	display: flex;
	justify-content: space-between;
}
.payment .item .left {
	display: flex;
	align-items: center;
}
.payment .item .icon {
	width: 54rpx;
  height: 54rpx;
}
.payment .item .txt {
	margin-left: 6px;
}
.payment .item .iconfont {
	font-size: 16px;
}
.payment .item.on .iconfont:before {
	color: #ff0000;
  content: '\e731';
}
</style>

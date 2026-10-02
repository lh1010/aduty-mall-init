<template>
  <view class="page">
    <CustomTop topTitle="充值金币"></CustomTop>
    <view class="container" v-if="!loading">
    	<view class="price_list">
				<view class="items">
					<view class="item" :class="gold == item.gold ? 'on' : ''" v-for="(item, index) in goldPrices" :key="index" @click="gold = item.gold; price = item.price;">
						<view class="itembox">
							<view class="gold">{{ item.gold }}金币</view>
							<view class="money">¥{{ item.price }}</view>
						</view>
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

      <view class="payment_foot">
        <view class="description">应付金额：<span class="aduty-text-price">{{ price }}元</span></view>
        <button class="aduty-btn aduty-btn-payment" hover-class="aduty-btn-hover" @click="payFun">立即支付</button>
      </view>
    </view>
  </view>
</template>

<script setup>
import { ref } from 'vue';
import { onLoad } from '@dcloudio/uni-app';
import http from "@/utils/http.js";
import util from "@/utils/util.js";
import CustomTop from "@/components/CustomTop.vue";
import config from "@/utils/config.js";

const loading = ref(true);
const sysConfig = ref({});
const goldPrices = ref([]);
const gold = ref(0);
const price = ref(0);
const paymentWay = ref('weixinpay');

onLoad(() => {
  uni.setNavigationBarTitle({ title: '充值金币' });
  http.post('/common/getConfig').then(res => {
    loading.value = false;
    sysConfig.value = res.data;
    goldPrices.value = res.data.gold_prices;
    gold.value = res.data.gold_prices[0].gold;
    price.value = res.data.gold_prices[0].price;
  });
});

const payFun = () => {
  /* 演示版本 start */
  if (config.env == 'dev') {
    uni.showModal({ content: '演示版不支持支付，正式版恢复此功能，可咨询正式版本。', showCancel: false });
    return;
  }
  /* 演示版本 end */
  if (!gold.value) {
    uni.showToast({ title: '请选择充值金币', icon: 'none' });
    return;
  }
  uni.showModal({
    content: '确认支付？',
    success(res) {
      if (res.confirm) {
        uni.showLoading();
        let params = { gold: gold.value };
        let pw = paymentWay.value;
        // 微信内打开
        if (pw == 'weixinpay') {
          if (util.isWx()) {
            pw = 'weixinpay_jsapi_wxmp';
          } else {
            pw = 'weixinpay_h5';
          }
        }
        params.payment_way = pw;
        http.post('/payment/pay_gold', params).then(res => {
          uni.hideLoading();
          if (res.code == 400) {
            uni.showToast({ title: res.message, icon: 'none' });
            return;
          }
					if (pw == 'weixinpay_jsapi_wxmp') {
					  payWxmp(res.data.jsApiParams);
					}
					if (pw == 'weixinpay_jsapi_wxapp') {
					  payWxapp(res.data.jsApiParams);
					}
					if (pw == 'weixinpay_h5') {
					  window.location.href = res.data.url;
					}
					// app内使用微信h5支付
					// if (pw == 'weixinpay_h5') {
					//   const webview = plus.webview.create('', sysConfig.value.app_url);
					//   webview.loadURL(res.data.url, {'Referer': sysConfig.value.app_url});
					//   return;
					// }
          if (pw == 'alipay_wap') {
            document.querySelector('body').innerHTML = res.data;
            document.forms[0].submit();
          }
          // app内使用 alipay_jsapi APP支付
          // if (pw == 'alipay_jsapi') {
          //   uni.requestPayment({
          //     provider: 'alipay',
          //     orderInfo: res.data,
          //     success: function (res) {
          //         console.log('success:' + JSON.stringify(res));
          //         uni.showToast({
          //           title: '支付成功',
          //           icon: 'success',
          //           duration: 1500,
          //           success: function () {
          //             setTimeout(function() {
          //               uni.navigateBack({ delta: 1 });
          //             }, 1500);
          //           }
          //         });
          //     },
          //     fail: function (err) {
          //         console.log('fail:' + JSON.stringify(err));
          //     }
          //   });
          // }
          if (pw == 'wallet') {
            uni.showToast({
              title: '支付成功',
              icon: 'success',
              duration: 1500,
              success: function () {
                setTimeout(function() {
                  uni.navigateBack({ delta: 1 });
                }, 1500);
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
          uni.showToast({
            title: '支付成功',
            icon: 'success',
            duration: 1500,
            success: function () {
              setTimeout(function() {
                uni.navigateBack({ delta: 1 });
              }, 1500);
            }
          });
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
    // 微信注入完成后再调用
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
      uni.showToast({
        title: '充值成功',
        icon: 'success',
        duration: 1500,
        success: function () {
          setTimeout(function() {
            uni.navigateBack({ delta: 1 });
          }, 1500);
        }
      });
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
</script>

<style>
page {
  padding-bottom: 60rpx;
}
.price_list {
	overflow: hidden;
	margin-top: 30rpx;
}
.price_list .items {
	display: flex;
	flex-wrap: wrap;
	gap: 30rpx 30rpx;
}
.price_list .item {
	width: 330rpx;
	height: 220rpx;
	border: 1px solid #eee;
	border-radius: 2px;
	background-color: #fff;
	text-align: center;
}
.price_list .item.on {
	border: 1px solid #d8b66c;
}
.price_list .item .itembox {
	padding-top: 70rpx;
}
.price_list .item:nth-child(2n) {
	margin-right: 0;
}
.price_list .item .gold {
	font-size: 16px;
	font-weight: bold;
}
.price_list .item .money {
	font-size: 14px;
	color: #ff0000;
  margin-top: 5px;
}

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
.payment_foot {
  margin-top: 50rpx;
}
.payment_foot .description {
	color: #999;
	font-size: 12px;
	margin-bottom: 30rpx;
}
</style>

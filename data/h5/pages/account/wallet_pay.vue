<template>
  <view class="page">
    <CustomTop topTitle="钱包充值"></CustomTop>
    <view class="container">
      <view class="aduty-form">
        <view class="aduty-form-box">
          <view class="aduty-form-box-top"><span class="txt">充值金额</span></view>
          <view class="aduty-form-cell">
            <view class="aduty-form-cell-box">
              <view class="aduty-form-cell-bd">
                <input v-model="form.price" class="aduty-form-input" type="text" placeholder="请输入充值金额" />
              </view>
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
      	</view>
      </view>
      <view class="payment_foot">
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

const form = ref({ price: '' });
const paymentWay = ref('weixinpay');
const sysConfig = ref({});

onLoad(() => {
  uni.setNavigationBarTitle({ title: '钱包充值' });
  http.post('/common/getConfig').then(res => {
    sysConfig.value = res.data;
  });
});

const payFun = () => {
  /* 演示版本 start */
  if (config.env == 'dev') {
    uni.showModal({ content: '演示版不支持支付，正式版恢复此功能，可咨询正式版本。', showCancel: false });
    return;
  }
  /* 演示版本 end */
  if (!form.value.price) {
    uni.showToast({ title: '请输入充值金额', icon: 'none' });
    return;
  }
  uni.showModal({
    content: '确认支付？',
    success(res) {
      if (res.confirm) {
        uni.showLoading();
        let params = { price: form.value.price };
        let pw = paymentWay.value;
        // 是否微信打开
        if (pw == 'weixinpay') {
          if (util.isWx()) {
            pw = 'weixinpay_jsapi_wxmp';
          } else {
            pw = 'weixinpay_h5';
          }
        }
        params.payment_way = pw;
        http.post('/payment/pay_wallet', params).then(res => {
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
.aduty-form {
	margin-top: 30rpx;
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

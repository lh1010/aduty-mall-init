<template>
  <view>
    <CustomTop top_title="钱包充值"></CustomTop>
    <view class="container">
			<view class="aduty-form">
				<view class="aduty-form-box">
					<view class="stitle"><span class="txt">充值金额</span></view>
					<view class="aduty-form-cell">
            <view class="aduty-form-cell-box">
              <view class="aduty-form-cell-bd">
                <input name="price" class="aduty-form-input" type="text" placeholder="请输入充值金额" @input="changeInput_price" />
              </view>
            </view>
					</view>
				</view>
			</view>

      <view class="payment">
      	<view class="stitle">支付方式</view>
      	<view class="items">
      		<view class="item" :class="payment_way == 'weixinpay' ? 'on' : ''" @click="setPaymentWay('weixinpay')">
      			<view class="left">
      				<image class="icon" src="@/static/images/icon_weixinpay.png" />
      				<span class="txt">微信支付</span>
      			</view>
      			<i class="iconfont icon-yuanxingweixuanzhong"></i>
      		</view>
          <view class="item" :class="payment_way == 'alipay_wap' ? 'on' : ''" @click="setPaymentWay('alipay_wap')">
      			<view class="left">
      				<image class="icon" src="@/static/images/icon_alipay.png" />
      				<span class="txt">支付宝支付</span>
      			</view>
      			<i class="iconfont icon-yuanxingweixuanzhong"></i>
      		</view>
      	</view>
      </view>
      <view class="payment_foot" v-if="systemInfo.platform != 'ios' || config.wxapp.ios_pay_status == 1">
        <button class="aduty-btn aduty-btn-payment" @click="payFun">立即支付</button>
      </view>
			<view
        :style="{ marginTop: '20px', marginBottom: '20px', textAlign: 'center' }"
        v-if="systemInfo.platform == 'ios' && config.wxapp.ios_pay_status == 0"
      >
        功能暂时不能使用
      </view>
    </view>
  </view>
</template>

<script src="http://res.wx.qq.com/open/js/jweixin-1.6.0.js"></script>
<script>
import { request } from "@/utils/http.js"
import util from "@/utils/util.js"
import CustomTop from "@/components/CustomTop.vue"
import config from "@/utils/config.js"

export default {
  components: { CustomTop },

  data() {
    return {
      price: {},
      payment_way: 'weixinpay',
      config: {},
      systemInfo: {}
    }
  },

  onLoad: function () {
    let that = this;
    uni.getSystemInfo({
      success (res) {
        that.systemInfo = res;
      }
    })
    that.systemInfo.platform = 'h5';

    request.post('/common/getConfig').then(res => {
      this.config = res.data;
    })
  },

  methods: {
    payFun: function() {
			/* 演示版本 start */
			if (config.app_env == 'dev') {
			  uni.showModal({ content: '演示版不支持支付，正式版恢复此功能，可咨询正式版本。', showCancel: false, });
			  return false;
			}
			/* 演示版本 end */
      let that = this;
      uni.showModal({
        title: '提示',
        content: '确认支付？',
        success (res) {
          if (res.confirm) {
            uni.showLoading();
						let params = { price: that.price };
            let payment_way = that.payment_way;
            // 微信内打开
            if (payment_way == 'weixinpay') {
              if (util.is_wx()) {
                payment_way = 'weixinpay_jsapi_wxmp';
              } else {
                payment_way = 'weixinpay_h5';
              }
            }
            params.payment_way = payment_way;
            request.post('/payment/pay_wallet', params).then((res) => {
              uni.hideLoading();
              if (res.code == 400) {
                uni.showToast({ title: res.message, icon: 'none' });
                return false;
              }
              if (payment_way == 'alipay_wap') {
                document.querySelector('body').innerHTML = res.data;
                document.forms[0].submit();
              }
              // app内使用 alipay_jsapi APP支付
              // if (payment_way == 'alipay_jsapi') {
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
              if (payment_way == 'weixinpay_h5') {
                window.location.href = res.data.url;
              }
              // app内使用微信h5支付
              // if (payment_way == 'weixinpay_h5') {
              //   const webview = plus.webview.create('', that.config.app_url);
              //   webview.loadURL(res.data.url, {'Referer': that.config.app_url});
              //   return false;
              // }
              if (payment_way == 'weixinpay_jsapi_wxmp') {
                that.onBridgeReady(res.data.jsApiParams);
              }
              if (payment_way == 'weixinpay_jsapi_wxapp') {
                that.onBridgeReady(res.data.jsApiParams);
              }
              if (payment_way == 'wallet') {
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
      })
    },

    // h5
    onBridgeReady: function(jsApiParams) {
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
        }
      });
    },

    // wxapp
    // onBridgeReady: function(jsApiParams) {
    //   uni.requestPayment({
    //     provider: 'wxpay',
    //     timeStamp: jsApiParams.timeStamp,
    //     nonceStr: jsApiParams.nonceStr,
    //     package: jsApiParams.package,
    //     signType: jsApiParams.signType,
    //     paySign: jsApiParams.paySign,
    //   	success: function (res) {
    //   		uni.showToast({
    //   		  title: '充值成功',
    //   		  icon: 'success',
    //   		  duration: 1500,
    //   		  success: function () {
    //   		    setTimeout(function() {
    //   		      uni.navigateBack({ delta: 1 });
    //   		    }, 1500);
    //   		  }
    //   		});
    //   	},
    //   	fail: function (err) {
    //       if (err.errMsg == 'requestPayment:fail cancel') {
    //         uni.showToast({ title: '取消支付', icon: 'none' });
    //       } else {
    //         uni.showToast({ title: '支付失败', icon: 'error' });
    //       }
    //   		console.log('fail:' + JSON.stringify(err));
    //   	}
    //   });
    // },

		setPaymentWay: function(payment_way) {
		  this.payment_way = payment_way;
		},

		changeInput_price: function(e) {
		  this.price = e.target.value;
		},

    jumpPage: function(url) {
      uni.navigateTo({
        url: url,
      })
    }
  }
}
</script>

<style>
.aduty-form-box {
	margin-top: 30rpx;
}
.payment {
  background-color: #fff;
  padding: 30rpx 30rpx 0 30rpx;
  border-radius: 3px;
  margin-top: 30rpx;
}
.payment .stitle {
	padding: 0 0 30rpx 0;
	border-bottom: 1px solid #f5f5f5;
}
.payment_foot {
  margin-top: 50rpx;
}
</style>

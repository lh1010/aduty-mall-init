<template>
  <view>
    <CustomTop top_title="充值金币"></CustomTop>
    <view class="container" v-if="!loading">
    	<view class="top_up_box">
    		<view class="item" :class="gold == item.gold ? 'on' : ''" v-for="(item, index) in gold_prices" :key="index" @click="setGoldPayData(item.gold, item.price);">
    			<view class="item_box">
    				<view class="gold">{{item.gold}}金币</view>
    				<view class="money">¥{{item.price}}</view>
    			</view>
    		</view>
    	</view>

    	<view class="payment">
        <view class="items">
          <view class="item" :class="payment_way == 'weixinpay' ? 'on' : ''" @click="setPaymentWay('weixinpay')">
            <view class="left">
              <image class="icon" src="/static/images/icon_weixinpay.png" />
              <span class="txt">微信支付</span>
            </view>
            <i class="iconfont icon-yuanxingweixuanzhong"></i>
          </view>
          <view class="item" :class="payment_way == 'alipay_wap' ? 'on' : ''" @click="setPaymentWay('alipay_wap')">
            <view class="left">
              <image class="icon" src="/static/images/icon_alipay.png" />
              <span class="txt">支付宝支付</span>
            </view>
            <i class="iconfont icon-yuanxingweixuanzhong"></i>
          </view>
          <view class="item" :class="payment_way == 'wallet' ? 'on' : ''" @click="setPaymentWay('wallet')">
            <view class="left">
              <image class="icon" src="/static/images/icon_wallet.png" />
              <span class="txt">余额支付</span>
            </view>
            <i class="iconfont icon-yuanxingweixuanzhong"></i>
          </view>
        </view>
      </view>

      <view class="payment_foot" v-if="systemInfo.platform != 'ios' || config.wxapp.ios_pay_status == 1">
        <view class="description">应付金额：<span class="aduty-text-price">{{price}}元</span></view>
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
      loading: true,
      gold_prices: [],
      gold: 0,
      price: 0,
      payment_way: 'weixinpay',
      config: {
        wxapp: {}
      },
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

    uni.showLoading();
    request.post('/common/getConfig').then(res => {
      uni.hideLoading();
      this.loading = false;
      this.config = res.data;
      this.gold_prices = res.data.gold_prices;
      this.gold = res.data.gold_prices[0].gold;
      this.price = res.data.gold_prices[0].price;
    })
  },

  methods: {
    setGoldPayData: function(gold, price) {
      this.gold = gold;
      this.price = price;
    },

    setPaymentWay: function(payment_way) {
      this.payment_way = payment_way;
    },

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
    				let params = { gold: that.gold };
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
            request.post('/payment/pay_gold', params).then((res) => {
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

    jumpPage: function(url) {
      uni.navigateTo({ url: url });
    }
  }
}
</script>

<style>
page {
  padding-bottom: 60rpx;
}
.top_up_box {
	overflow: hidden;
	margin-top: 30rpx;
}
.top_up_box .items {
  margin-bottom: -20rpx;
}
.top_up_box .item {
	width: 325rpx;
	height: 220rpx;
	border: 1px solid #eee;
	border-radius: 5px;
	float: left;
	margin-right: 20rpx;
	background-color: #fff;
	text-align: center;
	margin-bottom: 20rpx;
}
.top_up_box .item.on {
	border: 1px solid #d8b66c;
}
.top_up_box .item .item_box {
	padding-top: 70rpx;
}
.top_up_box .item:nth-child(2n) {
	margin-right: 0;
}
.top_up_box .item .gold {
	font-size: 18px;
}
.top_up_box .item .money {
	font-size: 14px;
	color: #ff0000;
  margin-top: 5px;
}

.payment {
  background-color: #fff;
  padding: 0 30rpx;
  border-radius: 3px;
  margin-top: 10rpx;
}
.payment .item:last-child {
  border-bottom: none;
  margin-bottom: 0;
}
</style>

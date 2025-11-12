<template>
	<view class="page">
		<CustomTop top_title="收银台"></CustomTop>
		<view class="paypage" v-if="!loading && payment_status == ''">
			<view class="pricebox">
				<span class="price">¥{{totalData.total_price}}</span>
			</view>
      <view class="pay_orders">
        <view class="stitle">订单信息</view>
        <view class="items">
          <view class="item" v-for="(item, index) in orders" :key="index" @click="jumpPage('/pages/order/show?id=' + item.id)">
            <span class="sp1">订单号：{{item.number}}</span>
            <span class="spa">查看详情</span>
          </view>
        </view>
      </view>
			<view class="payment">
				<view class="stitle">支付方式</view>
				<view class="items">
					<view class="item" :class="payment_way == 'weixinpay' ? 'on' : ''" @click="setPaymentWay('weixinpay')">
						<view class="left">
							<image class="icon" src="@/static/images/icon_weixinpay.png">
							<span class="txt">微信支付</span>
						</view>
						<i class="iconfont icon-yuanxingweixuanzhong"></i>
					</view>
					<view class="item" :class="payment_way == 'alipay_wap' ? 'on' : ''" @click="setPaymentWay('alipay_wap')">
						<view class="left">
							<image class="icon" src="@/static/images/icon_alipay.png">
							<span class="txt">支付宝支付</span>
						</view>
						<i class="iconfont icon-yuanxingweixuanzhong"></i>
					</view>
					<view class="item" :class="payment_way == 'wallet' ? 'on' : ''" @click="setPaymentWay('wallet')">
						<view class="left">
							<image class="icon" src="@/static/images/icon_wallet.png">
							<span class="txt">余额支付</span>
						</view>
						<i class="iconfont icon-yuanxingweixuanzhong"></i>
					</view>
				</view>
			</view>

			<view class="foot_action">
				<view class="btn" @click="payFun">立即支付</view>
			</view>
		</view>

		<view class="result" v-if="payment_status == 'success'">
			<view class="txt"><i class="iconfont icon-yuanxingxuanzhong"></i>支付成功</view>
			<view class="btns">
				<button class="aduty-btn aduty-btn-default" @click="jumpPage('/pages/order/list')">查看订单</button>
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
			order_ids: '',
			orders: {},
      totalData: {},
			payment_way: 'weixinpay',
			payment_status: '',
		}
	},

	onLoad(options) {
		this.order_ids = options.order_ids;
	},

  onShow() {
		this.getOrderPayData();
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
		    content: '确认支付？',
		    success (res) {
		      if (res.confirm) {
		        uni.showLoading();
						let params = { order_ids: that.order_ids };
		        // 微信内打开
		        let payment_way = that.payment_way;
		        if (payment_way == 'weixinpay') {
		        	if (util.is_wx()) {
		        		payment_way = 'weixinpay_jsapi_wxmp';
		        	} else {
		        		payment_way = 'weixinpay_h5';
		        	}
		        }
		        params.payment_way = payment_way;
		        request.post('/payment/pay_order', params).then((res) => {
		          uni.hideLoading();
		          if (res.code == 400) {
								uni.showToast({ title: res.message, icon: 'none' });
		            return false;
		          }
							// 普通浏览器使用 alipay_wap 手机网站支付
              if (payment_way == 'alipay_wap') {
                document.querySelector('body').innerHTML = res.data;
                document.forms[0].submit();
              }
              // app内使用 alipay_wap 手机网站支付
              if (payment_way == 'alipay_wap') {
              	uni.navigateTo({ url: '/pages/index/out?url=' + res.data.url });
              }
              // app内使用 alipay_jsapi APP支付
              // if (payment_way == 'alipay_jsapi') {
              //   uni.requestPayment({
              //     provider: 'alipay',
              //     orderInfo: res.data,
              //     success: function (res) {
              //       console.log('success:' + JSON.stringify(res));
              //       that.payment_status = 'success';
              //     },
              //     fail: function (err) {
              //         console.log('fail:' + JSON.stringify(err));
              //     }
              //   });
              // }
              // 普通浏览器使用 weixinpay_h5 H5支付
              if (payment_way == 'weixinpay_h5') {
                window.location.href = res.data.url;
              }
              // app内使用 weixinpay_h5 H5支付
              // if (payment_way == 'weixinpay_h5') {
              // 	const webview = plus.webview.create('', that.config.app_url);
              // 	webview.loadURL(res.data.url, {'Referer': that.config.app_url});
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
										that.payment_status = 'success';
									}
								});
							}
		        });
		      }
		    }
		  })
		},

    // weixinpay jsapi h5
    // onBridgeReady: function(jsApiParams) {
    // 	WeixinJSBridge.invoke(
    // 	'getBrandWCPayRequest', {
    // 		appId: jsApiParams.appId,
    // 		timeStamp: jsApiParams.timeStamp,
    // 		nonceStr: jsApiParams.nonceStr,
    // 		package: jsApiParams.package,
    // 		signType: jsApiParams.signType,
    // 		paySign: jsApiParams.paySign
    // 	},
    // 	function (res) {
    // 		if (res.err_msg == "get_brand_wcpay_request:ok") {
    // 			this.payment_status = 'success';
    // 			uni.showModal({ title: '提示', content: '支付成功', showCancel: false });
    // 		}
    // 	});
    // },

    // weixinpay jsapi wxapp
    onBridgeReady: function(jsApiParams) {
      uni.requestPayment({
        provider: 'wxpay',
        timeStamp: jsApiParams.timeStamp,
        nonceStr: jsApiParams.nonceStr,
        package: jsApiParams.package,
        signType: jsApiParams.signType,
        paySign: jsApiParams.paySign,
        success: function (res) {
          uni.showToast({
            title: '充值成功',
            icon: 'success',
            duration: 1500,
            success: function () {
              setTimeout(function() {
                this.payment_status = 'success';
              }, 1500);
            }
          });
        },
        fail: function (err) {
          if (err.errMsg == 'requestPayment:fail cancel') {
            uni.showToast({ title: '取消支付', icon: 'none' });
          } else {
            uni.showToast({ title: '支付失败', icon: 'error' });
          }
          console.log('fail:' + JSON.stringify(err));
        }
      });
    },

		getOrderPayData: function() {
			uni.showLoading();
			request.post('/order/getOrderPayData', {order_ids: this.order_ids}).then(res => {
				this.loading = false;
				uni.hideLoading();
				this.orders = res.data.orders;
        this.totalData = res.data.totalData;
			})
		},

		setPaymentWay: function(payment_way) {
			this.payment_way = payment_way;
		},

		switchTab: function(url) {
			uni.switchTab({ url: url });
		},

		jumpPage: function(url) {
			uni.navigateTo({ url: url })
		}
	}
}
</script>

<style>
@import url("pay.css");
.page {
	padding-bottom: 30rpx;
}
</style>

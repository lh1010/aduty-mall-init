<style lang="scss">
@import "@/uni_modules/uview-ui/index.scss";
</style>

<script>
	export default {
		onLaunch: function() {
			console.log('App Launch')
		},
		onShow: function() {
			console.log('App Show')
		},
		onHide: function() {
			console.log('App Hide')
		}
	}
</script>

<style>
@import url("@/static/iconfont/iconfont.css");
@import url("@/static/weui.min.css");
@import url("@/static/aduty.css");

page {
	background-color: #f6f7fb;
  margin: 0 auto;
}
uni-modal {
  z-index: 999999 !important;
}
.container {
  width: 700rpx;
  margin: 0 auto;
}
.none {
	display: none !important;
}
.uloadmore {
	margin: 30rpx 0;
}

/* payment 支付 start */
.payment {}
.payment .item {
	width: 100%;
	height: 120rpx;
	line-height: 120rpx;
	overflow: hidden;
	border-bottom: 1px solid #f5f5f5;
  font-size: 28rpx;
}
.payment .item .left {
	float: left;
}
.payment .item .icon {
	width: 54rpx;
  height: 54rpx;
  vertical-align: middle;
}
.payment .item .txt {
	margin-left: 5px;
}
.payment .item i {
	float: right;
}
.payment .item i:before {}
.payment .item.on i:before {
	color: #f4645f;
  content: '\e731';
}
.payment_foot {
  margin-top: 30rpx;
}
.payment_foot .description {
	color: #666;
	font-size: .875rem;
	margin-bottom: 30rpx;
}
.payment_foot .description .price_color {
	font-size: 40rpx;
  margin-right: 3px;
}
.payment_foot .pay_qrcode .msg {
	width: 150px;
	text-align: center;
	font-size: .875rem;
	letter-spacing: 3px;
}
.payment_foot .pay_qrcode .image {
	border: 1px solid #eee;
	width: 150px;
	height: 150px;
	line-height: 150px;
	text-align: center;
	margin-bottom: 5px;
	overflow: hidden;
}
.payment_foot .pay_qrcode .image img {
	max-width: 100%;
	max-height: 100%;
}
/* payment 支付 end */

/* popup_payment 底部弹出支付 start */
.popup_payment {
  background-color: #ffffff;
  padding-bottom: 40rpx;
  border-top-left-radius: 8px;
  border-top-right-radius: 8px;
}
.popup_payment_container {
  width: 700rpx;
  margin: 0 auto;
}
.popup_payment_top {
  width: 100%;
  text-align: center;
  height: 90rpx;
  line-height: 90rpx;
  border-bottom: 1px solid #f5f5f5;
  overflow: hidden;
}
.popup_payment_top .close {
	position: absolute;
  top: 0;
	right: 40rpx;
	height: 90rpx;
	line-height: 90rpx !important;
	color: #999;
}
.popup_payment .items .item {
	width: 100%;
	height: 120rpx;
	line-height: 120rpx;
	overflow: hidden;
	border-bottom: 1px solid #f5f5f5;
  font-size: 28rpx;
}
.popup_payment .items .item .left {
	float: left;
}
.popup_payment .items .item .icon {
	width: 54rpx;
  height: 54rpx;
  vertical-align: middle;
}
.popup_payment .items .item .txt {
	margin-left: 5px;
}
.popup_payment .items .item i {
	float: right;
}
.popup_payment .items .item.on i:before {
	color: #f4645f;
  content: '\e731';
}
.popup_payment_foot {
  margin-top: 30rpx;
}
.popup_payment_foot .description {
	color: #666;
	font-size: .875rem;
	margin-bottom: 30rpx;
}
.popup_payment_foot .description .text-price {
	font-size: 20px;
  margin-right: 3px;
	color: #ff0000;
}
/* popup_payment 底部弹出支付 end */

/* 自定义头部 start */
.customtop_blank {
  height: 54px;
  padding-top: var(--status-bar-height);
}
.customtop {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  margin: auto;
  z-index: 99;
  height: 53px;
  line-height: 53px;
  border-bottom: 1px solid #f5f5f5;
  background: #fff;
  padding: 0 30rpx;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-top: var(--status-bar-height);
}
.customtop .item {
  flex: 1;
}
.customtop .left .iconfont {
  font-weight: 600;
	margin-left: 10px;
}
.customtop .left .iconfont:first-child {
  margin-left: 0;
}
.customtop .title {
  text-align: center;
  white-space: nowrap;
  overflow: hidden;
  text-overflow:ellipsis;
}
.customtop .right {
  text-align: right;
	font-size: 12px;
	color: #999;
}
/* 自定义头部 end */
</style>

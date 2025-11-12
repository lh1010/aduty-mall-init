<template>
	<view class="page">
		<CustomTop top_title="买入订单"></CustomTop>
		<view class="order_show" v-if="!loading">
			<view class="">
				<view class="ptop">
					<view class="status">{{order.status_str}}</view>
				</view>

				<view class="pagebox snaps">
					<view class="btop">共1件商品</view>
					<view class="snaps_item" v-for="(item, index) in order.snaps" :key="index">
						<image class="cover" :src="item.cover" mode="aspectFit" />
						<view class="info">
							<view class="name">{{item.name}}</view>
              <view class="spes" v-if="item.specifications.length > 0">
                <view class="spes_item" v-for="(item_specification, index_specification) in item.specifications" :key="index_specification">
                  {{item_specification.specification_name}}-{{item_specification.specification_option}}
                </view>
              </view>
							<view class="pricebox">
								<span class="price">¥{{item.price}}</span>
								<span class="number">x{{item.count}}</span>
							</view>
						</view>
					</view>
				</view>

				<view class="pagebox">
					<view class="bd">
						<view class="item">
							<span class="span1">订单编号：</span><span class="span2">{{order.number}}</span>
						</view>
            <view class="item">
            	<span class="span1">创建时间：</span><span class="span2">{{order.created_at}}</span>
            </view>
						<view class="item">
							<span class="span1">合计金额：</span><span class="span2 aduty-text-price">¥{{order.total_price}}</span>
						</view>
					</view>
				</view>

        <view class="pagebox snaps">
        	<view class="btop">订单流程进度</view>
        	<view class="items">
        		<view class="item" v-for="(item, index) in order.logs" :key="index">{{item.created_at}} {{item.content}}</view>
            <view class="item" v-if="![-10, 30].includes(order.status)">......</view>
        	</view>
        </view>

				<view v-if="[0, 20].includes(order.status)">
					<view class="foot_action_blank"></view>
					<view class="foot_action">
						<view class="btns">
							<view class="btn" @click="receiveOrder(order.id)" v-if="order.status == 20">确认收货</view>
							<view class="btn" @click="cancelOrder(order.id)" v-if="order.status == 0">取消订单</view>
							<view class="btn btn_pay" @click="jumpPage('/pages/checkout/pay?order_ids=' + order.id)" v-if="order.status == 0">立即支付</view>
						</view>
					</view>
				</view>
			</view>
		</view>
	</view>
</template>

<script>
import { request } from "@/utils/http.js"
import CustomTop from "@/components/CustomTop.vue"
export default {
	components: { CustomTop },

	data() {
		return {
			loading: true,
			order: {},
			tonggao: {},
			contact_popup_show: false,
			contact: {},
		}
	},

	onLoad(options) {
		this.id = options.id;
		this.getShow();
	},

	methods: {
		getShow: function() {
			uni.showLoading();
			request.post('/order/getShow', {id: this.id}).then(res => {
				uni.hideLoading();
				this.loading = false;
				this.order = res.data;
			})
		},

		// 取消订单
		cancelOrder: function(order_id) {
			let that = this;
			uni.showModal({
				content: '确定取消？',
				success (res) {
					if (res.confirm) {
						uni.showLoading();
						request.post('/order/cancelOrder', {order_id: order_id}).then((res) => {
							if (res.code == 200) {
								that.getShow();
								uni.showToast({ icon: 'none', title: '操作成功' });
							} else if (res.code == 400) {
								uni.showToast({ title: res.message, icon: 'none' });
							}
						});
					}
				}
			})
		},

		receiveOrder: function(order_id) {
			let that = this;
			uni.showModal({
				content: '确定操作？',
				success (res) {
					if (res.confirm) {
						uni.showLoading();
						request.post('/order/receiveOrder', {order_id: order_id}).then((res) => {
							if (res.code == 200) {
								that.getShow();
								uni.showToast({ icon: 'none', title: '操作成功' });
							} else if (res.code == 400) {
								uni.showToast({ title: res.message, icon: 'none' });
							}
						});
					}
				}
			})
		},

		getContact: function(id) {
			uni.showLoading();
			request.post('/shop/getContact', {shop_id: id}).then((res) => {
				uni.hideLoading();
				this.contact = res.data;
				this.contact_popup_show = true;
			})
		},

		onClose_contact_popup: function() {
		  this.contact_popup_show = false;
		},

		copy: function(content) {
		  uni.setClipboardData({ data: content });
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
@import url("order.css");
.page {
	padding-bottom: 30rpx;
}
</style>

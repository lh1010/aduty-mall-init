<template>
	<view class="page">
		<CustomTop top_title="确认订单"></CustomTop>
		<view class="checkout" v-if="!loading">
      <view class="address">
				<view class="bd" @click="onPopupShow_setAddress" v-if="checkoutData.address">
					<view class="info">
						<view class="vm1">
							{{checkoutData.address.province_name}} {{checkoutData.address.city_name}} {{checkoutData.address.district_name}} {{checkoutData.address.detailed_address}}
						</view>
						<view class="vm2">
							{{checkoutData.address.name}} {{checkoutData.address.phone}}
						</view>
					</view>
					<i class="iconfont icon-youbian more"></i>
				</view>
				<view class="bd" v-if="!checkoutData.address" @click="jumpPage('/pages/address/list')">
					<view class="info">设置收货地址</view>
					<i class="iconfont icon-youbian more"></i>
				</view>
			</view>

			<view class="data_list" v-if="checkoutData.products.length > 0">
				<view class="items">
					<view class="item_product" v-for="(item_product, index_product) in checkoutData.products" :key="index_product">
						<image class="cover" :src="item_product.cover" mode="scaleToFill" @click="jumpPage('/pages/product/show?id=' + item_product.id + '&sku=' + item_product.sku)" />
						<view class="info" @click="jumpPage('/pages/product/show?id=' + item_product.id + '&sku=' + item_product.sku)">
							<view class="name">
								<span class="txt">{{item_product.name}}</span>
							</view>
              <view class="types" v-if="item_product.specifications.length > 0">
                <view class="types_item" v-for="(item_specification, index_specification) in item_product.specifications" :key="index_specification">
                  {{item_specification.specification_name}}-{{item_specification.specification_option}}
                </view>
              </view>
							<view class="pricebox">
								<span class="price">¥{{item_product.price}}</span>
								<span class="number">x{{item_product.count}}</span>
							</view>
						</view>
					</view>
				</view>
			</view>

			<view class="fee">
				<view class="items">
					<view class="item">
						<view class="vm1">商品总额</view>
						<view class="vm2">¥{{checkoutData.totalData.product_total_price}}</view>
					</view>
				</view>
			</view>

			<view class="foot_action_blank"></view>
			<view class="foot_action">
				<view class="left">
					合计：<span class="price">¥{{checkoutData.totalData.total_price}}</span>
				</view>
				<view class="right">
					<view class="btn" @click="createOrder">提交订单</view>
				</view>
			</view>
		</view>

    <u-popup
		  :show="popupShow_setAddress"
			@close="onPopupClose_setAddress"
		  mode="bottom"
		>
			<view class="popup_setAddress">
				<view class="btop">
					<span class="stitle">选择地址</span>
					<i class="iconfont iconguanbicopy close" @click="onPopupClose_set_address"></i>
				</view>
				<view class="items">
					<view class="item" v-for="(item, index) in addresses" :key="index" @click="setAddress(item.id);">
						<view class="info">
							<view class="vm1">
								{{item.province_name}} {{item.city_name}} {{item.district_name}} {{item.detailed_address}}
							</view>
							<view class="vm2">
								{{item.name}} {{item.phone}}
							</view>
						</view>
						<i class="iconfont icon-yuanxingxuanzhong" v-if="checkoutData.address.id == item.id"></i>
					</view>
				</view>
			</view>
		</u-popup>
	</view>
</template>

<script>
import { request } from "@/utils/http.js"
import util from "@/utils/util.js"
import CustomTop from "@/components/CustomTop.vue"
export default {
	components: { CustomTop },

	data() {
		return {
			loading: true,
			params : {
        type: 'onekeybuy',
        count: 1,
        sku: '',
      },
			checkoutData: [],
      addresses: [],
			popupShow_setAddress: false,
		}
	},

	onLoad(options) {
    this.params.count = options.count || 1;
    this.params.sku = options.sku || '';
	},

  onShow() {
		this.getCheckoutData();
    this.getAddresses();
  },

	methods: {
		createOrder: function() {
			uni.showLoading();
			let params = this.params;
			request.post('/order/createOrder', params).then(res => {
				uni.hideLoading();
				if (res.code == 200) {
					uni.navigateTo({ url: '/pages/checkout/pay?order_ids=' + res.data.order_ids })
				} else if (res.code == 400) {
					uni.showToast({ title: res.message, icon: 'none' });
					return false;
				}
			})
		},

		getCheckoutData: function() {
			uni.showLoading();
			let params = this.params;
			request.post('/order/getCheckoutData', params).then(res => {
				uni.hideLoading();
				if (res.code == 200) {
					this.loading = false;
					this.checkoutData = res.data;
				} else if (res.code == 400) {
					uni.showModal({
						content: res.message,
						showCancel: false,
						success: function (res) {
							if (res.confirm) {
								uni.navigateBack();
							}
						}
					});
					return false;
				}
			})
		},

    getAddresses: function() {
		  let params = this.params;
		  request.post('/address/getAddresses', params).then(res => {
				this.addresses = res.data;
		  })
		},

		onPopupShow_setAddress: function() {
			this.popupShow_setAddress = true;
		},

		onPopupClose_setAddress: function() {
			this.popupShow_setAddress = false;
		},

		setAddress: function(address_id) {
			this.params.address_id = address_id;
			this.onPopupClose_setAddress();
			this.getCheckoutData();
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
@import url("checkout.css");
.page {
	padding-bottom: 30rpx;
}
</style>

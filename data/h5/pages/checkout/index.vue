<template>
	<view class="page">
		<CustomTop topTitle="确认订单"></CustomTop>
		<view class="checkout" v-if="!loading">
      <view class="address">
				<view class="bd" @click="addressPopupRef.open()" v-if="checkoutData.address && checkoutData.address.id">
					<view class="info">
						<view class="vm1">
							{{ checkoutData.address.province_name }} {{ checkoutData.address.city_name }} {{ checkoutData.address.district_name }} {{ checkoutData.address.detailed_address }}
						</view>
						<view class="vm2">
							{{ checkoutData.address.name }} {{ checkoutData.address.phone }}
						</view>
					</view>
					<i class="iconfont icon-youbian more"></i>
				</view>
				<view class="bd" v-if="!checkoutData.address || !checkoutData.address.id" @click="uni.navigateTo({url: '/pages/address/list'})">
					<view class="info">设置收货地址</view>
					<i class="iconfont icon-youbian more"></i>
				</view>
			</view>

			<view class="data_list" v-if="checkoutData.products.length > 0">
				<view class="items">
					<view class="item_product" v-for="(item_product, index_product) in checkoutData.products" :key="index_product">
						<image class="cover" :src="item_product.cover" mode="scaleToFill" @click="uni.navigateTo({url: '/pages/product/show?id=' + item_product.id + '&sku=' + item_product.sku})" />
						<view class="info" @click="uni.navigateTo({url: '/pages/product/show?id=' + item_product.id + '&sku=' + item_product.sku})">
							<view class="name">
								<span class="txt">{{ item_product.name }}</span>
							</view>
              <view class="types" v-if="item_product.specifications.length > 0">
                <view class="types_item" v-for="(item_specification, index_specification) in item_product.specifications" :key="index_specification">
                  {{ item_specification.specification_name }}-{{ item_specification.specification_option }}
                </view>
              </view>
							<view class="pricebox">
								<span class="price">¥{{ item_product.price }}</span>
								<span class="number">x{{ item_product.count }}</span>
							</view>
						</view>
					</view>
				</view>
			</view>

			<view class="fee">
				<view class="items">
					<view class="item">
						<view class="vm1">商品总额</view>
						<view class="vm2">¥{{ checkoutData.totalData.product_total_price }}</view>
					</view>
				</view>
			</view>

			<view class="foot_action_blank"></view>
			<view class="foot_action">
				<view class="foot_action_container">
					<view class="left">
						合计：<span class="price">¥{{ checkoutData.totalData.total_price }}</span>
					</view>
					<view class="right">
						<view class="btn" @click="createOrder">提交订单</view>
					</view>
				</view>
			</view>
		</view>

    <uni-popup ref="addressPopupRef" type="bottom">
			<view class="popup_setAddress">
				<view class="btop">
					<span class="stitle">选择地址</span>
					<i class="iconfont icon-quxiao close" @click="addressPopupRef.close()"></i>
				</view>
				<view class="items">
					<view class="item" v-for="(item, index) in addresses" :key="index" @click="setAddress(item.id)">
						<view class="info">
							<view class="vm1">
								{{ item.province_name }} {{ item.city_name }} {{ item.district_name }} {{ item.detailed_address }}
							</view>
							<view class="vm2">
								{{ item.name }} {{ item.phone }}
							</view>
						</view>
						<i class="iconfont icon-yuanxingxuanzhong" v-if="checkoutData.address.id == item.id"></i>
					</view>
				</view>
				<view class="bfoot">
					<view class="btn" @click="uni.navigateTo({url: '/pages/address/my_list'})">设置新地址</view>
				</view>
			</view>
		</uni-popup>
	</view>
</template>

<script setup>
import { ref } from 'vue';
import { onLoad, onShow } from '@dcloudio/uni-app';
import http from "@/utils/http.js";
import CustomTop from "@/components/CustomTop.vue";

const loading = ref(true);
const params = ref({
  type: 'cart',
});
const checkoutData = ref({ products: [], address: {}, totalData: {} });
const addresses = ref([]);
const addressPopupRef = ref(null);

onLoad((options) => {
  uni.setNavigationBarTitle({ title: '确认订单' });
  params.value.type = options.type || 'cart';
  if (params.value.type == 'onekeybuy') {
    params.value.count = options.count || 1;
    params.value.sku = options.sku || '';
  }
});

onShow(() => {
  getCheckoutData();
  getAddresses();
});

const createOrder = () => {
  uni.showLoading();
  let orderParams = { ...params.value, address_id: checkoutData.value.address?.id };
  http.post('/order/createOrder', orderParams).then(res => {
    uni.hideLoading();
    if (res.code == 200) {
      uni.navigateTo({ url: '/pages/checkout/pay?order_ids=' + res.data.order_ids });
    } else if (res.code == 400) {
      uni.showToast({ title: res.message, icon: 'none' });
    }
  });
};

const getCheckoutData = () => {
  uni.showLoading();
  http.post('/order/getCheckoutData', params.value).then(res => {
    uni.hideLoading();
    if (res.code == 200) {
      loading.value = false;
      checkoutData.value = res.data;
    } else if (res.code == 400) {
      uni.showModal({
        content: res.message,
        showCancel: false,
        success(res) {
          if (res.confirm) {
            uni.navigateBack();
          }
        }
      });
    }
  });
};

const getAddresses = () => {
  http.post('/address/getList').then(res => {
    addresses.value = res.data;
  });
};

const setAddress = (addressId) => {
  params.value.address_id = addressId;
  checkoutData.value.address = addresses.value.find(item => item.id == addressId);
  addressPopupRef.value.close();
  getCheckoutData();
};
</script>

<style>
@import url("checkout.css");
</style>

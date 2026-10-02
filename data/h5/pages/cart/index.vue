<template>
  <view class="account_index">
    <view class="customtop">
      <view class="item left"></view>
      <view class="item title">购物车</view>
      <view class="item right" @click="manageIdent = manageIdent ? 0 : 1">
				<template v-if="logined && products.length > 0">
					<span v-if="manageIdent == 0">编辑</span>
					<span v-if="manageIdent == 1">完成</span>
				</template>
			</view>
    </view>
    <view class="customtop_blank"></view>
    <view class="cart" v-if="!loading">
			<view class="empty_data" v-if="!logined">
				<image class="cart_img" src="/static/images/foot_icon_cart.png" mode="scaleToFill" />
				<view class="msg">登录后可同步购物车的产品～</view>
				<view class="btn" @click="uni.navigateTo({url: '/pages/account/login'})">请先登录</view>
			</view>
			<template v-if="logined && !dataLoading">
				<view class="empty_data" v-if="products.length == 0">
					<image class="cart_img" src="/static/images/foot_icon_cart.png" mode="scaleToFill" />
					<view class="msg">购物车空空如也～</view>
					<view class="btn" @click="uni.switchTab({url: '/pages/index/index'})">去逛逛</view>
				</view>
				<view class="data_list" v-if="products.length > 0">
					<view class="items">
						<view class="item_product" v-for="(item, index) in products" :key="index">
							<i class="iconfont icon-yuanxingweixuanzhong icon_selected" :class="item.selected == 1 ? 'on' : ''" @click="setCartSelected(item.sku, item.selected == 1 ? 0 : 1)"></i>
							<image class="cover" :src="item.cover" mode="scaleToFill" @click="uni.navigateTo({url: '/pages/product/show?id=' + item.id + '&sku=' + item.sku})" />
							<view class="info" @click="uni.navigateTo({url: '/pages/product/show?id=' + item.id + '&sku=' + item.sku})">
								<view class="name">
									<span class="txt">{{ item.name }}</span>
								</view>
                <view class="types" v-if="item.specifications.length > 0">
                  <view class="types_item" v-for="(item_specification, index_specification) in item.specifications" :key="index_specification">
                    {{ item_specification.specification_name }}-{{ item_specification.specification_option }}
                  </view>
                </view>
								<view class="pricebox">
									<span class="price">¥{{ item.price }}</span>
									<span class="number">x{{ item.count }}</span>
								</view>
							</view>
						</view>
					</view>
					<!-- foot action start -->
					<view class="foot_action_blank"></view>
					<view class="foot_action" v-if="manageIdent == 0">
						<view class="left">
							<view class="selectbox">
								<i class="iconfont icon-yuanxingweixuanzhong icon_selected" :class="checkAllSelected ? 'on' : ''" @click="toggleAll()"></i>
								<span class="txt">全选</span>
							</view>
						</view>
						<view class="right">
							<view class="pricebox"><span class="em">合计：</span><span class="price">¥{{ calculating ? '计算中' : totalData.total_price }}</span></view>
							<view class="btn" @click="uni.navigateTo({url: '/pages/checkout/index?type=cart'})">去结算</view>
						</view>
					</view>
					<view class="foot_action" v-if="manageIdent == 1">
						<view class="left">
							<view class="selectbox">
								<i class="iconfont icon-yuanxingweixuanzhong icon_selected" :class="checkAllSelected ? 'on' : ''" @click="toggleAll()"></i>
								<span class="txt">全选</span>
							</view>
						</view>
						<view class="right">
							<view class="btn btn-delete" @click="deleteCart">删除选中</view>
						</view>
					</view>
					<!-- foot action end -->
				</view>
			</template>
    </view>
  </view>
</template>

<script setup>
import { ref, computed } from 'vue';
import { onLoad, onShow, onPullDownRefresh } from '@dcloudio/uni-app';
import http from "@/utils/http.js";

const loading = ref(true);
const logined = ref(false);
const dataLoading = ref(true);
const products = ref([]);
const totalData = ref({total_price: null});
const manageIdent = ref(0);
const calculating = ref(false);

const checkAllSelected = computed(() => {
  return products.value.length > 0 && products.value.every(item => item.selected == 1);
});

onLoad(() => {
	uni.setNavigationBarTitle({ title: '购物车' });
  uni.showLoading();
});

onShow(() => {
  getLoginUser();
});

onPullDownRefresh(() => {
  getLoginUser();
});

const getLoginUser = () => {
  http.post('/account/getLoginUser').then(res => {
		loading.value = false;
    logined.value = !!res.data.id;
    if (logined.value) {
      getCartData();
    } else {
			uni.hideLoading();
			uni.stopPullDownRefresh();
		}
  });
};

const getCartData = () => {
  return http.post('/order/getCartData').then(res => {
    uni.stopPullDownRefresh();
    uni.hideLoading();
    dataLoading.value = false;
    products.value = res.data.products;
    totalData.value = res.data.totalData;
    calculating.value = false;
  });
};

const setCartSelected = (sku, selected) => {
  calculating.value = true;
  products.value.forEach(product => {
    if (product.sku == sku) {
      product.selected = selected;
    }
  });
  http.post('/product/setCartSelected', { sku, selected }).then(res => {
    totalData.value.total_price = res.data.total_price;
    calculating.value = false;
  });
};

const toggleAll = () => {
  calculating.value = true;
  const newSelected = checkAllSelected.value ? 0 : 1;
  products.value.forEach(product => {
    product.selected = newSelected;
  });
  let skus = products.value.map(item => item.sku);
  http.post('/product/setCartSelectedBatch', { skus, selected: newSelected }).then(res => {
    totalData.value.total_price = res.data.total_price;
    calculating.value = false;
  });
};

const deleteCart = () => {
  let selectedSkus = [];
  products.value.forEach((item) => {
    if (item.selected == 1) {
      selectedSkus.push(item.sku);
    }
  });
  uni.showModal({
    content: '确认删除选中的商品？',
    success(res) {
      if (res.confirm) {
        uni.showLoading();
        http.post('/product/deleteCart', { skus: selectedSkus }).then(res => {
          uni.hideLoading();
          getCartData();
        });
      }
    }
  });
};
</script>

<style>
@import url("cart.css");
page {
  padding-bottom: 30rpx;
}
</style>

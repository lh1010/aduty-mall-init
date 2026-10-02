<template>
	<view class="page">
		<CustomTop :topTitle="topTitle"></CustomTop>
    <view class="top_search">
      <view class="container">
        <view class="top_search_box">
          <view class="icon"><i class="iconfont icon-sousuoxiao"></i></view>
          <view class="input"><input class="weui-input" type="text" placeholder="请输入搜索内容" v-model="params.k" @confirm="doSearch" /></view>
        </view>
      </view>
    </view>
    <view class="top_search_blank"></view>
    <view class="wnav">
      <view class="sv_nav">
      	<view class="items box">
      		<view class="item svItem" :class="{ on: params.order == '' }" @click="setOrder('')">默认</view>
					<view class="item svItem" :class="{ on: params.order == '最新' }" @click="setOrder('最新')">最新</view>
					<view class="item svItem" :class="{ on: params.order == '销量' }" @click="setOrder('销量')">销量</view>
					<view class="item svItem" :class="['价格最低', '价格最高'].includes(params.order) ? 'on' : ''" @click="setOrderPrice()">
						价格
						<i class="iconfont icon-shangxiayidong" v-if="!['价格最低', '价格最高'].includes(params.order)"></i>
						<i class="iconfont icon-jiantou_xiangshang" v-if="params.order == '价格最低'"></i>
						<i class="iconfont icon-jiantou_xiangxia" v-if="params.order == '价格最高'"></i>
					</view>

      	</view>
      </view>
    </view>
		<view class="wnav_blank"></view>

		<view class="product_list" v-if="!dataListLoading">
			<view class="container">
				<view v-if="dataList.length > 0">
					<view class="items">
						<view class="item" @click="jumpPage('/pages/product/show?id=' + item.id + '&sku=' + item.sku)" v-for="item in dataList" :key="item.id">
							<image class="cover" :src="item.cover" mode="aspectFit" />
							<view class="info">
								<view class="name">
									<span class="txt">{{item.name}}</span>
								</view>
								<view class="price aduty-text-price">¥ {{item.price}}</view>
							</view>
						</view>
					</view>
					<view class="uloadmore"><uni-load-more :status="loadmoreStatus" /></view>
				</view>
				<view class="aduty-empty" v-if="dataList.length == 0">
					<image class="aduty-empty-img" src="/static/images/empty.png" mode="aspectFit"></image>
					<text class="aduty-empty-text">暂无内容</text>
				</view>
			</view>
		</view>
	</view>
</template>

<script setup>
import { ref } from 'vue';
import { onLoad, onReachBottom, onPullDownRefresh } from '@dcloudio/uni-app';
import CustomTop from "@/components/CustomTop.vue";
import http from "@/utils/http.js";
import util from "@/utils/util.js";

const topTitle = ref('商品');
const dataList = ref([]);
const dataListLoading = ref(true);
const loadmoreStatus = ref('more');
const loadmoreFinished = ref(false);
const params = ref({
	page_size: 15,
	page: 1,
	k: '',
	order: '',
	category_id: '',
});
const category = ref({});

onLoad((options) => {
	Object.assign(params.value, options);
	uni.showLoading();
	getInit();
	if (options.category_id != undefined) {
		http.post('/product/getCategory', { id: options.category_id }).then(res => {
			category.value = res.data;
			topTitle.value = res.data.name;
			uni.setNavigationBarTitle({ title: res.data.name });
		});
	}
});

// 触底加载
onReachBottom(() => {
	getMore();
});

// 下拉刷新
onPullDownRefresh(() => {
	getInit();
});

const getList = () => {
	http.post('/product/getList', params.value).then(res => {
		uni.stopPullDownRefresh();
		uni.hideLoading();
		dataListLoading.value = false;
		// 数据为空
		if (res.data.total == 0) {
			dataList.value = [];
			return;
		}
		// 组装数据
		dataList.value = res.data.current_page == 1 ? res.data.data : dataList.value.concat(res.data.data);
		// 最后一页
		if (params.value.page >= res.data.last_page) {
			loadmoreFinished.value = true;
			loadmoreStatus.value = 'noMore';
		} else {
			params.value.page = parseInt(res.data.current_page) + 1;
			loadmoreStatus.value = 'more';
			loadmoreFinished.value = false;
		}
	});
};

const getInit = () => {
	dataListLoading.value = true;
	loadmoreStatus.value = 'more';
	params.value.page = 1;
	getList();
};

const getMore = () => {
	if (!loadmoreFinished.value) {
		loadmoreStatus.value = 'loading';
		getList();
	}
};

const setOrder = (order) => {
	params.value.order = order;
	uni.showLoading();
	getInit();
	util.updateUrl(params.value);
};

const setOrderPrice = () => {
	if (!['价格最低', '价格最高'].includes(params.value.order)) {
		params.value.order = '价格最低';
	} else if (params.value.order == '价格最低') {
		params.value.order = '价格最高';
	} else {
		params.value.order = '价格最低';
	}
	uni.showLoading();
	getInit();
	util.updateUrl(params.value);
};

const doSearch = () => {
	uni.showLoading();
	getInit();
	util.updateUrl(params.value);
};

const switchTab = (url) => {
	uni.switchTab({ url: url });
};

const jumpPage = (url) => {
	uni.navigateTo({ url: url });
};
</script>

<style>
@import url("product.css");
page {
	background-color: #fff;
}
</style>

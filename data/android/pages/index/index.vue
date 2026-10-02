<template>
	<view>
		<CustomTopIndex :topTitle="topTitle"></CustomTopIndex>
		<view class="skeleton_banner aduty-skeleton" v-if="bannerListLoading">
			<view class="skeleton_banner_item aduty-skeleton-item aduty-skeleton-animated"></view>
		</view>
		<view class="banner" v-if="bannerList.length > 0">
			<swiper class="swiper" circular :autoplay="true" :style="{height: `${swiperHeight}`}">
				<swiper-item v-for="item in bannerList" :key="item.id"
					@click="jumpPage(item.open_mode == 1 ? item.url : '/pages/index/out?url=' + item.url)">
					<view class="swiper-item">
						<image class="img" mode="widthFix" :src="item.image" @load="onLoadImg" />
					</view>
				</swiper-item>
			</swiper>
		</view>
		<view class="skeleton_sudoku aduty-skeleton" v-if="sudokuListLoading">
			<view class="container">
				<view class="skeleton_sudoku_items">
					<view class="skeleton_sudoku_item"><view class="aduty-skeleton-item aduty-skeleton-animated"></view></view>
					<view class="skeleton_sudoku_item"><view class="aduty-skeleton-item aduty-skeleton-animated"></view></view>
					<view class="skeleton_sudoku_item"><view class="aduty-skeleton-item aduty-skeleton-animated"></view></view>
					<view class="skeleton_sudoku_item"><view class="aduty-skeleton-item aduty-skeleton-animated"></view></view>
					<view class="skeleton_sudoku_item"><view class="aduty-skeleton-item aduty-skeleton-animated"></view></view>
					<view class="skeleton_sudoku_item"><view class="aduty-skeleton-item aduty-skeleton-animated"></view></view>
					<view class="skeleton_sudoku_item"><view class="aduty-skeleton-item aduty-skeleton-animated"></view></view>
					<view class="skeleton_sudoku_item"><view class="aduty-skeleton-item aduty-skeleton-animated"></view></view>
				</view>
			</view>
		</view>
		<view class="sudoku" v-if="sudokuList.length > 0">
			<view class="container">
				<view class="item" v-for="item in sudokuList" :key="item.id"
					@click="item.open_mode == 1 ? jumpPage(item.url) : switchTab(item.url)">
					<image class="img" :src="item.image" />
					<view class="txt">{{item.title}}</view>
				</view>
			</view>
		</view>
		<view class="skeleton_prolist aduty-skeleton" v-if="dataListLoading">
			<view class="container">
				<view class="skeleton_prolist_items">
					<view class="skeleton_prolist_item aduty-skeleton-item aduty-skeleton-animated"></view>
					<view class="skeleton_prolist_item aduty-skeleton-item aduty-skeleton-animated"></view>
					<view class="skeleton_prolist_item aduty-skeleton-item aduty-skeleton-animated"></view>
					<view class="skeleton_prolist_item aduty-skeleton-item aduty-skeleton-animated"></view>
					<view class="skeleton_prolist_item aduty-skeleton-item aduty-skeleton-animated"></view>
					<view class="skeleton_prolist_item aduty-skeleton-item aduty-skeleton-animated"></view>
				</view>
			</view>
		</view>
		<view class="prolist" v-if="!dataListLoading">
			<view class="container">
				<view v-if="dataList.length > 0">
					<view class="items">
						<view class="item" @click="jumpPage('/pages/product/show?id=' + item.id + '&sku=' + item.sku)" v-for="(item, index) in dataList" :key="index">
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
	import { onLoad, onShow, onReachBottom, onPullDownRefresh } from '@dcloudio/uni-app';
	import CustomTopIndex from "@/components/CustomTopIndex.vue";
	import http from "@/utils/http.js";

	const topTitle = ref('');
	const dataList = ref([]);
	const dataListLoading = ref(true);
	const loadmoreStatus = ref('more');
	const loadmoreFinished = ref(false);
	const params = ref({
		page_size: 10,
		page: 1,
	});
	const bannerListLoading = ref(true);
	const bannerList = ref([]);
	const sudokuListLoading = ref(true);
	const sudokuList = ref([]);
	const swiperHeight = ref(0);
	const config = ref({});

	onLoad(() => {
		http.post('/common/getConfig').then(res => {
			topTitle.value = res.data.app_name;
			uni.setNavigationBarTitle({
				title: res.data.app_name
			});
			config.value = res.data;
		});

		http.post('/common/getAdver', { code: 'h5_index_banner' }).then(res => {
			bannerListLoading.value = false;
			bannerList.value = res.data.values ?? [];
		});

		http.post('/common/getAdver', { code: 'h5_index_sudoku' }).then(res => {
			sudokuListLoading.value = false;
			sudokuList.value = res.data.values ?? [];
		});

		getInit();
	});

	// 触底加载
	onReachBottom(() => {
		getMore();
	});

	// 下拉刷新
	onPullDownRefresh(() => {
		getInit();
	});

	// 获取列表数据
	const getList = () => {
		let reqParams = params.value;
		http.post('/product/getList', reqParams).then(res => {
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

	// 初始化请求
	const getInit = () => {
		dataListLoading.value = true;
		loadmoreStatus.value = 'more';
		params.value.page = 1;
		getList();
	};

	// 加载更多
	const getMore = () => {
		if (!loadmoreFinished.value) {
			loadmoreStatus.value = 'loading';
			getList();
		}
	};

	// 图片加载完成
	const onLoadImg = (e) => {
		// 获取可使用窗口宽度
		var width = uni.getSystemInfoSync().windowWidth;
		// 获取图片实际高度
		var imgheight = e.detail.height;
		// 获取图片实际宽度
		var imgwidth = e.detail.width;
		var height = width * imgheight / imgwidth + "px";
		swiperHeight.value = height;
	};

	const switchTab = (url) => {
		uni.switchTab({ url: url });
	};

	const jumpPage = (url) => {
		uni.navigateTo({ url: url });
	};
</script>

<style>
@import url("index.css");
@import url("../product/product.css");
.skeleton_banner .skeleton_banner_item {
	width: 750rpx;
	height: 300rpx;
}
.skeleton_sudoku {
	margin-top: 46rpx;
}
.skeleton_sudoku .skeleton_sudoku_items {
	display: flex;
	flex-wrap: wrap;
}
.skeleton_sudoku .skeleton_sudoku_items .skeleton_sudoku_item {
	width: 25%;
	text-align: center;
	margin-bottom: 46rpx;
}
.skeleton_sudoku .skeleton_sudoku_items .skeleton_sudoku_item .aduty-skeleton-item {
	width: 120rpx;
	height: 120rpx;
	border-radius: 50%;
}
.skeleton_prolist {
	margin-top: 30rpx;
}
.skeleton_prolist .skeleton_prolist_items {
	display: flex;
	flex-wrap: wrap;
	margin-right: -20rpx;
	margin-bottom: -20rpx;
}
.skeleton_prolist .skeleton_prolist_items .skeleton_prolist_item {
	width: 340rpx;
	height: 460rpx;
	margin-right: 20rpx;
	margin-bottom: 20rpx;
	border-radius: 4px;
}
</style>

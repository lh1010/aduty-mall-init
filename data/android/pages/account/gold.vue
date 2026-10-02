<template>
  <view class="page">
    <CustomTop topTitle="我的金币"></CustomTop>
		<view class="container">
			<view class="pagetop" v-if="!loading">
				<view class="box">
					<view class="number">
						<view class="icon"><i class="iconfont icon-jinbi"></i></view>
						<view class="txt">{{ loginUser.gold }}</view>
					</view>
					<view class="description">小金币有大用途，多领一些存起来吧~</view>
					<view class="actionbox">
						<span class="btn" @click="uni.navigateTo({url:'/pages/account/gold_pay'})">充值金币</span>
						<span class="btn" @click="uni.navigateTo({url:'/pages/account/exchange_cdkey'})">卡密兑换</span>
					</view>
				</view>
			</view>
			<view class="log_list" v-if="!dataListLoading">
				<view class="btop"><span class="txt">金币记录</span></view>
				<view class="bd">
					<template v-if="dataList.length > 0">
						<view class="items">
							<view class="item" v-for="(item, index) in dataList" :key="index">
								<view class="info">
									<view class="txt">{{ item.description }}</view>
									<view class="ident">{{ item.ident == 'inc' ? '+' : '-' }}{{ item.gold }}</view>
								</view>
								<view class="date">{{ item.created_at }}</view>
							</view>
						</view>
						<view class="uloadmore"><uni-load-more :status="loadmoreStatus" /></view>
					</template>
					<view class="aduty-empty" v-if="dataList.length == 0">
						<image class="aduty-empty-img" src="/static/images/empty.png" mode="aspectFit"></image>
						<text class="aduty-empty-text">暂无记录</text>
					</view>
				</view>
			</view>
		</view>
  </view>
</template>

<script setup>
import { ref } from 'vue';
import { onLoad, onShow, onReachBottom } from '@dcloudio/uni-app';
import http from "@/utils/http.js";
import CustomTop from "@/components/CustomTop.vue";

onLoad(() => {
  uni.setNavigationBarTitle({ title: '我的金币' });
});

const loading = ref(true);
const loginUser = ref({});
const dataList = ref([]);
const dataListLoading = ref(true);
const loadmoreStatus = ref('loadmore');
const loadmoreFinished = ref(false);
const params = ref({ page_size: 15, page: 1 });

const getUser = () => {
  uni.showLoading();
  http.post('/account/getLoginUser').then(res => {
    uni.hideLoading();
    loginUser.value = res.data;
    loading.value = false;
  });
};

const getList = () => {
  http.post('/account/getGoldLogsPaginate', params.value).then(res => {
    dataListLoading.value = false;
    if (res.data.total == 0) return;
    if (res.data.current_page == 1) {
      dataList.value = res.data.data;
    } else {
      dataList.value = dataList.value.concat(res.data.data);
    }
    if (params.value.page >= res.data.last_page) {
      loadmoreFinished.value = true;
      loadmoreStatus.value = 'nomore';
      return;
    }
    params.value.page = parseInt(res.data.current_page) + 1;
    loadmoreStatus.value = 'loadmore';
    loadmoreFinished.value = false;
  });
};

const getInit = () => {
  uni.showLoading();
  dataList.value = [];
  dataListLoading.value = true;
  loadmoreStatus.value = 'loadmore';
  loadmoreFinished.value = false;
  params.value.page = 1;
  getList();
};

const getMore = () => {
  if (!loadmoreFinished.value) {
    loadmoreStatus.value = 'loading';
    getList();
  }
};

onShow(() => {
  getUser();
  getInit();
});

onReachBottom(() => {
  getMore();
});
</script>

<style>
page {
	padding-bottom: 30rpx;
}
.pagetop {
  width: 690rpx;
  margin: 0 auto;
  margin-top: 30rpx;
  border-radius: 2px;
  background-color: #61e7ce;
}
.pagetop .box {
  padding: 30rpx 30rpx 50rpx 30rpx;
  overflow: hidden;
  position: relative;
}
.pagetop .number {
  overflow: hidden;
	display: flex;
	align-items: center;
}
.pagetop .number .icon {
  margin-right: 10rpx;
  font-size: 16px;
}
.pagetop .number .txt {
  font-size: 40px;
}
.pagetop .actionbox {
	margin-top: 50rpx;
}
.pagetop .actionbox .btn {
  margin-left: 6px;
	font-size: 14px;
	background-color: #333;
	color: #61e7ce;
	border-radius: 5rpx;
	padding: 8px 15px;
}
.pagetop .actionbox .btn:first-child {
  margin-left: 0;
}
.pagetop .description {
  font-size: 12px;
}

.log_list {
  margin-top: 30rpx;
	background-color: #fff;
	font-size: 14px;
	border-radius: 2px;
}
.log_list .btop {
	font-weight: bold;
	padding: 30rpx;
	border-bottom: 1px solid #f5f5f5;
}
.log_list .item {
	border-bottom: 1px solid #f5f5f5;
	padding: 30rpx;
}
.log_list .items .info {
	overflow: hidden;
}
.log_list .items .info .txt {
	float: left;
	max-width: 80%;
}
.log_list .items .info .ident {
	float: right;
	color: #61e7ce;
}
.log_list .items .item .date {
	font-size: 24rpx;
	color: #999;
	margin-top: 5px;
}
</style>

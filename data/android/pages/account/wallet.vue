<template>
  <view class="page">
    <CustomTop topTitle="我的钱包"></CustomTop>
    <view class="container" v-if="!loading">
      <view class="wallet_top pagebox">
        <view class="txt">
          <view>钱包余额</view>
          <view class="price">{{loginUser.wallet}}</view>
        </view>
        <view class="btns">
          <button class="aduty-btn aduty-btn-primary" hover-class="aduty-btn-hover" @click="uni.navigateTo({url:'/pages/account/wallet_withdraw'})">提现</button>
          <button class="aduty-btn aduty-btn-payment" hover-class="aduty-btn-hover" @click="uni.navigateTo({url:'/pages/account/wallet_pay'})">充值</button>
        </view>
      </view>
      <view class="log_list" v-if="!dataListLoading">
      	<view class="btop"><span class="txt">钱包明细</span></view>
        <view class="bd">
					<template v-if="dataList.length > 0">
						<view class="items">
							<view class="item" v-for="(item, index) in dataList" :key="index">
								<view class="info">
									<view class="txt">{{item.description}}</view>
									<view class="ident">{{item.ident == 'inc' ? '+' : '-'}}{{item.price}}</view>
								</view>
								<view class="date">{{item.created_at}}</view>
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
import { onLoad, onShow, onReachBottom, onPullDownRefresh } from '@dcloudio/uni-app';
import http from "@/utils/http.js";
import CustomTop from "@/components/CustomTop.vue";

onLoad(() => {
  uni.setNavigationBarTitle({ title: '我的钱包' });
});

const loading = ref(true);
const loginUser = ref({});
const dataList = ref([]);
const dataListLoading = ref(true);
const loadmoreStatus = ref('loadmore');
const loadmoreFinished = ref(false);
const params = ref({
  page_size: 15,
  page: 1,
});

onShow(() => {
  getUser();
  getList();
});

onReachBottom(() => {
  getMore();
});

onPullDownRefresh(() => {
  uni.showLoading();
  getInit();
});

const getUser = () => {
  uni.showLoading();
  http.post('/account/getLoginUser').then(res => {
    uni.hideLoading();
    loginUser.value = res.data;
    loading.value = false;
  });
};

const getList = () => {
  http.post('/account/getWalletLogsPaginate', params.value).then(res => {
    uni.stopPullDownRefresh();
    dataListLoading.value = false;
    if (res.data.total == 0) return;
    if (params.value.page == 1) {
      dataList.value = res.data.data;
    } else {
      dataList.value = dataList.value.concat(res.data.data);
    }
    if (params.value.page >= res.data.last_page) {
      loadmoreFinished.value = true;
      loadmoreStatus.value = 'nomore';
      return;
    }
    params.value.page++;
    loadmoreStatus.value = 'loadmore';
    loadmoreFinished.value = false;
  });
};

const getInit = () => {
  dataListLoading.value = true;
  dataList.value = [];
  loadmoreStatus.value = 'loadmore';
  params.value.page = 1;
  getList();
};

const getMore = () => {
  if (!loadmoreFinished.value) {
    loadmoreStatus.value = 'loading';
    getList();
  }
};
</script>

<style>
page {
	padding-bottom: 30rpx;
}
.wallet_top {
	text-align: center;
	padding-top: 40rpx !important;
	padding-bottom: 50rpx !important;
}
.wallet_top .txt .price {
	font-size: 68rpx;
}
.wallet_top .txt .price i {
	font-size: 32rpx;
	margin-right: 6px;
	font-style: normal;
}
.wallet_top .btns {
	margin-top: 32rpx;
  display: flex;
	justify-content: center;
}
.wallet_top .btns .aduty-btn {
	width: 300rpx;
	font-size: 14px;
	margin: 0 10rpx;
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
	color: #FF0000;
}
.log_list .items .item .date {
	font-size: 24rpx;
	color: #999;
	margin-top: 5px;
}
</style>

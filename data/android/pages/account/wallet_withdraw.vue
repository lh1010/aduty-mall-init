<template>
  <view class="page">
    <CustomTop topTitle="钱包提现"></CustomTop>
    <view class="container" v-if="!loading">
      <form class="aduty-form">
        <view class="aduty-form-box">
          <view class="aduty-form-cell">
            <view class="aduty-form-cell-box">
              <view class="aduty-form-cell-hd">
                <view class="aduty-form-label">钱包余额</view>
              </view>
              <view class="aduty-form-cell-bd">
                {{ loginUser.wallet }}元
              </view>
            </view>
          </view>
          <view class="aduty-form-cell">
            <view class="aduty-form-cell-box">
              <view class="aduty-form-cell-hd">
                <view class="aduty-form-label">提现金额</view>
              </view>
              <view class="aduty-form-cell-bd">
                <input v-model="form.price" class="aduty-form-input" type="text" placeholder="¥" />
              </view>
            </view>
          </view>
          <view class="aduty-form-cell">
            <view class="aduty-form-cell-box">
              <view class="aduty-form-cell-hd">
                <view class="aduty-form-label">支付宝账号</view>
              </view>
              <view class="aduty-form-cell-bd">
                <input v-model="form.alipay_account" class="aduty-form-input" type="text" placeholder="请输入支付宝账号" />
              </view>
            </view>
          </view>
          <view class="aduty-form-cell">
            <view class="aduty-form-cell-box">
              <view class="aduty-form-cell-hd">
                <view class="aduty-form-label">账号名字</view>
              </view>
              <view class="aduty-form-cell-bd">
                <input v-model="form.alipay_name" class="aduty-form-input" type="text" placeholder="请输入支付宝账号名字" />
              </view>
            </view>
          </view>
        </view>
        <view class="aduty-form-action">
          <button class="aduty-btn aduty-btn-primary" hover-class="aduty-btn-hover" @click="formSubmit">提交信息</button>
        </view>
      </form>

      <view class="msgbox" v-if="sysConfig.withdrawal.min > 0 || sysConfig.withdrawal.max > 0 || sysConfig.withdrawal.today_count > 0 || sysConfig.withdrawal.rate > 0">
        <view class="item" v-if="sysConfig.withdrawal.rate > 0">提现手续费：{{sysConfig.withdrawal.rate * 100}}%</view>
        <view class="item" v-if="sysConfig.withdrawal.min > 0">单次最小提现金额：{{sysConfig.withdrawal.min}}元</view>
        <view class="item" v-if="sysConfig.withdrawal.max > 0">单次最大提现金额：{{sysConfig.withdrawal.max}}元</view>
        <view class="item" v-if="sysConfig.withdrawal.today_count > 0">每天最多可提现次数：{{sysConfig.withdrawal.today_count}}次</view>
      </view>

      <view class="log_list" v-if="!dataListLoading">
        <view class="top"><span class="txt">提现记录</span></view>
				<view class="bd">
					<template v-if="dataList.length > 0">
					  <view class="items">
					    <view class="item" v-for="(item, index) in dataList" :key="index">
					      <view>申请金额：{{item.price}}</view>
					      <view>手续费：{{item.commission_price}}</view>
					      <view>
					        审核状态：
					        <span v-if="item.status == 0">审核中</span>
					        <span v-if="item.status == 1">审核成功</span>
					        <span v-if="item.status == 2">
					          审核失败
					          <i class="iconfont icon-wenhao icon_question" @click="uni.showModal({showCancel:false,confirmText:'我知道了',content:item.message || '无留言'})"></i>
					        </span>
					      </view>
					      <view>申请时间：{{item.created_at}}</view>
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
import { onLoad, onReachBottom } from '@dcloudio/uni-app';
import http from "@/utils/http.js";
import CustomTop from "@/components/CustomTop.vue";

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
const sysConfig = ref({ withdrawal: {} });
const form = ref({ price: '', alipay_account: '', alipay_name: '' });

onLoad(() => {
  uni.setNavigationBarTitle({ title: '钱包提现' });
  http.post('/common/getConfig').then(res => {
    sysConfig.value = res.data;
  });
  getUser();
  getInit();
});

onReachBottom(() => {
  getMore();
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
  http.post('/account/getWalletWithdrawalLogsPaginate', params.value).then(res => {
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

const formSubmit = () => {
  if (!form.value.price) {
    uni.showToast({ title: '请输入提现金额', icon: 'none' });
    return;
  }
  if (!form.value.alipay_account) {
    uni.showToast({ title: '请输入支付宝账号', icon: 'none' });
    return;
  }
  if (!form.value.alipay_name) {
    uni.showToast({ title: '请输入支付宝账号名字', icon: 'none' });
    return;
  }
  uni.showModal({
    content: '确认提交？',
    success(res) {
      if (res.confirm) {
        uni.showLoading();
        http.post('/account/walletWithdraw', form.value).then(res => {
          uni.hideLoading();
          if (res.code == 200) {
            getUser();
            getInit();
            uni.showToast({ icon: 'none', title: '申请成功，等待系统审核~' });
          } else if (res.code == 400) {
            uni.showToast({ title: res.message, icon: 'none' });
          }
        });
      }
    }
  });
};
</script>

<style>
page {
	padding-bottom: 30rpx;
}
.aduty-form-box {
	margin-top: 30rpx;
}
.log_list {
  margin-top: 30rpx;
	background-color: #fff;
	font-size: 14px;
	border-radius: 2px;
}
.log_list .top {
	font-weight: bold;
	padding: 30rpx;
	border-bottom: 1px solid #f5f5f5;
}
.log_list .item {
	border-bottom: 1px solid #f5f5f5;
	padding: 30rpx;
}
.log_list .item:last-child {
	margin-bottom: 0;
}
.icon_question {
  margin-left: 3px;
}
.msgbox {
  color: #ff0000;
  margin-top: 30rpx;
  padding: 30rpx;
  border-radius: 3px;
  font-size: 12px;
	background-color: #fff;
}
</style>

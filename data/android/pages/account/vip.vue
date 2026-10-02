<template>
  <view>
    <CustomTop topTitle="会员特权"></CustomTop>
    <view v-if="!loading">
      <view class="vipcard">
        <view class="bd">
          <view class="stitle">VIP会员</view>
          <view class="des" v-if="loginUser.vip != 1">开通会员，获取多多特权</view>
					<view class="des" v-if="loginUser.vip == 1">有效期：{{ loginUser.member_end_date }}</view>
          <view class="status">{{ loginUser.vip == 1 ? '已开通' : '未开通' }}</view>
        </view>
      </view>

			<view class="vip_prices">
				<view class="items">
					<view class="item"
						:class="month == item.month ? 'on' : ''"
						v-for="(item, index) in vipPrices"
						:key="index"
						@click="month = item.month"
					>
						<view class="itembox">
							<view class="date">{{ item.date }}</view>
							<view class="bd">
								<span class="span1">{{ item.gold }}</span>
								<span class="span2">金币</span>
							</view>
						</view>
					</view>
				</view>
			</view>

			<view class="content" v-if="article && Object.keys(article).length > 0">
				<view class="bd">
					<mp-html :content="article.content" />
				</view>
			</view>

      <view class="foot_action_blank"></view>
      <view class="foot_action">
        <view class="btn" @click="payFun">{{ loginUser.vip == 1 ? '续费会员' : '立即开通' }}</view>
      </view>
    </view>
  </view>
</template>

<script setup>
import { ref } from 'vue';
import { onLoad } from '@dcloudio/uni-app';
import http from "@/utils/http.js";
import CustomTop from "@/components/CustomTop.vue";

const loading = ref(true);
const vipPrices = ref([]);
const month = ref('');
const loginUser = ref({});
const article = ref({});

onLoad(() => {
  uni.setNavigationBarTitle({ title: '会员特权' });
  uni.showLoading();
  http.post('/common/getConfig').then(res => {
    vipPrices.value = res.data.vip_prices;
    month.value = res.data.vip_prices[0].month;
  });
  getLoginUser();
  getArticle();
});

const getLoginUser = () => {
  http.post('/account/getLoginUser').then(res => {
    loading.value = false;
    uni.hideLoading();
    loginUser.value = res.data;
  });
};

const payFun = () => {
  let content = loginUser.value.vip == 1 ? '确认续费？' : '确认开通？';
  uni.showModal({
    content: content,
    success(res) {
      if (res.confirm) {
        uni.showLoading();
        http.post('/payment/pay_vip', { month: month.value }).then(res => {
          uni.hideLoading();
          if (res.code == 200) {
            uni.showToast({ title: res.message, icon: 'none' });
            getLoginUser();
          } else {
            uni.showToast({ title: res.message, icon: 'none' });
          }
        });
      }
    }
  });
};

const getArticle = () => {
  http.post('/article/getShow', { type: 'vip' }).then(res => {
    article.value = res.data;
  });
};
</script>

<style>
page {
	background-color: #fff;
}
.vipcard {
  background: linear-gradient(to bottom right, #dacbaa, #d8b66c);
	color: #4e3a10;
  width: 700rpx;
  margin: 0 auto;
  margin-top: 30rpx;
  border-radius: 5px;
  position: relative;
  overflow: hidden;
  font-size: 14px;
  box-shadow: 0 5px 10px rgba(0,0,0,0.15);
}
.vipcard .bd {
  padding: 50rpx 40rpx;
}
.vipcard .stitle {
  font-weight: bold;
  font-size: 20px;
  letter-spacing: 1px;
}
.vipcard .des {
  font-size: 12px;
}
.vipcard .status {
  position: absolute;
  right: 0;
  top: 0;
  background-color: #ece0c4;
  padding: 5px 12px;
  border-top-right-radius: 5px;
  border-bottom-left-radius: 5px;
  font-size: 12px;
}

.vip_prices {
	width: 700rpx;
	margin: 0 auto;
	margin-top: 30rpx;
}
.vip_prices .items {
	display: flex;
	justify-content: space-between;
	flex-wrap: wrap;
	margin-bottom: -30rpx;
}
.vip_prices .item {
	border: 1px solid #eee;
	width: 330rpx;
	margin-bottom: 30rpx;
	text-align: center;
	border-radius: 5px;
	box-shadow: 0 5px 10px rgba(0,0,0,0.05);
	font-size: 14px;
}
.vip_prices .itembox {
	padding: 30rpx;
}
.vip_prices .item.on {
	border: 1px solid #d8b66c;
}
.vip_prices .item .date {
	padding-bottom: 30rpx;
  border-bottom: 1px solid #eee;
}
.vip_prices .item .bd {
	padding-top: 30rpx;
}
.vip_prices .item .bd .span1 {
	font-size: 20px;
	color: #d8b66c;
}
.vip_prices .item .bd .span2 {
	font-size: 12px;
	color: #d8b66c;
}

.content {
	width: 700rpx;
	margin: 0 auto;
	margin-top: 60rpx;
  font-size: 14px;
  border: 1px solid #eee;
  border-radius: 5px;
  box-shadow: 0 5px 10px rgba(0,0,0,0.05);
}
.content .bd {
  padding: 30rpx;
}

.foot_action_blank {
  height: 160rpx;
}
.foot_action {
	position: fixed;
	left: 0;
	bottom: 0;
	width: 100%;
	opacity: .8;
	background-color: #fff;
	border-top: 1px solid #f5f5f5;
	padding: 30rpx 0;
  font-size: 14px;
	font-weight: bold;
}
.foot_action .btn {
	text-align: center;
	height: 80rpx;
	line-height: 80rpx;
  width: 700rpx;
  margin: 0 auto;
	background: linear-gradient(120deg,#fb9503,#fd5b0b);
  box-shadow: 0 10px 20px rgba(148, 105, 39, 0.2);
	color: #fff;
	border: none;
	border-radius: 25px;
	padding: 0;
}
</style>

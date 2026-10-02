<template>
	<view class="page">
		<CustomTopIndex topTitle="个人中心"></CustomTopIndex>
		<view class="container">
      <view class="ptop">
        <view class="userbox" @click="jumpPage('/pages/account/login')" v-if="!logined">
          <image class="avatar" mode="scaleToFill" :src="config.image.user_avatar" />
          <view class="infobox">
            <view class="name">立即登录<i class="iconfont icon-youbian"></i></view>
            <view class="tags">
              <span>点击登录</span>
            </view>
          </view>
        </view>
        <view class="userbox" @click="jumpPage('/pages/account/user_info')" v-if="logined">
          <image class="avatar" mode="scaleToFill" :src="loginUser.avatar" />
          <view class="infobox">
            <view class="name">{{loginUser.nickname}}<i class="iconfont icon-youbian"></i></view>
            <view class="tags">
              <span>平台号:{{loginUser.id}}</span>
            </view>
          </view>
        </view>
        <view class="rightbox">
          <view class="qiandao" @click="qiandao">
            <span class="txt">签到</span>
          </view>
        </view>
      </view>

      <view class="vipcard" @click="jumpPage(logined ? '/pages/account/vip' : '/pages/account/login')">
        <view class="bd" v-if="!logined || loginUser.vip == 0">
          <view class="leftbox">
            <view class="stitle">VIP会员</view>
            <view class="des">开通会员，获取多多特权</view>
          </view>
          <view class="action">
            <span class="btn">立即开通</span>
          </view>
        </view>
        <view class="bd" v-if="logined && loginUser.vip == 1">
          <view class="leftbox">
						<view class="stitle">VIP会员</view>
            <view class="des">有效期：{{loginUser.member_end_date}}</view>
          </view>
          <view class="action">
            <span class="btn">续费会员</span>
          </view>
        </view>
      </view>

      <view class="box100">
      	<view class="items">
          <view class="item" @click="jumpPage(logined ? '/pages/account/wallet' : '/pages/account/login')">
          	<view class="icon"><i class="iconfont icon-qianbao"></i></view>
          	<view class="txt">我的余额</view>
          </view>
					<view class="item" @click="jumpPage(logined ? '/pages/account/gold' : '/pages/account/login')">
						<view class="icon"><i class="iconfont icon-jinbi"></i></view>
						<view class="txt">我的金币</view>
					</view>
					<view class="item" @click="jumpPage(logined ? '/pages/order/list' : '/pages/account/login')">
						<view class="icon"><i class="iconfont icon-daifukuan"></i></view>
						<view class="txt">我的订单</view>
					</view>
          <view class="item" @click="jumpPage('/pages/article/show?type=contact')">
          	<view class="icon"><i class="iconfont icon-kefu11"></i></view>
          	<view class="txt">平台客服</view>
          </view>
      	</view>
      </view>

      <view class="box100 order_status_list">
        <view class="stitle">
          <span class="txt">我的订单</span>
          <view class="more" @click="jumpPage(logined ? '/pages/order/list' : '/pages/account/login')">查看全部</view>
        </view>
      	<view class="items">
          <view class="item" @click="jumpPage(logined ? '/pages/order/list?status=0' : '/pages/account/login')">
          	<view class="icon"><i class="iconfont icon-daifukuan"></i></view>
          	<view class="txt">待付款</view>
          </view>
          <view class="item" @click="jumpPage(logined ? '/pages/order/list?status=10' : '/pages/account/login')">
          	<view class="icon"><i class="iconfont icon-icon2"></i></view>
          	<view class="txt">待发货</view>
          </view>
          <view class="item" @click="jumpPage(logined ? '/pages/order/list?status=20' : '/pages/account/login')">
          	<view class="icon"><i class="iconfont icon-daishouhuo2"></i></view>
          	<view class="txt">待收货</view>
          </view>
          <view class="item" @click="jumpPage(logined ? '/pages/order/list?status=30' : '/pages/account/login')">
          	<view class="icon"><i class="iconfont icon-chenggong"></i></view>
          	<view class="txt">已完成</view>
          </view>
          <view class="item" @click="jumpPage(logined ? '/pages/order/list?status=-10' : '/pages/account/login')">
          	<view class="icon"><i class="iconfont icon-shibai"></i></view>
          	<view class="txt">已取消</view>
          </view>
      	</view>
      </view>

			<view class="box101">
				<view class="items">
					<view class="item" @click="jumpPage(logined ? '/pages/account/user_info' : '/pages/account/login')">
						<view class="left"><i class="iconfont icon-guanyu"></i>个人信息</view>
						<view class="right"><i class="iconfont icon-youbian"></i></view>
					</view>
					<view class="item" @click="jumpPage(logined ? '/pages/address/my_list' : '/pages/account/login')">
						<view class="left"><i class="iconfont icon-buoumaotubiao23"></i>收货地址</view>
						<view class="right"><i class="iconfont icon-youbian"></i></view>
					</view>
					<view class="item" @click="jumpPage(logined ? '/pages/account/auth_index' : '/pages/account/login')">
						<view class="left"><i class="iconfont icon-renzheng"></i>身份认证</view>
						<view class="right"><i class="iconfont icon-youbian"></i></view>
					</view>
					<view class="item"
						@click="jumpPage(logined ? '/pages/account/user_password' : '/pages/account/login')">
						<view class="left"><i class="iconfont icon-mima1"></i>设置密码</view>
						<view class="right"><i class="iconfont icon-youbian"></i></view>
					</view>
					<view class="item"
						@click="jumpPage(logined ? '/pages/account/user_contact' : '/pages/account/login')">
						<view class="left"><i class="iconfont icon-ziyuan"></i>联系方式</view>
						<view class="right"><i class="iconfont icon-youbian"></i></view>
					</view>
				</view>
			</view>

      <view class="invitebox" @click="jumpPage(logined ? '/pages/team/index' : '/pages/account/login')">
        <view class="bd">
          <image class="img" mode="scaleToFill" :src="config.image.invite" />
        </view>
      </view>

			<view class="box101">
				<view class="items">
					<view class="item" @click="jumpPage('/pages/article/show?type=about')">
						<view class="left"><i class="iconfont icon-yonghu7"></i>关于我们</view>
						<view class="right"><i class="iconfont icon-youbian"></i></view>
					</view>
					<view class="item" @click="jumpPage('/pages/article/show?type=contact')">
						<view class="left"><i class="iconfont icon-kefu2"></i>联系我们</view>
						<view class="right"><i class="iconfont icon-youbian"></i></view>
					</view>
					<view class="item" @click="jumpPage('/pages/article/show?type=user_agreement')">
						<view class="left"><i class="iconfont icon-guanyuliushu"></i>用户协议</view>
						<view class="right"><i class="iconfont icon-youbian"></i></view>
					</view>
          <view class="item" @click="jumpPage('/pages/article/show?type=privacy_agreement')">
          	<view class="left"><i class="iconfont icon-guanyuliushu"></i>隐私协议</view>
          	<view class="right"><i class="iconfont icon-youbian"></i></view>
          </view>
				</view>
			</view>

			<view class="box101">
				<view class="items">
					<view class="item about">{{config.app_name}} Version {{config.version}}</view>
				</view>
			</view>
		</view>
	</view>
</template>

<script setup>
import { ref } from 'vue';
import { onLoad, onShow } from '@dcloudio/uni-app';
import CustomTopIndex from "@/components/CustomTopIndex.vue";
import http from "@/utils/http.js";

const loading = ref(true);
const logined = ref(false);
const loginUser = ref({});
const config = ref({ image: {} });
const swiperHeight = ref(0);

onLoad(() => {
  uni.setNavigationBarTitle({ title: '个人中心' });
  uni.showLoading();
  http.post('/common/getConfig').then(res => {
    config.value = res.data;
  });
});

onShow(() => {
  getLoginUser();
});

const getLoginUser = () => {
  http.post('/account/getLoginUser').then(res => {
    uni.hideLoading();
    loading.value = false;
    if (res.data.id) {
      logined.value = true;
      loginUser.value = res.data;
    } else {
      logined.value = false;
    }
  });
};

const onLoadImg = (e) => {
  var width = uni.getSystemInfoSync().windowWidth - 30;
  var imgheight = e.detail.height;
  var imgwidth = e.detail.width;
  var height = width * imgheight / imgwidth + "px";
  swiperHeight.value = height;
};

const qiandao = () => {
  uni.showLoading();
  http.post('/account/qiandao').then(res => {
    uni.hideLoading();
    uni.showModal({
      showCancel: false,
      confirmText: '我知道了',
      content: res.message
    });
  });
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
page {
	padding-bottom: 30rpx;
}
</style>

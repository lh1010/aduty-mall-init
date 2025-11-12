<template>
	<view>
    <CustomTop top_title="身份认证"></CustomTop>
    <view class="items">
      <view class="item" @click="jumpPage('/pages/account/auth_realname')">
        <view class="title">实名认证</view>
        <view class="description">认证成功可显示实名认证标识</view>
      </view>
      <view class="item" @click="jumpPage('/pages/account/auth_company')">
        <view class="title">企业认证</view>
        <view class="description">认证成功显示企业认证标识</view>
      </view>
    </view>
	</view>
</template>

<script>
import { request } from "@/utils/http.js"
import CustomTop from "@/components/CustomTop.vue"

export default {
  components: { CustomTop },

  data() {
    return {}
  },

  onLoad: function() {},

  methods: {
    switchTab: function(url) {
      uni.switchTab({ url: url });
    },

    jumpPage: function(url) {
      uni.navigateTo({ url: url });
    }
  }
}
</script>
<style>
page {
  padding-bottom: 30rpx;
}
.items {
	display: flex;
	flex-wrap: wrap;
  justify-content: space-between;
  width: 700rpx;
  margin: 0 auto;
}
.items .item {
	width: 100%;
	margin-top: 30rpx;
  background-color: #ffffff;
  border-radius: 3px;
  padding: 32rpx;
}
.items .item .title {
  letter-spacing: 3px;
	font-weight: bold;
}
.items .item .description {
  color: #999;
  font-size: 12px;
  margin-top: 10rpx;
}
</style>

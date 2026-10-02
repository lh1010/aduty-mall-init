<template>
	<view class="page_customtop">
    <view class="customtop">
      <view class="item left">
        <i class="back iconfont icon-xiangzuo2" @click="goBack"></i>
        <i class="home iconfont icon-shouye" @click="goHome"></i>
      </view>
      <view class="item title">{{topTitle}}</view>
      <view class="item right" @click="jumpPage(rightUrl)">{{rightTxt}}</view>
    </view>
    <view class="customtop_blank"></view>
	</view>
</template>

<script>
export default {
  name:"CustomTop",

  data() {
    return {};
  },

  props: {
    topTitle: {
      type: String
    },

		rightTxt: {
		  type: String
		},

		rightUrl: {
		  type: String
		},
  },

  methods: {
    goBack: function() {
      uni.navigateBack();
    },

    goHome: function() {
      uni.switchTab({ url: '/pages/index/index' });
    },

		switchTab: function(url) {
			uni.switchTab({ url: url });
		},

		jumpPage: function(url) {
			uni.navigateTo({ url: url })
		}
  }
}
</script>

<style></style>

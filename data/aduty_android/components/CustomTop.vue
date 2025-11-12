<template>
	<view>
    <view class="customtop">
      <view class="item left">
        <i class="back iconfont icon-xiangzuo2" @click="goBack"></i>
        <i class="home iconfont icon-shouye" @click="goHome"></i>
      </view>
      <view class="item title">{{top_title}}</view>
      <view class="item right" @click="jumpPage(right_url)">{{right_txt}}</view>
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
    top_title: {
      type: String
    },

		right_txt: {
		  type: String
		},

		right_url: {
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

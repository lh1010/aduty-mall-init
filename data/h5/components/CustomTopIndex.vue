<template>
	<view class="page_customtop none">
    <view class="customtop">
      <view class="item left"></view>
      <view class="item title">{{topTitle}}</view>
      <view class="item right"></view>
    </view>
    <view class="customtop_blank none"></view>
	</view>
</template>

<script>
export default {
  name:"CustomTopIndex",

  data() {
    return {};
  },

  props: {
    topTitle: {
      type: String
    }
  },

  methods: {
    goBack: function() {
      uni.navigateBack();
    },

    goHome: function() {
      uni.switchTab({ url: '/pages/index/index' });
    }
  }
}
</script>

<style></style>

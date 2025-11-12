<template>
	<view>
    <view class="customtop none">
      <view class="item left"></view>
      <view class="item title">{{top_title}}</view>
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
    top_title: {
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

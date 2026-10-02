<template>
	<view>
		<CustomTop topTitle="详情"></CustomTop>
    <view class="container">
      <view class="article_show" v-if="!loading">
        <view class="top">
          <view class="title">{{ article.title }}</view>
        </view>
        <view class="content">
          <mp-html :content="article.content" />
        </view>
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
const article = ref({});

onLoad((options) => {
  uni.setNavigationBarTitle({ title: '详情' });
  let params = {};
  if (options.id != undefined) {
    params.id = options.id;
  }
  if (options.type != undefined) {
    params.type = options.type;
  }
  uni.showLoading();
  http.post('/article/getShow', params).then(res => {
    uni.hideLoading();
    loading.value = false;
    article.value = res.data;
  });
});
</script>

<style>
@import url("article.css");
page {
  background-color: #fff;
}
</style>

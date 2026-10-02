<template>
  <view>
    <CustomTop :topTitle="topTitle"></CustomTop>
    <view class="container">
      <view class="article_list" v-if="!loading">
        <view class="items" v-if="dataList.length > 0">
          <view
            class="item"
            v-for="(item, index) in dataList"
            :key="index"
            @click="uni.navigateTo({url: '/pages/article/show?id=' + item.id})"
          >
            <image :src="item.cover" class="cover" mode="aspectFill" v-if="item.cover != ''" />
            <view class="info">
              <view class="title">{{ item.title }}</view>
              <view class="description">{{ item.description ? item.description : '暂无简介' }}</view>
            </view>
          </view>
          <uni-load-more :status="loadmoreStatus" />
        </view>
        <view class="aduty-empty" v-if="dataList.length == 0">
          <image class="aduty-empty-img" src="/static/images/empty.png" mode="aspectFit"></image>
          <text class="aduty-empty-text">暂无内容</text>
        </view>
      </view>
    </view>
  </view>
</template>

<script setup>
import { ref, watch } from 'vue';
import { onLoad, onReachBottom } from '@dcloudio/uni-app';
import http from "@/utils/http.js";
import CustomTop from "@/components/CustomTop.vue";

const topTitle = ref('文档中心');
watch(topTitle, (val) => {
  uni.setNavigationBarTitle({ title: val });
});
const loading = ref(true);
const dataList = ref([]);
const loadmoreStatus = ref('loadmore');
const loadmoreFinished = ref(false);
const params = ref({ page_size: 15, page: 1, category_id: '' });

onLoad((options) => {
  if (options.category_id != undefined && options.category_id != '') {
    params.value.category_id = options.category_id;
    http.post('/article/getCategory', { id: options.category_id }).then(res => {
      if (res.data != null) {
        topTitle.value = res.data.name;
      }
    });
  }
  uni.showLoading();
  getList();
});

onReachBottom(() => {
  getMore();
});

const getList = () => {
  http.post('/article/getList', params.value).then(res => {
    uni.stopPullDownRefresh();
    uni.hideLoading();
    loading.value = false;
    if (res.data.total == 0) {
      dataList.value = [];
      return;
    }

    if (res.data.current_page == 1) {
      dataList.value = res.data.data;
    } else {
      dataList.value = dataList.value.concat(res.data.data);
    }

    if (params.value.page == res.data.last_page) {
      loadmoreFinished.value = true;
      loadmoreStatus.value = 'nomore';
      return;
    }

    params.value.page = parseInt(res.data.current_page) + 1;
    loadmoreStatus.value = 'loadmore';
    loadmoreFinished.value = false;
  });
};

const getMore = () => {
  if (!loadmoreFinished.value) {
    loadmoreStatus.value = 'loading';
    getList();
  }
};
</script>

<style>
@import url("article.css");
page {
  background-color: #fff;
}
</style>

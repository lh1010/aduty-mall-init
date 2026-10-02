<template>
	<view class="page">
		<CustomTopIndex topTitle="商品分类"></CustomTopIndex>
		<view class="category" v-if="!loading">
      <scroll-view class="sidebar scroll-view" :style="{height: scrollHeight + 'px'}" scroll-y="true">
        <view class="items">
          <view
            class="item"
            :class="categoryId == item.id ? 'on' : ''"
            v-for="(item, index) in categorys"
            :key="index"
            @click="setCategoryId(item.id)"
          >
            <span class="name">{{ item.name }}</span>
          </view>
        </view>
      </scroll-view>
      <scroll-view class="main scroll-view" :style="{height: scrollHeight + 'px'}" scroll-y="true">
        <template v-for="item in categorys" :key="item.id">
          <view class="box" v-if="categoryId == item.id">
          <button class="aduty-btn aduty-btn-primary" @click="jumpPage('/pages/product/list?category_id=' + item.id)">进入{{ category.name }}</button>
          <view class="items">
            <view class="item" v-for="item100 in item.items" :key="item100.id">
              <view class="stitle" @click="jumpPage('/pages/product/list?category_id=' + item100.id)">{{ item100.name }}</view>
              <view class="options">
                <span
                  class="option"
                  v-for="item101 in item100.items"
                  :key="item101.id"
									@click="jumpPage('/pages/product/list?category_id=' + item101.id)"
                >
                  {{ item101.name }}
                </span>
              </view>
            </view>
          </view>
        </view>
        </template>
      </scroll-view>
		</view>
	</view>
</template>

<script setup>
import { ref, nextTick } from 'vue';
import { onLoad } from '@dcloudio/uni-app';
import http from "@/utils/http.js";
import CustomTopIndex from "@/components/CustomTopIndex.vue";

const loading = ref(true);
const categorys = ref([]);
const categoryId = ref('');
const category = ref({});
const scrollHeight = ref(0);

onLoad(() => {
  uni.setNavigationBarTitle({ title: '商品分类' });
  uni.showLoading();
  http.post('/product/getCategorys', { get_attributes: 1 }).then(res => {
    loading.value = false;
    uni.hideLoading();
    if (res.data.length > 0) {
      categoryId.value = res.data[0].id;
      category.value = res.data[0];
      categorys.value = res.data;
    }
		// 获取页面高度
    nextTick(() => {
      uni.createSelectorQuery().select('.page_customtop').boundingClientRect((data) => {
        if (data) {
          const sysInfo = uni.getSystemInfoSync();
          scrollHeight.value = sysInfo.windowHeight - data.height;
        }
      }).exec();
    });
  });
});

const setCategoryId = (id) => {
  categoryId.value = id;
  category.value = categorys.value.find(item => item.id == id);
};

const jumpPage = (url) => {
  uni.navigateTo({ url: url })
};
</script>

<style>
@import url("product.css");
page {
	background-color: #fff;
}
</style>

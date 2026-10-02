<template>
<Top />
<Head />
<Menu />
<div class="banner">
  <el-skeleton animated v-if="banners.length === 0">
    <template #template>
      <div class="skeleton_banner el-skeleton__item"></div>
    </template>
  </el-skeleton>
  <swiper
    v-if="banners.length > 0"
    :modules="[Pagination, Autoplay]"
    :pagination="{ clickable: true }"
    :autoplay="{ delay: 3000 }"
    :loop="banners.length >= 2"
  >
    <swiper-slide v-for="item in banners" :key="item.id">
      <a :href="item.url || 'javascript:void(0)'" :target="item.url && item.open_mode == 1 ? '_blank' : '_self'">
        <img :src="item.image" :alt="item.title">
      </a>
    </swiper-slide>
  </swiper>
</div>
<!-- <template v-if="sectionsLoading">
  <div class="loading">
    <img src="@/assets/images/loading.gif" alt="loading">
    <div class="txt">正在努力加载中，感谢您的等待</div>
  </div>
</template> -->
<div class="container" v-if="sectionsLoading">
  <el-skeleton animated>
    <template #template>
      <div class="skeleton_sections">
        <div class="skeleton_sections_item">
          <div class="skeleton_sections_item_top el-skeleton__item"></div>
          <div class="skeleton_sections_item_list">
            <div class="skeleton_sections_item_list_item el-skeleton__item"></div>
            <div class="skeleton_sections_item_list_item el-skeleton__item"></div>
            <div class="skeleton_sections_item_list_item el-skeleton__item"></div>
            <div class="skeleton_sections_item_list_item el-skeleton__item"></div>
            <div class="skeleton_sections_item_list_item el-skeleton__item"></div>
          </div>
        </div>
        <div class="skeleton_sections_item">
          <div class="skeleton_sections_item_top el-skeleton__item"></div>
          <div class="skeleton_sections_item_list">
            <div class="skeleton_sections_item_list_item el-skeleton__item"></div>
            <div class="skeleton_sections_item_list_item el-skeleton__item"></div>
            <div class="skeleton_sections_item_list_item el-skeleton__item"></div>
            <div class="skeleton_sections_item_list_item el-skeleton__item"></div>
            <div class="skeleton_sections_item_list_item el-skeleton__item"></div>
          </div>
        </div>
      </div>
    </template>
  </el-skeleton>
</div>
<div class="section prolist" v-for="section in sections" :key="section.id" v-if="!sectionsLoading && sections.length > 0">
  <div class="container">
    <div class="top">
      <span class="title">{{ section.name }}</span>
      <router-link class="more" :to="'/product/list/' + section.id" target="_blank">查看更多</router-link>
    </div>
    <div class="items clearfix">
      <template v-if="section.products.length > 0">
        <div class="item" v-for="product in section.products" :key="product.id">
          <div class="item_box">
            <router-link class="cover" :to="'/product/show/' + product.id" target="_blank">
              <img class="lazy" :src="product.cover">
            </router-link>
            <div class="con">
              <div class="price text-price"><em>¥</em>{{ product.price }}</div>
              <router-link class="title" :to="'/product/show/' + product.id" target="_blank">{{ product.name }}</router-link>
            </div>
          </div>
        </div>
      </template>
      <div class="s_noresult" v-if="section.products.length == 0">
        <p>该分类下暂无商品~</p>
      </div>
    </div>
  </div>
</div>
<Foot />
</template>

<script setup>
defineOptions({ name: 'Home' });
import { ref, onMounted, watch } from 'vue';
import { Swiper, SwiperSlide } from 'swiper/vue';
import { Pagination, Autoplay } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/pagination';
import http from '@/utils/http';
import Top from '@/components/Top.vue';
import Head from '@/components/Head.vue';
import Menu from '@/components/Menu.vue';
import Foot from '@/components/Foot.vue';
import { useConfigStore } from '@/stores/configStore';

const configStore = useConfigStore();
const banners = ref([]);
const sections = ref([]);
const sectionsLoading = ref(true);

watch(() => configStore.data?.app_name, (newName) => { document.title = newName ?? ''; }, { immediate: true });

onMounted(() => {
  getBanners();
  getIndexSections();
});

// get banners
const getBanners = () => {
  http.post('/common/getAdver', { code: 'pc_index_banner' }).then((res) => {
    banners.value = res.data.values;
  });
}

// get index sections
const getIndexSections = () => {
  http.post('/common/getIndexSections').then((res) => {
    sectionsLoading.value = false;
    sections.value = res.data;
  });
}
</script>

<style scoped>
@import '@/assets/style/product.css';
.skeleton_banner {
  height: 440px;
}
.skeleton_sections_item {
  margin-top: 20px;
}
.skeleton_sections_item_top {
  height: 59px;
}
.skeleton_sections_item_list {
  margin-top: 20px;
  margin-bottom: -20px;
}
.skeleton_sections_item_list_item {
  width: 224px;
  height: 308px;
  margin-right: 20px;
  margin-bottom: 20px;
}
.skeleton_sections_item_list_item:nth-child(5n) {
  margin-right: 0;
}
</style>

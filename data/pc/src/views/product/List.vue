<template>
<Top />
<Head />
<Menu />
<div class="prolist">
  <div class="container">
    <div class="nav">
      <span class="on">当前位置：</span>
      <router-link to="/">首页</router-link>
      <span v-if="params.k">></span>
      <span class="on" v-if="params.k">{{ params.k }}</span>
      <template v-else>
        <span>></span>
        <span class="on">{{ category?.name || '商品' }}</span>
      </template>
    </div>
    <div class="condition">
      <div class="c_group">
        <div class="c_group_title">排序方式：</div>
        <div class="c_group_items">
          <a class="span_item" :class="{ on: params.order == '' }" @click="setOrder('')">默认</a>
          <a class="span_item" :class="{ on: params.order == '最新' }" @click="setOrder('最新')">最新</a>
          <a class="span_item" :class="{ on: params.order == '销量' }" @click="setOrder('销量')">销量</a>
          <a class="span_item span_price" :class="{ 'price-asc': params.order == '价格最低', 'price-desc': params.order == '价格最高' }" @click="setOrder('价格')">
            <span class="text">价格</span>
            <span class="arrows">
              <i class="arrow-up"></i>
              <i class="arrow-down"></i>
            </span>
          </a>
        </div>
      </div>
    </div>
    <!-- <template v-if="dataListLoading">
      <div class="loading">
        <img src="@/assets/images/loading.gif" alt="loading">
        <div class="txt">正在努力加载中，感谢您的等待</div>
      </div>
    </template> -->
    <template v-if="dataListLoading">
      <el-skeleton animated>
        <template #template>
          <div class="skeleton_prolist">
            <div class="skeleton_prolist_items">
              <div class="skeleton_prolist_item el-skeleton__item"></div>
              <div class="skeleton_prolist_item el-skeleton__item"></div>
              <div class="skeleton_prolist_item el-skeleton__item"></div>
              <div class="skeleton_prolist_item el-skeleton__item"></div>
              <div class="skeleton_prolist_item el-skeleton__item"></div>
              <div class="skeleton_prolist_item el-skeleton__item"></div>
              <div class="skeleton_prolist_item el-skeleton__item"></div>
              <div class="skeleton_prolist_item el-skeleton__item"></div>
              <div class="skeleton_prolist_item el-skeleton__item"></div>
              <div class="skeleton_prolist_item el-skeleton__item"></div>
            </div>
          </div>
        </template>
      </el-skeleton>
    </template>
    <template v-if="!dataListLoading">
      <template v-if="dataListTotal > 0">
        <div class="items clearfix" v-loading="dataItemsLoading">
          <div class="item" v-for="item in dataList" :key="item.id">
            <div class="item_box">
              <router-link class="cover" :to="'/product/show/' + item.id" target="_blank">
                <img class="lazy" :src="item.cover">
              </router-link>
              <div class="con">
                <div class="price text-price"><em>¥</em>{{ item.price }}</div>
                <router-link class="title" :to="'/product/show/' + item.id" target="_blank">{{ item.name }}</router-link>
              </div>
            </div>
          </div>
        </div>
        <div class="page">
          <el-pagination
            size="small"
            :total="dataListTotal"
            :current-page="parseInt(params.page)"
            :page-size="parseInt(params.page_size)"
            @current-change="pageChange"
            background
            layout="prev, pager, next"
            v-if="dataListTotal > params.page_size"
          >
          </el-pagination>
        </div>
      </template>
      <template v-else>
        <div class="noresult">
          <img src="@/assets/images/noresult.png" alt="noresult">
          <p>暂无商品</p>
        </div>
      </template>
    </template>
  </div>
</div>
<Foot />
</template>

<script setup>
defineOptions({ name: 'ProductList' });
import { ref, onMounted, watch } from 'vue';
import http from '@/utils/http';
import Top from '@/components/Top.vue';
import Head from '@/components/Head.vue';
import Menu from '@/components/Menu.vue';
import Foot from '@/components/Foot.vue';
import util from '@/utils/util';
import { useRoute } from 'vue-router';
import { useConfigStore } from '@/stores/configStore';

const configStore = useConfigStore();
const route = useRoute();
const categoryId = ref(route.params.id); // 分类ID
const dataListLoading = ref(true);
const dataList = ref([]);
const dataListTotal = ref(0);
const dataItemsLoading = ref(true);
const params = ref({
  page_size: 15,
  page: 1,
  k: '',
  order: '',
});
const category = ref({});

watch(
  [() => configStore.data.app_name, () => category.value?.name, () => params.value.k],
  ([newName, newCategoryName, newK]) => {
    let titlePart = '';
    if (newK) {
      titlePart = newK;
    } else if (newCategoryName) {
      titlePart = newCategoryName;
    } else {
      titlePart = '商品';
    }
    document.title = titlePart + (newName ? ' ' + newName : '');
  },
  { immediate: true }
);

watch(() => route.params.id, (newId) => {
  categoryId.value = newId;
  category.value = {};
  if (newId) {
    getCategory();
  }
  getInit();
});

watch(() => route.query.k, (newK) => {
  params.value.k = newK || '';
  params.value.order = '';
  getInit();
});

onMounted(() => {
  let urlParams = new URLSearchParams(window.location.search);
  if (urlParams.toString() != '') {
    for (const [key, value] of urlParams) {
      params.value[key] = value;
    }
  }
  getCategory();
  getList();
});

const setOrder = (order) => {
  if (order == '价格') {
    if (params.value.order == '价格最低') {
      params.value.order = '价格最高';
    } else if (params.value.order == '价格最高') {
      params.value.order = '';
    } else {
      params.value.order = '价格最低';
    }
  } else {
    params.value.order = order;
  }
  getInit();
  util.updateUrl(params.value);
};

const getCategory = () => {
  http.post('/product/getCategory', { id: categoryId.value }).then(res => {
    category.value = res.data;
  });
};

const getInit = () => {
  dataListLoading.value = true;
  dataList.value = [];
  dataListTotal.value = 0;
  params.value.page = 1;
  getList();
};

const getList = () => {
  const paramData = { ...params.value };
  if (categoryId.value != undefined) {
    paramData.category_id = categoryId.value;
  }
  http.post('/product/getList', paramData).then(res => {
    dataListLoading.value = false;
    dataItemsLoading.value = false;
    dataListTotal.value = res.data.total;
    dataList.value = res.data.data;
  });
};

const pageChange = (page) => {
  dataItemsLoading.value = true;
  params.value.page = page;
  getList(params);
  util.updateUrl(params.value);
  window.scrollTo({ top: 0 });
}
</script>

<style scoped>
@import '@/assets/style/product.css';
.skeleton_prolist_items {
  display: flex;
  flex-wrap: wrap;
}
.skeleton_prolist_item {
  width: 224px;
  height: 308px;
  margin-bottom: 20px;
  margin-right: 20px;
}
.skeleton_prolist_item:nth-child(5n) {
  margin-right: 0;
}
</style>

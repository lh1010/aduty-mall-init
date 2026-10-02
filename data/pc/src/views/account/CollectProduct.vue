<template>
<Top />
<TopNav />
<div class="account clearfix collect_product_page">
  <div class="container">
    <LeftMenu />
    <!-- main box start -->
    <div class="account_main">
      <div class="am_unify_box">
        <div class="top">
          <span class="title">收藏商品</span>
        </div>
        <template v-if="dataListLoading">
          <div class="loading">
            <img src="@/assets/images/loading.gif" alt="loading">
            <div class="txt">正在努力加载中，感谢您的等待</div>
          </div>
        </template>
        <template v-if="!dataListLoading">
          <template v-if="dataListTotal > 0">
            <div class="items clearfix" v-loading="dataItemsLoading">
              <div class="item" v-for="item in dataList" :key="item.id">
                <div class="item_box">
                  <div class="cover">
                    <img class="lazy" :src="item.cover" />
                    <div class="actions">
                      <a class="a1" href="javascript:void(0);" @click="addCart(item.sku)">加入购物车</a>
                      <a class="a2" href="javascript:void(0);" @click="deleteCollect(item.sku)">移除</a>
                    </div>
                  </div>
                  <div class="con">
                    <router-link class="title" :to="'/product/show/' + item.product_id + '?sku=' + item.sku" target="_blank">{{ item.product_name }}</router-link>
                    <div class="types" v-if="item.specifications && item.specifications.length > 0">
                      <div class="types_item" v-for="specn in item.specifications" :key="specn.id">
                        {{ specn.specification_name }} - {{ specn.specification_option }}
                      </div>
                    </div>
                    <div class="price text-price"><em>¥</em>{{ item.price }}</div>
                  </div>
                </div>
              </div>
            </div>
            <div class="page" v-if="dataListTotal > params.page_size">
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
          <div class="noresult" v-if="dataList.length == 0">
            <img src="@/assets/images/noresult.png" alt="noresult">
            <p>暂无收藏</p>
          </div>
        </template>
      </div>
    </div>
    <!-- main box end -->
  </div>
</div>
<Foot />
</template>

<script setup>
defineOptions({ name: 'AccountCollectProduct' });
import Top from '@/components/Top.vue';
import TopNav from '@/components/account/TopNav.vue';
import LeftMenu from '@/components/account/LeftMenu.vue';
import Foot from '@/components/Foot.vue';
import { ref, onMounted } from 'vue';
import http from '@/utils/http';
import util from '@/utils/util';
import { ElMessage, ElLoading, ElMessageBox } from 'element-plus';
import { useCartStore } from '@/stores/cartStore';

const cartStore = useCartStore();
const dataListLoading = ref(true);
const dataList = ref([]);
const dataListTotal = ref(0);
const dataItemsLoading = ref(true);
const params = ref({
  page_size: 10,
  page: 1,
});

onMounted(() => {
  let urlParams = new URLSearchParams(window.location.search);
  if (urlParams.toString() != '') {
    for (const [key, value] of urlParams) {
      params.value[key] = value;
    }
  }
  getList();
});

const getInit = () => {
  dataListLoading.value = true;
  dataList.value = [];
  dataListTotal.value = 0;
  params.value.page = 1;
  getList();
};

const getList = () => {
  http.post('/product/getCollectProducts', params.value).then(res => {
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

const deleteCollect = (sku) => {
  ElMessageBox.confirm('确认移除收藏？').then(() => {
    const loading = ElLoading.service({ lock: true, background: 'rgba(0, 0, 0, 0.7)' });
      http.post('/product/deleteCollect', { sku: sku }).then(res => {
        if (res.code == 200) {
          const index = dataList.value.findIndex(item => item.sku === sku);
        dataList.value.splice(index, 1);
        } else {
          ElMessage.error(res.message || '操作失败');
        }
    }).finally(() => {
      loading.close();
    });
  }).catch(() => {});
}

const addCart = (sku) => {
  const loading = ElLoading.service({ lock: true, background: 'rgba(0, 0, 0, 0.7)' });
  http.post('/product/addCart', { sku: sku, quantity: 1 }).then((res) => {
    if (res.code == 200) {
      cartStore.getCount();
      ElMessage.success('添加购物成功');
    } else {
      ElMessage.error(res.message || '操作失败');
    }
  }).finally(() => {
    loading.close();
  });
}
</script>

<style scoped>
.collect-product {
	padding: 24px;
}
</style>

<template>
<Top />
<TopNav />
<div class="account clearfix realname_auth">
  <div class="container">
    <LeftMenu />
    <!-- main box start -->
    <div class="account_main">
      <div class="am_unify_box log_list">
        <div class="top">
          <span class="title">
            <router-link to="/account/wallet">我的钱包</router-link>
            > 钱包明细
          </span>
        </div>
        <template v-if="dataListLoading">
          <div class="loading">
            <img src="@/assets/images/loading.gif" alt="loading">
            <div class="txt">正在努力加载中，感谢您的等待</div>
          </div>
        </template>
        <template v-if="!dataListLoading">
          <template v-if="dataListTotal > 0">
            <div class="items_wrapper" v-loading="dataItemsLoading">
              <div class="items">
                <div class="item" v-for="item in dataList" :key="item.id">
                  <div class="info">
                    <div class="txt">{{ item.description }}</div>
                    <div class="ident">{{ item.ident == 'inc' ? '+' : '-' }}{{ item.price }}</div>
                  </div>
                  <div class="date">{{ item.created_at.substring(0, 16) }}</div>
                </div>
              </div>
            </div>
            <div class="page mt-20">
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
            <p>暂无记录</p>
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
defineOptions({ name: 'AccountWalletLogs' });
import Top from '@/components/Top.vue';
import TopNav from '@/components/account/TopNav.vue';
import LeftMenu from '@/components/account/LeftMenu.vue';
import Foot from '@/components/Foot.vue';
import { ref, onMounted } from 'vue';
import http from '@/utils/http';
import util from '@/utils/util';

const dataListLoading = ref(true);
const dataList = ref([]);
const dataListTotal = ref(0);
const dataItemsLoading = ref(true);
const params = ref({
  page_size: 15,
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
  http.post('/account/getWalletLogsPaginate', params.value).then(res => {
    dataListLoading.value = false;
    dataItemsLoading.value = false;
    dataListTotal.value = res.data.total;
    dataList.value = res.data.data;
  });
};

const pageChange = (page) => {
  dataItemsLoading.value = true;
  params.value.page = page;
  getList();
  util.updateUrl(params.value);
  window.scrollTo({ top: 0 });
}
</script>

<style scoped>
.items_wrapper {
  position: relative;
}
</style>

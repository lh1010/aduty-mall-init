<template>
<Top />
<TopNav />
<div class="account clearfix">
  <div class="container">
    <LeftMenu />
    <!-- main box start -->
    <div class="account_main opros">
      <div class="am_unify_box">
        <div class="top">
          <span class="title">我的订单</span>
        </div>
        <template v-if="dataListLoading">
          <div class="loading">
            <img src="@/assets/images/loading.gif" alt="loading">
            <div class="txt">正在努力加载中，感谢您的等待</div>
          </div>
        </template>
        <template v-if="!dataListLoading">
          <template v-if="dataList.length">
            <div class="items_ss" v-for="item in dataList" :key="item.id">
              <div class="items_top">
                <ul>
                  <li>订单号：{{ item.id }}</li>
                  <li>交易时间：{{ item.created_at }}</li>
                  <li class="more">
                    <router-link :to="'/account/order_show?id=' + item.id" target="_blank">订单详情</router-link>
                  </li>
                </ul>
              </div>
              <div class="items">
                <div class="item" v-for="snap in item.snaps" :key="snap.id">
                  <div class="cover">
                    <img class="lazy" :src="snap.cover" />
                  </div>
                  <div class="infobox">
                    <div class="name">
                      <router-link :to="'/product/show/' + snap.product_id + '?sku=' + snap.sku" target="_blank">{{ snap.name }}</router-link>
                    </div>
                    <div class="types" v-if="snap.specifications && snap.specifications.length > 0">
                      <div class="types_item" v-for="specn in snap.specifications" :key="specn.id">
                        {{ specn.specification_name }}-{{ specn.specification_option }}
                      </div>
                    </div>
                  </div>
                  <div class="price text-price">¥{{ item.total_price }}</div>
                </div>
              </div>
              <div class="items_foot">
                <div class="ofmain">
                  <div class="pricebox">
                    <span class="s1">合计：</span>
                    <span class="s2 text-price">¥</span>
                    <span class="s3 text-price">{{ item.total_price }}</span>
                  </div>
                  <div class="status">订单状态：{{ item.status_show }}</div>
                  <template v-if="item.status == 0">
                    <div class="btns">
                      <el-button type="primary" size="small" @click="payNow(item.id)">立即付款</el-button>
                    </div>
                    <div class="actions">
                      <a href="javascript:void(0);" @click="cancelOrder(item.id)">取消订单</a>
                    </div>
                  </template>
                  <template v-if="item.status == 20">
                    <div class="btns">
                      <el-button type="success" size="small" @click="receiveOrder(item.id)">确认收货</el-button>
                    </div>
                  </template>
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
defineOptions ({ name: 'AccountOrderList' });
import Top from '@/components/Top.vue';
import TopNav from '@/components/account/TopNav.vue';
import LeftMenu from '@/components/account/LeftMenu.vue';
import Foot from '@/components/Foot.vue';
import { ref, onMounted } from 'vue';
import http from '@/utils/http';
import util from '@/utils/util';
import { ElMessage, ElLoading, ElMessageBox } from 'element-plus';

const dataListLoading = ref(true);
const dataList = ref([]);
const dataListTotal = ref(0);
const params = ref({
  page_size: 10,
  page: 1,
  k: '',
});

onMounted(() => {
  let urlParams = new URLSearchParams(window.location.search);
  if (urlParams.toString() != '') {
    for (const [key, value] of urlParams) {
      params.value[key] = value;
    }
  }
  getList();
})

const getInit = () => {
  dataListLoading.value = true;
  dataList.value = [];
  dataListTotal.value = 0;
  params.value.page = 1;
  getList();
};

const getList = () => {
  http.post('/order/getList', params.value).then(res => {
    dataListLoading.value = false;
    dataListTotal.value = res.data.total;
    dataList.value = res.data.data;
  });
};

const pageChange = (page) => {
  // dataListLoading.value = true;
  params.value.page = page;
  getList(params);
  util.updateUrl(params.value);
  window.scrollTo({ top: 0 });
}

const payNow = (id) => {
  window.open('/checkout/pay?order_ids=' + id, '_blank');
}

const cancelOrder = (id) => {
  ElMessageBox.confirm('确认取消订单？').then(() => {
    let loading = ElLoading.service({ lock: true, background: 'rgba(0, 0, 0, 0.7)' });
    http.post('/order/cancelOrder', { order_id: id }).then(res => {
      if (res.code == 200) {
        let order = dataList.value.find(item => item.id === id);
        order.status = -10;
        order.status_show = '已取消';
        ElMessage.success('操作成功');
      } else {
        ElMessage.error(res.message || '操作失败');
      }
    }).finally(() => {
      loading.close();
    });
  }).catch(() => {});
}

const receiveOrder = (id) => {
  ElMessageBox.confirm('确定收货？').then(() => {
    let loading = ElLoading.service({ lock: true, background: 'rgba(0, 0, 0, 0.7)' });
    http.post('/order/receiveOrder', { order_id: id }).then(res => {
      if (res.code == 200) {
        let order = dataList.value.find(item => item.id === id);
        order.status = 30;
        order.status_show = '已完成';
        ElMessage.success('操作成功');
      } else {
        ElMessage.error(res.message || '操作失败');
      }
    }).finally(() => {
      loading.close();
    });
  }).catch(() => {});
}
</script>

<style lang="less" scoped>

</style>

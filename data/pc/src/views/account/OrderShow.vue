<template>
<Top />
<TopNav />
<div class="account clearfix">
  <div class="container">
    <LeftMenu />
    <!-- main box start -->
    <div class="account_main">
      <div class="am_unify_box order_show">
        <div class="top">
          <span class="title"><router-link to="/account/order_list">我的订单</router-link> > 详情</span>
        </div>
        <template v-if="loading">
          <div class="loading">
            <img src="@/assets/images/loading.gif" alt="loading">
            <div class="txt">正在努力加载中，感谢您的等待</div>
          </div>
        </template>
        <template v-if="!loading">
          <div class="order_flow">
            <li class="on">
              <span class="s1">买方付款</span>
              <span class="s2"></span>
              <span class="s3">1</span>
            </li>
            <li class="">
              <span class="s1">商家确认发货</span>
              <span class="s2"></span>
              <span class="s3">2</span>
            </li>
            <li class="">
              <span class="s1">买家确认收货</span>
              <span class="s2"></span>
              <span class="s3">3</span>
            </li>
            <li class="">
              <span class="s1">交易完成</span>
              <span class="s2"></span>
              <span class="s3">4</span>
            </li>
          </div>
          <div class="order_info">
            <div class="btop">订单信息</div>
            <div class="bd">
              <div class="cols">
                <div class="col">
                  <span class="label">订单编号：</span>
                  <span class="value">{{ order.id }}</span>
                </div>
                <div class="col">
                  <span class="label">订单金额：</span>
                  <span class="value text-price">{{ order.total_price }}</span>
                </div>
                <div class="col">
                  <span class="label">订单状态：</span>
                  <span class="value">{{ order.status_show }}</span>
                </div>
                <div class="col">
                  <span class="label">创建时间：</span>
                  <span class="value">{{ order.created_at }}</span>
                </div>
              </div>
              <div class="actionbox" v-if="[0, 20].includes(order.status)">
                <div class="btns" v-if="order.status === 0">
                  <el-button type="info" @click="cancelOrder">取消订单</el-button>
                  <el-button type="primary" @click="payNow">立即付款</el-button>
                </div>
                <div class="btns" v-if="order.status === 20">
                  <el-button type="success" @click="receiveOrder">确认收货</el-button>
                </div>
              </div>
            </div>
          </div>
          <div class="order_info">
            <div class="btop">收货人信息</div>
            <div class="bd">
              <div class="rows">
                <div class="row">
                  <span class="label">收货人：</span>
                  <span class="value">{{ order.name }}</span>
                </div>
                <div class="row">
                  <span class="label">地址：</span>
                  <span class="value">{{ order.province_name }} {{ order.city_name }} {{ order.district_name }} {{ order.detailed_address }}</span>
                </div>
                <div class="row">
                  <span class="label">手机：</span>
                  <span class="value">{{ order.phone }}</span>
                </div>
              </div>
            </div>
          </div>
          <div class="order_info order_snaps">
            <div class="btop">订单中的商品</div>
            <div class="bd">
              <div class="items">
                <div class="item" v-for="item in order.snaps" :key="item.id">
                  <div class="cover">
                    <img class="lazy" :src="item.cover" :alt="item.name">
                  </div>
                  <div class="infobox">
                    <div class="name">
                      <router-link :to="'/product/show/' + item.product_id + '?sku=' + item.sku" target="_blank">{{ item.name }}</router-link>
                    </div>
                    <div class="types" v-if=" item.specifications && item.specifications.length > 0 ">
                      <div class="types_item" v-for="specn in item.specifications" :key="specn.id">
                        {{ specn.specification_name }} - {{ specn.specification_option }}
                      </div>
                    </div>
                  </div>
                  <div class="price text-price">¥{{ item.total_price }}</div>
                </div>
              </div>
            </div>
          </div>
          <div class="order_info">
            <div class="btop">订单流程进度</div>
            <div class="bd">
              <div class="rows">
                <div class="row" v-for="log in order.logs" :key="log.id">
                  {{ log.created_at }} {{ log.content }}
                </div>
                <div class="row" v-if="![-10, 30].includes(order.status)">
                  ......
                </div>
              </div>
            </div>
          </div>
        </template>

      </div>
    </div>
  </div>
</div>
<Foot />
</template>

<script setup>
defineOptions ({ name: 'AccountOrderShow' });
import Top from '@/components/Top.vue';
import TopNav from '@/components/account/TopNav.vue';
import LeftMenu from '@/components/account/LeftMenu.vue';
import Foot from '@/components/Foot.vue';
import { ref, onMounted } from 'vue';
import http from '@/utils/http';
import { ElMessage, ElLoading, ElMessageBox } from 'element-plus';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();
const id = route.query.id;
const loading = ref(true);
const order = ref({});

onMounted(() => {
  getOrder();
});

const getOrder = () => {
  http.post('/order/getShow', { id }).then(res => {
    loading.value = false;
    order.value = res.data;
  })
}

const payNow = () => {
  // window.open('/checkout/pay?order_ids=' + order.value.id, '_blank');
  router.push('/checkout/pay?order_ids=' + order.value.id);
}

const cancelOrder = () => {
  ElMessageBox.confirm('确定取消订单？').then(() => {
    let loading = ElLoading.service({ lock: true, background: 'rgba(0, 0, 0, 0.7)' });
    http.post('/order/cancelOrder', { order_id: order.value.id }).then(res => {
      if (res.code == 200) {
        order.value.status = -10;
        order.value.status_show = '已取消';
        ElMessage.success('操作成功');
      } else {
        ElMessage.error(res.message || '操作失败');
      }
    }).finally(() => {
      loading.close();
    });
  }).catch(() => {});
}

const receiveOrder = () => {
  ElMessageBox.confirm('确定收货？').then(() => {
    let loading = ElLoading.service({ lock: true, background: 'rgba(0, 0, 0, 0.7)' });
    http.post('/order/receiveOrder', { order_id: order.value.id }).then(res => {
      if (res.code == 200) {
        order.value.status = 30;
        order.value.status_show = '已完成';
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

<style lang="css" scoped></style>

<template>
<Top />
<Head />
<Menu />
<div class="checkout">
  <div class="container">
    <div class="nav">
      <span class="on">当前位置：</span>
      <router-link href="/">首页</router-link>
      <span>></span>
      <span class="on">收银台</span>
    </div>
    <div class="paymain" v-loading="loading">
      <div class="pm_title">收银台</div>
      <div class="pm1">
        <div class="d1">结算总费用<span class="price text-price">{{ totalData.total_price }}</span>元</div>
        <div class="d2">您的可用余额：<span class="price text-price">{{ user.wallet }}</span>元</div>
      </div>
      <div class="pm_orders">
        <div class="stitle">订单信息</div>
        <div class="item" v-for="item in orders" :key="item.id">
          <span class="sp1">订单号:</span>
          <span class="sp2">{{ item.number }}</span>
          <router-link :to="`/account/order_show?id=${item.id}`" target="_blank">查看详情</router-link>
        </div>
      </div>
      <div class="payment">
        <div class="payment_top none"><span class="title">支付方式</span></div>
        <div class="payment_items">
          <div class="item" :class="{ on: paymentWay == 'alipay_pc' }" @click=" paymentWay = 'alipay_pc' ">
            <i class="iconfont"></i>
            <img src="@/assets/images/pay_02.jpg">
          </div>
          <div class="item" :class="{ on: paymentWay == 'weixinpay_native' }" @click=" paymentWay = 'weixinpay_native' ">
            <i class="iconfont"></i>
            <img src="@/assets/images/pay_01.jpg">
          </div>
          <div class="item" :class="{ on: paymentWay == 'wallet' }" @click=" paymentWay = 'wallet' ">
            <i class="iconfont"></i>
            <img src="@/assets/images/pay_03.png">
          </div>
        </div>
        <div class="payment_btns">
          <el-button type="primary" size="large" @click="pay">发起支付</el-button>
        </div>
      </div>
    </div>
  </div>
</div>
<Foot />
<el-dialog v-model="dialogVisible_weixinpay" title="微信扫码支付" width="300" :show-close="false" center>
  <div class="popup_weixinpay100">
    <div class="box">
      <img class="qrcode" :src="qrcode" alt="微信扫码支付">
      <div class="msg">请使用微信扫码进行支付</div>
    </div>
  </div>
  <template #footer>
    <div class="dialog-footer">
      <el-button type="primary" @click="dialogVisible_weixinpay = false; $router.push('/account/order_list')">已完成支付</el-button>
    </div>
  </template>
</el-dialog>
</template>

<script setup>
defineOptions({ name: 'CheckoutPay' });
import { ref, onMounted } from 'vue';
import http from '@/utils/http';
import Top from '@/components/Top.vue';
import Head from '@/components/Head.vue';
import Menu from '@/components/Menu.vue';
import Foot from '@/components/Foot.vue';
import { useRoute, useRouter } from 'vue-router';
import { ElMessage, ElLoading } from 'element-plus';

const route = useRoute();
const router = useRouter();
const order_ids = route.query.order_ids;
const loading = ref(true);
const orders = ref([]);
const totalData = ref({});
const user = ref({});

const paymentWay = ref('alipay_pc');
const dialogVisible_weixinpay = ref(false);
const qrcode = ref('');

onMounted(() => {
  getOrderPayData();
});

const getOrderPayData = () => {
  http.post('/order/getOrderPayData', { order_ids: order_ids }).then(res => {
    orders.value = res.data.orders;
    totalData.value = res.data.totalData;
    user.value = res.data.user;
  }).finally(() => {
    loading.value = false;
  });
};

const pay = () => {
  let loading = ElLoading.service({ lock: true, background: 'rgba(0, 0, 0, 0.7)' });
  http.post('/payment/pay_order', { order_ids: order_ids, payment_way: paymentWay.value }).then(res => {
    if (res.code == 200) {
      if (paymentWay.value == 'alipay_pc') {
        window.location.href = res.data.url;
      } else if (paymentWay.value == 'weixinpay_native') {
        qrcode.value = res.data.qrcode;
        dialogVisible_weixinpay.value = true;
      } else if (paymentWay.value == 'wallet') {
        ElMessage.success('支付成功');
        router.push('/account/order_list');
      }
    } else {
      ElMessage.error(res.message || '支付失败');
    }
  }).catch(err => {
    ElMessage.error(err.message || '支付失败');
  }).finally(() => {
    loading.close();
  });
};
</script>

<style lang="less" scoped>
@import '@/assets/style/checkout.css';
</style>

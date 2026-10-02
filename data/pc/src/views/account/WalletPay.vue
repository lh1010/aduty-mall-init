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
            > 钱包充值
          </span>
        </div>
        <div class="wallet_pay_box">
          <div>
            <el-input
              v-model="price"
              size="large"
              style="max-width: 360px"
              placeholder="请输入要充值的金额"
              @input="price = price.replace(/[^\d.]/g, '').replace(/(\..*)\./g, '$1').replace(/^(\d+\.\d{2}).*/, '$1')"
            >
              <template #prepend>充值金额</template>
              <template #append>元</template>
            </el-input>
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
            </div>
            <div class="payment_btns">
              <el-button type="primary" size="large" @click="pay">发起支付</el-button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- main box end -->
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
      <el-button type="primary" @click="dialogVisible_weixinpay = false; $router.push('/account/wallet')">已完成支付</el-button>
    </div>
  </template>
</el-dialog>
</template>

<script setup>
defineOptions({ name: 'AccountWalletLogs' });
import Top from '@/components/Top.vue';
import TopNav from '@/components/account/TopNav.vue';
import LeftMenu from '@/components/account/LeftMenu.vue';
import Foot from '@/components/Foot.vue';
import { ref, onMounted } from 'vue';
import http from '@/utils/http';
import { ElMessage, ElLoading } from 'element-plus';

const dataLoading = ref(true);
const loginUser = ref({});
const price = ref('');

const paymentWay = ref('alipay_pc');
const dialogVisible_weixinpay = ref(false);
const qrcode = ref('');

onMounted(() => {
  getLoginUser();
});

const getLoginUser = () => {
  http.post('/account/getLoginUser').then(res => {
    dataLoading.value = false;
    loginUser.value = res.data;
  });
};

const pay = () => {
  let loading = ElLoading.service({ lock: true, background: 'rgba(0, 0, 0, 0.7)' });
  http.post('/payment/pay_wallet', { price: price.value, payment_way: paymentWay.value }).then(res => {
    if (res.code == 200) {
      if (paymentWay.value == 'alipay_pc') {
        window.location.href = res.data.url;
      } else if (paymentWay.value == 'weixinpay_native') {
        qrcode.value = res.data.qrcode;
        dialogVisible_weixinpay.value = true;
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

<style scoped></style>

<template>
<Top />
<TopNav />
<div class="account clearfix realname_auth">
  <div class="container">
    <LeftMenu />
    <!-- main box start -->
    <div class="account_main">
      <div class="am_unify_box">
        <div class="top">
          <span class="title">我的钱包</span>
          <span class="action"><router-link to="/account/wallet_logs" class="text-link">查看明细</router-link></span>
        </div>
        <div class="wallet_box" v-loading="dataLoading">
          <div class="txt">
            <span>钱包余额</span>
            <span class="price">{{ loginUser?.wallet || 0 }}</span>
          </div>
          <div class="btns">
            <el-button type="success" @click="$router.push('/account/wallet_withdraw')">提现</el-button>
            <el-button type="primary" @click="$router.push('/account/wallet_pay')">充值</el-button>
          </div>
        </div>
      </div>
    </div>
    <!-- main box end -->
  </div>
</div>
<Foot />
</template>

<script setup>
defineOptions({ name: 'AccountWallet' });
import Top from '@/components/Top.vue';
import TopNav from '@/components/account/TopNav.vue';
import LeftMenu from '@/components/account/LeftMenu.vue';
import Foot from '@/components/Foot.vue';
import { ref, onMounted } from 'vue';
import http from '@/utils/http';

const dataLoading = ref(true);
const loginUser = ref(null);

onMounted(() => {
  getLoginUser();
})

const getLoginUser = () => {
  http.post('/account/getUser').then(res => {
    loginUser.value = res.data;
  }).finally(() => {
    dataLoading.value = false;
  });
};
</script>

<style scoped></style>

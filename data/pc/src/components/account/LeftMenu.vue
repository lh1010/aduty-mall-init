<template>
<div class="account_menu">
  <dl>
    <dt class="title"><a href="javascript:void(0);">账户设置</a></dt>
    <dd :class="{ on: route.path == '/account/user_info' }"><router-link to="/account/user_info">个人信息</router-link></dd>
    <dd :class="{ on: route.path == '/account/user_password' }"><router-link to="/account/user_password">修改密码</router-link></dd>
    <dd :class="{ on: route.path == '/account/user_contact' }"><router-link to="/account/user_contact">联系方式</router-link></dd>
    <dd :class="{ on: route.path == '/account/address_list' }"><router-link to="/account/address_list">收货地址</router-link></dd>
  </dl>
  <dl>
    <dt class="title"><a href="javascript:void(0);">交易中心</a></dt>
    <dd :class="{ on: route.path == '/account/order_list' }"><router-link to="/account/order_list">我的订单</router-link></dd>
  </dl>
  <dl>
    <dt class="title"><a href="javascript:void(0);">我的资产</a></dt>
    <dd :class="{ on: ['/account/wallet', '/account/wallet_pay', '/account/wallet_withdraw', '/account/wallet_logs'].includes(route.path) }">
      <router-link to="/account/wallet">我的钱包</router-link>
    </dd>
  </dl>
  <dl>
    <dt class="title"><a href="javascript:void(0);">身份认证</a></dt>
    <dd :class="{ on: route.path == '/account/realname_auth' }"><router-link to="/account/realname_auth">实名认证</router-link></dd>
    <dd :class="{ on: route.path == '/account/company_auth' }"><router-link to="/account/company_auth">企业认证</router-link></dd>
  </dl>
  <dl>
    <dt class="title"><a href="javascript:void(0);">我的收藏</a></dt>
    <dd :class="{ on: route.path == '/account/collect_product' }"><router-link to="/account/collect_product">收藏商品</router-link></dd>
  </dl>
</div>
</template>

<script setup>
defineOptions({ name: 'AccountLeftMenu' });
import { useRoute } from 'vue-router';
const route = useRoute();
</script>

<style scoped></style>

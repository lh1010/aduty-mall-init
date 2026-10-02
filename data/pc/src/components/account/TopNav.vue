<template>
<div class="account_top_nav">
  <div class="container">
    <div class="account_logo">
      <router-link to="/account/user_info">
        <img :src="configStore.data.pc.account_logo" v-if="configStore.data.pc && configStore.data.pc.account_logo">
      </router-link>
    </div>
    <div class="bd1">
      <router-link to="/account/user_info" class="a1">用户中心</router-link>
      <router-link to="/" class="a2">返回平台首页</router-link>
    </div>
    <ul class="navitems">
      <li class="li1"><router-link to="/account/user_info">首页</router-link></li>
      <li class="fold">
        <div class="dl">
          <div class="dt"><span>账户设置</span><i class="iconfont"></i></div>
          <div class="dd">
            <router-link to="/account/user_info"><span>个人信息</span></router-link>
            <router-link to="/account/user_password"><span>修改密码</span></router-link>
            <router-link to="/account/user_contact"><span>联系方式</span></router-link>
            <router-link to="/account/address_list"><span>收货地址</span></router-link>
            <router-link to="/account/realname_auth"><span>实名认证</span></router-link>
            <router-link to="/account/company_auth"><span>企业认证</span></router-link>
          </div>
        </div>
      </li>
      <li class="fold">
        <div class="dl">
          <div class="dt"><span>交易与资产</span><i class="iconfont"></i></div>
          <div class="dd">
            <router-link to="/account/order_list"><span>我的订单</span></router-link>
            <router-link to="/account/wallet"><span>我的钱包</span></router-link>
            <router-link to="/account/collect_product"><span>收藏的商品</span></router-link>
          </div>
        </div>
      </li>
    </ul>
  </div>
</div>
</template>

<script setup>
defineOptions({ name: 'AccountTopNav' });
import { useConfigStore } from '@/stores/configStore';

const configStore = useConfigStore();
</script>

<style>
@import '@/assets/style/account.css';
</style>

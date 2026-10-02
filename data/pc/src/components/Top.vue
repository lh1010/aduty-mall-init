<template>
<div class="header_top">
  <div class="container">
    <p class="left_title">免费开源可商用的PHP商城系统源码 ~</p>
    <ul class="right_nav">
      <template v-if="!userStore.isLogin">
        <li class="item login_box">
          <a href="javascript:void(0)" @click="userStore.showLoginDialog = true" v-if="!userStore.isLogin">请登录</a>
          <router-link to="/register" class="link">免费注册</router-link>
        </li>
      </template>
      <template v-else>
        <li class="item user_box">
          <a href="javascript:void();">{{ userStore.userInfo.nickname }}<i class="iconfont">&#xe799;</i></a>
          <div class="fold_items">
            <router-link to="/account/user_info" class="fold_item">我的信息</router-link>
            <router-link to="/account/order_list" class="fold_item">我的订单</router-link>
            <a @click.prevent="logout" class="fold_item">安全退出</a>
          </div>
        </li>
      </template>
      <li class="item" id="head_top_cart">
        <a href="javascript:void(0)" @click="userStore.showLoginDialog = true" v-if="!userStore.isLogin">购物车 <span class="text-price">{{ cartStore.count }}</span> 件</a>
        <router-link to="/cart" v-else>购物车 <span class="text-price">{{ cartStore.count }}</span> 件</router-link>
      </li>
      <li class="item">
        <router-link to="/help/show/100000">用户协议</router-link>
      </li>
      <li class="item">
        <router-link to="/help/show/100001">隐私协议</router-link>
      </li>
      <li class="item">
        <router-link to="/web/download" target="_blank">移动端</router-link>
      </li>
    </ul>
  </div>
</div>
<LoginDialog v-model="userStore.showLoginDialog" />
</template>

<script setup>
defineOptions({ name: 'Top' });
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useUserStore } from '@/stores/userStore';
import { useCartStore } from '@/stores/cartStore';
import LoginDialog from '@/components/LoginDialog.vue';

const router = useRouter();
const userStore = useUserStore();
const cartStore = useCartStore();

onMounted(() => {
  cartStore.getCount();
});

const logout = () => {
  userStore.logout();
  router.push('/');
};
</script>

<style lang="less" scoped></style>

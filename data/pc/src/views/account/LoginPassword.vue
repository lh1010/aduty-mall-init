<template>
<Top />
<Head />
<div class="account_login">
  <div class="container box">
    <div class="form_box">
      <form @submit.prevent="login" autocomplete="off">
        <div class="title">密码登录</div>
        <el-form-item>
          <el-input v-model="form.phone" placeholder="登录手机" clearable size="large"/>
        </el-form-item>
        <el-form-item>
          <el-input v-model="form.password" type="password" placeholder="登录密码" show-password size="large" @keyup.enter="login"/>
        </el-form-item>
        <div class="d2">
          登录/注册代表同意本平台
          <router-link class="link" to="/help/show/100000" target="_blank" rel="noopener noreferrer">用户协议</router-link>
          <router-link class="link" to="/help/show/100001" target="_blank" rel="noopener noreferrer">隐私协议</router-link>
        </div>
        <div class="dbtn">
          <el-button class="btn" type="primary" size="large" :loading="loginLoading" @click="login">立即登录</el-button>
        </div>
      </form>
      <div class="d1">
        <router-link to="/login" class="link">手机验证码登录</router-link>
        <router-link to="/register" class="link">立即注册</router-link>
      </div>
    </div>
  </div>
</div>
<Foot />
</template>

<script setup>
import { ref } from 'vue';
import Top from '@/components/Top.vue';
import Head from '@/components/Head.vue';
import Foot from '@/components/Foot.vue';
import http from '@/utils/http';
import { ElMessage, ElLoading } from 'element-plus';
import { useRouter } from 'vue-router';
const router = useRouter();
import { useUserStore } from '@/stores/userStore';
const userStore = useUserStore();

const form = ref({
  phone: '',
  password: ''
});
const loginLoading = ref(false);

const login = () => {
  if (loginLoading.value) return;
  const loading = ElLoading.service({ lock: true, background: 'rgba(0, 0, 0, 0.7)' });
  loginLoading.value = true;
  http.post('/account/login_password', form.value).then(res => {
    if (res.code == 200) {
      userStore.setUserData(res.data.token, res.data.user_info);
      ElMessage({
        message: '登录成功',
        type: 'success',
        duration: 1000,
        onClose: () => {
          // window.location.href = '/';
          router.replace('/');
        }
      });
    } else if (res.code == 400) {
      ElMessage({
        message: res.message,
        type: 'error',
      })
    } else {
      ElMessage({
        message: '登录失败',
        type: 'error',
      })
    }
  }).finally(() => {
    loading.close();
    loginLoading.value = false;
  });
};

</script>

<style lang="less" scoped>
@import '@/assets/style/account.css';
</style>

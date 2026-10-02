<template>
<div class="login">
  <div class="container box">
    <div class="pagetop">
      <router-link to="/">
        <img :src="configStore.data.pc.app_logo" v-if="configStore.data.pc && configStore.data.pc.app_logo" alt="login" />
      </router-link>
    </div>
    <div class="pagecon">
      <div class="tabs">
        <div class="tab on">用户注册</div>
      </div>
      <el-form>
        <el-form-item>
          <el-input v-model="form.nickname" placeholder="请输入昵称" clearable size="large"/>
        </el-form-item>
        <el-form-item>
          <el-input v-model="form.phone" placeholder="请输入手机号码" clearable size="large" maxlength="11"/>
        </el-form-item>
        <el-form-item>
          <div class="sms_code_box">
            <el-input v-model="form.code" placeholder="请输入验证码" clearable size="large" maxlength="6"/>
            <el-button size="large" :disabled="countdown > 0" @click="sendCode">
              {{ countdown > 0 ? countdown + 's后重新获取' : '获取验证码' }}
            </el-button>
          </div>
        </el-form-item>
        <el-form-item>
          <el-input v-model="form.password" type="password" placeholder="请输入登录密码" show-password size="large"/>
        </el-form-item>
        <el-form-item class="last_item">
          <el-input v-model="form.password_confirm" type="password" placeholder="请再次输入密码" show-password size="large" @keyup.enter="register"/>
        </el-form-item>
        <div class="agreement">
          <el-checkbox v-model="agreed" class="checkbox" />
          <span>注册代表同意本平台</span>
          <div class="links">
            <router-link class="link" to="/help/show/100000" target="_blank" rel="noopener noreferrer">用户协议</router-link>
            <router-link class="link" to="/help/show/100001" target="_blank" rel="noopener noreferrer">隐私协议</router-link>
          </div>
        </div>
        <div class="operation">
          <el-button class="btn" type="primary" size="large" :loading="registerLoading" @click="register">立即注册</el-button>
        </div>
        <div class="small_operation">
          <router-link to="/login" class="link">已有账号？去登录</router-link>
        </div>
      </el-form>
    </div>
    <div class="pagefoot">
      © {{ new Date().getFullYear() }}
      <router-link to="/" class="link">{{ configStore.data.pc?.app_name }}</router-link>
    </div>
  </div>
</div>
</template>

<script setup>
defineOptions({ name: 'Register' });
import { ref, watch } from 'vue';
import http from '@/utils/http';
import { ElMessage, ElLoading } from 'element-plus';
import { useRouter } from 'vue-router';
import { useConfigStore } from '@/stores/configStore';
import { useUserStore } from '@/stores/userStore';
import { useCartStore } from '@/stores/cartStore';

const router = useRouter();
const configStore = useConfigStore();
const userStore = useUserStore();

const form = ref({
  nickname: '',
  phone: '',
  code: '',
  password: '',
  password_confirm: ''
});
const registerLoading = ref(false);
const agreed = ref(true);
const countdown = ref(0);
let timer = null;

watch(() => configStore.data.app_name, (newName) => { document.title = '注册' + (newName ? ' ' + newName : ''); }, { immediate: true });

// 发送验证码
const sendCode = () => {
  if (!agreed.value) {
    ElMessage.warning('请先同意用户协议');
    return;
  }
  if (!form.value.phone) {
    ElMessage.warning('请输入手机号码');
    return;
  }
  if (!/^1\d{10}$/.test(form.value.phone)) {
    ElMessage.error('请输入正确的手机号码');
    return;
  }
  const loading = ElLoading.service({ lock: true, background: 'rgba(0, 0, 0, 0.7)' });
  http.post('/account/sendCode', { phone: form.value.phone }).then(res => {
    if (res.code == 200) {
      ElMessage.success('验证码已发送');
      countdown.value = 60;
      timer = setInterval(() => {
        countdown.value--;
        if (countdown.value <= 0) {
          clearInterval(timer);
        }
      }, 1000);
    } else {
      ElMessage.error(res.message || '发送失败');
    }
  }).finally(() => {
    loading.close();
  });
};

// 注册
const register = () => {
  if (registerLoading.value) return;
  if (!agreed.value) {
    ElMessage.error('请先同意用户协议');
    return;
  }
  if (!form.value.nickname) {
    ElMessage.error('请输入昵称');
    return;
  }
  if (!form.value.phone) {
    ElMessage.error('请输入手机号码');
    return;
  }
  if (!/^1\d{10}$/.test(form.value.phone)) {
    ElMessage.error('请输入正确的手机号码');
    return;
  }
  if (!form.value.code) {
    ElMessage.error('请输入验证码');
    return;
  }
  if (!form.value.password) {
    ElMessage.error('请输入登录密码');
    return;
  }
  if (form.value.password !== form.value.password_confirm) {
    ElMessage.error('两次密码输入不一致');
    return;
  }
  registerLoading.value = true;
  const loading = ElLoading.service({ lock: true, background: 'rgba(0, 0, 0, 0.7)' });
  http.post('/account/register', form.value).then(res => {
    if (res.code == 200) {
      userStore.setUserData(res.data.token, res.data.user_info);
      // 刷新购物车数量
      const cartStore = useCartStore();
      cartStore.getCount();
      ElMessage({
        message: '注册成功',
        type: 'success',
        duration: 1000,
        onClose: () => {
          router.replace('/');
        }
      });
    } else {
      ElMessage.error(res.message || '注册失败');
    }
  }).finally(() => {
    loading.close();
    registerLoading.value = false;
  });
};
</script>

<style scoped>
@import '@/assets/style/account.css';
</style>

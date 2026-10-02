<template>
<el-dialog v-model="visible" width="380px" :close-on-click-modal="false">
  <div class="login_dialog">
    <div class="tabs">
      <div class="tab" :class="{ on: type == 'password' }" @click="setType('password')">密码登录</div>
      <div class="tab" :class="{ on: type == 'sms' }" @click="setType('sms')">短信登录</div>
    </div>
    <template v-if="type == 'password'">
      <el-form label-width="0">
        <el-form-item>
          <el-input v-model="form.phone" placeholder="登录手机" clearable size="large"/>
        </el-form-item>
        <el-form-item class="item_password">
          <el-input v-model="form.password" type="password" placeholder="登录密码" show-password size="large" @keyup.enter="login"/>
        </el-form-item>
        <div class="agreement">
          <el-checkbox v-model="agreed" class="checkbox" />
          <span>登录/注册代表同意本平台</span>
          <div class="links">
            <router-link class="link" to="/help/show/100000" target="_blank" rel="noopener noreferrer">用户协议</router-link>
            <router-link class="link" to="/help/show/100001" target="_blank" rel="noopener noreferrer">隐私协议</router-link>
          </div>
        </div>
        <div class="operation">
          <el-button class="btn" type="primary" size="large" :loading="loginLoading" @click="login">立即登录</el-button>
        </div>
        <div class="small_operation">
          <a href="javascript:void(0)" @click="setType('sms')" class="link">短信验证码登录</a>
          <router-link to="/register" class="link" @click="close">立即注册</router-link>
        </div>
      </el-form>
    </template>
    <template v-if="type == 'sms'">
      <el-form label-width="0">
        <el-form-item>
          <el-input v-model="smsForm.phone" placeholder="手机号码" clearable size="large" maxlength="11"/>
        </el-form-item>
        <el-form-item class="item_password">
          <div class="sms_code_box">
            <el-input v-model="smsForm.code" placeholder="验证码" clearable size="large" maxlength="6" @keyup.enter="login"/>
            <el-button size="large" :disabled="countdown > 0" @click="sendCode">
              {{ countdown > 0 ? countdown + 's后重新发送' : '获取验证码' }}
            </el-button>
          </div>
        </el-form-item>
        <div class="agreement">
          <el-checkbox v-model="agreed" class="checkbox" />
          <span>登录/注册代表同意本平台</span>
          <div class="links">
            <router-link class="link" to="/help/show/100000" target="_blank" rel="noopener noreferrer">用户协议</router-link>
            <router-link class="link" to="/help/show/100001" target="_blank" rel="noopener noreferrer">隐私协议</router-link>
          </div>
        </div>
        <div class="operation">
          <el-button class="btn" type="primary" size="large" :loading="loginLoading" @click="login">立即登录</el-button>
        </div>
        <div class="small_operation">
          <a href="javascript:void(0)" @click="setType('password')" class="link">密码登录</a>
          <router-link to="/register" class="link" @click="close">立即注册</router-link>
        </div>
      </el-form>
    </template>
  </div>
</el-dialog>
</template>

<script setup>
import { ref, watch } from 'vue';
import http from '@/utils/http';
import { ElMessage, ElLoading } from 'element-plus';
import { useRouter } from 'vue-router';
import { useUserStore } from '@/stores/userStore';
import { useConfigStore } from '@/stores/configStore';
import { useCartStore } from '@/stores/cartStore';

const emit = defineEmits(['success']);

const visible = defineModel();
const router = useRouter();
const userStore = useUserStore();
const configStore = useConfigStore();

const form = ref({
  phone: '',
  password: ''
});
const smsForm = ref({
  phone: '',
  code: ''
});
const loginLoading = ref(false);
const agreed = ref(true);
const type = ref('password');
const countdown = ref(0);
let timer = null;

const setType = (value) => {
  type.value = value;
};

// 关闭弹窗时重置数据
watch(visible, (val) => {
  if (!val) {
    form.value = { phone: '', password: '' };
    smsForm.value = { phone: '', code: '' };
    type.value = 'password';
    countdown.value = 0;
    if (timer) {
      clearInterval(timer);
      timer = null;
    }
  }
});

// 关闭弹窗
const close = () => {
  visible.value = false;
};

// 发送验证码
const sendCode = () => {
  if (!agreed.value) {
    ElMessage.error('请先同意用户协议');
    return;
  }
  if (!smsForm.value.phone) {
    ElMessage.error('请输入手机号码');
    return;
  }
  if (!/^1\d{10}$/.test(smsForm.value.phone)) {
    ElMessage.error('请输入正确的手机号码');
    return;
  }
  const loading = ElLoading.service({ lock: true, background: 'rgba(0, 0, 0, 0.7)' });
  http.post('/account/sendCode', { phone: smsForm.value.phone }).then(res => {
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

// 登录
const login = () => {
  if (loginLoading.value) return;
  if (!agreed.value) {
    ElMessage.error('请先同意用户协议');
    return;
  }
  let url = '';
  let data = {};
  if (type.value == 'password') {
    if (!form.value.phone) {
      ElMessage.error('请输入手机号码');
      return;
    }
    if (!/^1\d{10}$/.test(form.value.phone)) {
      ElMessage.error('请输入正确的手机号码');
      return;
    }
    if (!form.value.password) {
      ElMessage.error('请输入登录密码');
      return;
    }
    url = '/account/login_password';
    data = form.value;
  } else if (type.value == 'sms') {
    if (!smsForm.value.phone) {
      ElMessage.error('请输入手机号码');
      return;
    }
    if (!/^1\d{10}$/.test(smsForm.value.phone)) {
      ElMessage.error('请输入正确的手机号码');
      return;
    }
    if (!smsForm.value.code) {
      ElMessage.error('请输入验证码');
      return;
    }
    url = '/account/login';
    data = smsForm.value;
  }
  loginLoading.value = true;
  const loading = ElLoading.service({ lock: true, background: 'rgba(0, 0, 0, 0.7)' });
  http.post(url, data).then(res => {
    if (res.code == 200) {
      userStore.setUserData(res.data.token, res.data.user_info);
      // 刷新购物车数量
      const cartStore = useCartStore();
      cartStore.getCount();
      ElMessage.success('登录成功');
      visible.value = false;
      emit('success', res.data);
    } else {
      ElMessage.error(res.message || '登录失败');
    }
  }).finally(() => {
    loading.close();
    loginLoading.value = false;
  });
};
</script>

<style scoped>
@import '@/assets/style/account.css';
</style>

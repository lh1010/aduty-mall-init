<template>
<Top />
<TopNav />
<div class="account clearfix">
  <div class="container">
    <LeftMenu />
    <!-- main box start -->
    <div class="account_main">
      <div class="am_unify_box">
        <div class="top_nav">
          <router-link class="item" to="/account/user_info">个人信息</router-link>
          <router-link class="item" to="/account/user_password">修改密码</router-link>
          <router-link class="item on" to="/account/user_contact">联系方式</router-link>
        </div>
        <el-form :model="form" label-width="auto" v-loading="loading">
          <el-form-item label="微信">
            <el-input v-model="form.weixin" placeholder="请输入微信" />
          </el-form-item>
          <el-form-item label="手机">
            <el-input v-model="form.phone" placeholder="请输入手机" />
          </el-form-item>
          <el-form-item label="Q Q">
            <el-input v-model="form.qq" placeholder="请输入Q Q" />
          </el-form-item>
          <el-form-item label="电话">
            <el-input v-model="form.telphone" placeholder="请输入电话" />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" @click="onSubmit">更新信息</el-button>
          </el-form-item>
        </el-form>
      </div>
    </div>
    <!-- main box end -->
  </div>
</div>
<Foot />
</template>

<script setup>
defineOptions({ name: 'AccountUserContact' });
import Top from '@/components/Top.vue';
import TopNav from '@/components/account/TopNav.vue';
import LeftMenu from '@/components/account/LeftMenu.vue';
import Foot from '@/components/Foot.vue';
import { ref, onMounted } from 'vue';
import http from '@/utils/http';
import { ElMessage, ElLoading } from 'element-plus';

const loading = ref(true);
const loginUser = ref(null);
const form = ref({
  weixin: '',
  phone: '',
  qq: '',
  telphone: '',
});

onMounted(() => {
  getLoginUser();
});

const getLoginUser = async () => {
  http.post('/account/getUser').then((res) => {
    loginUser.value = { ...res.data };
    Object.assign(form.value, loginUser.value.contact);
  }).finally(() => {
    loading.value = false;
  });
};

const onSubmit = () => {
  let loading = ElLoading.service({ lock: true, background: 'rgba(0, 0, 0, 0.7)' });
  http.post('/account/updateUserContact', form.value).then((res) => {
    if (res.code == 200) {
      ElMessage.success('更新成功');
    } else {
      ElMessage.error(res.message || '更新失败');
    }
  }).finally(() => {
    loading.close();
  });
};
</script>

<style scoped></style>

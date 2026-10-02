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
          <router-link class="item on" to="/account/user_password">修改密码</router-link>
          <router-link class="item" to="/account/user_contact">联系方式</router-link>
        </div>
        <el-form :model="form" ref="formRef" label-width="auto" v-loading="loading">
          <template v-if="loginUser?.password">
            <el-form-item label="旧密码" prop="password_old">
              <el-input v-model="form.password_old" type="password" placeholder="请输入旧密码" />
            </el-form-item>
            <el-form-item label="新密码" prop="password">
              <el-input v-model="form.password" type="password" placeholder="请输入新密码" />
            </el-form-item>
            <el-form-item label="确认新密码" prop="password_confirm">
              <el-input v-model="form.password_confirm" type="password" placeholder="请确认新密码" />
            </el-form-item>
          </template>
          <template v-if="!loginUser?.password">
            <el-form-item label="登录密码" prop="password">
              <el-input v-model="form.password" type="password" placeholder="请输入登录密码" />
            </el-form-item>
            <el-form-item label="确认新密码" prop="password_confirm">
              <el-input v-model="form.password_confirm" type="password" placeholder="请确认新密码" />
            </el-form-item>
          </template>
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
defineOptions({ name: 'AccountUserPassword' });
import Top from '@/components/Top.vue';
import TopNav from '@/components/account/TopNav.vue';
import LeftMenu from '@/components/account/LeftMenu.vue';
import Foot from '@/components/Foot.vue';
import { ref, onMounted } from 'vue';
import http from '@/utils/http';
import { ElMessage, ElLoading } from 'element-plus';

const loading = ref(true);
const loginUser = ref(null);
const formRef = ref(null);
const form = ref({
  password_old: '',
  password: '',
  password_confirm: '',
});

onMounted(() => {
  getLoginUser();
});

const getLoginUser = async () => {
  http.post('/account/getUser').then((res) => {
    loginUser.value = { ...res.data };
  }).finally(() => {
    loading.value = false;
  });
};

const onSubmit = () => {
  let loading = ElLoading.service({ lock: true, background: 'rgba(0, 0, 0, 0.7)' });
  http.post('/account/updateUserPassword', form.value).then((res) => {
    if (res.code == 200) {
      getLoginUser();
      formRef.value?.resetFields();
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

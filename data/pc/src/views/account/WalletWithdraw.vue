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
          <span class="title"><router-link to="/account/wallet">我的钱包</router-link> > 余额提现</span>
          <span class="action"><router-link to="/account/withdrawal_logs" class="text-link">提现记录</router-link></span>
        </div>
        <div class="wallet_pay_box">
          <el-form :model="form" ref="formRef" :rules="rules" label-width="auto" v-loading="loading">
            <el-form-item label="钱包余额">
              {{ loginUser?.wallet || 0 }} 元
            </el-form-item>
            <el-form-item label="提现金额" prop="price">
              <el-input
                v-model="form.price"
                style="max-width: 360px"
                placeholder="请输入要提现的金额"
                @input="form.price = form.price.replace(/[^\d.]/g, '').replace(/(\..*)\./g, '$1').replace(/^(\d+\.\d{2}).*/, '$1')"
              >
                <template #append>元</template>
              </el-input>
            </el-form-item>
            <el-form-item label="支付宝账号" prop="alipay_account">
              <el-input v-model="form.alipay_account" placeholder="请输入支付宝账号" />
            </el-form-item>
            <el-form-item label="账号名字" prop="alipay_name">
              <el-input v-model="form.alipay_name" placeholder="请输入账号名字" />
            </el-form-item>
            <el-form-item>
              <el-button type="primary" @click="onSubmit">提交信息</el-button>
            </el-form-item>
          </el-form>
          <div class="wallet_withdraw_info">
            <div class="stitle">提现注意事项</div>
            <div class="item" v-if="configStore.data.withdrawal.rate > 0">提现手续费：{{ configStore.data.withdrawal.rate * 100 }}%</div>
            <div class="item" v-if="configStore.data.withdrawal.min > 0">最小提现金额：{{ configStore.data.withdrawal.min }}元</div>
            <div class="item" v-if="configStore.data.withdrawal.max > 0">最大提现金额：{{ configStore.data.withdrawal.max }}元</div>
            <div class="item" v-if="configStore.data.withdrawal.today_count > 0">每天可提现次数：{{ configStore.data.withdrawal.today_count }}次</div>
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
defineOptions({ name: 'AccountWalletLogs' });
import Top from '@/components/Top.vue';
import TopNav from '@/components/account/TopNav.vue';
import LeftMenu from '@/components/account/LeftMenu.vue';
import Foot from '@/components/Foot.vue';
import { ref, onMounted } from 'vue';
import http from '@/utils/http';
import { ElMessage, ElLoading, ElMessageBox } from 'element-plus';
import { useConfigStore } from '@/stores/configStore';

const configStore = useConfigStore();
const loading = ref(true);
const loginUser = ref(null);
const formRef = ref(null);
const form = ref({
  price: '',
  alipay_account: '',
  alipay_name: '',
});
const rules = {
  price: [
    { required: true, message: '请输入提现金额', trigger: 'blur' },
    { pattern: /^\d+(\.\d{1,2})?$/, message: '请输入正确的提现金额', trigger: 'blur' },
  ],
  alipay_account: [
    { required: true, message: '请输入支付宝账号', trigger: 'blur' },
  ],
  alipay_name: [
    { required: true, message: '请输入账号名字', trigger: 'blur' },
  ],
};

onMounted(() => {
  getLoginUser();
});

const getLoginUser = () => {
  http.post('/account/getLoginUser').then(res => {
    loading.value = false;
    loginUser.value = res.data;
  });
};

const onSubmit = () => {
  formRef.value.validate((valid) => {
    if (!valid) return;
    ElMessageBox.confirm('确认提交？').then(() => {
      const loading = ElLoading.service({ lock: true, background: 'rgba(0, 0, 0, 0.7)' });
      http.post('/account/walletWithdraw', form.value).then((res) => {
        if (res.code == 200) {
          ElMessage.success('提交成功');
          getLoginUser();
        } else {
          ElMessage.error(res.message || '提交失败');
        }
      }).finally(() => {
        loading.close();
      });
    }).catch(() => {});
  });
};
</script>

<style scoped></style>

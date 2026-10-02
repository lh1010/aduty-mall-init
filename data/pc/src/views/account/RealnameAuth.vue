<template>
<Top />
<TopNav />
<div class="account clearfix realname_auth">
  <div class="container">
    <LeftMenu />
    <!-- main box start -->
    <div class="account_main">
      <div class="am_unify_box">
        <div class="top_nav">
          <router-link class="item on" to="/account/realname_auth">实名认证</router-link>
          <router-link class="item" to="/account/company_auth">企业认证</router-link>
        </div>
        <template v-if="!dataLoading">
          <template v-if="loginUser.realname_auth == 0">
            <el-form :model="form" ref="formRef" :rules="rules" label-width="auto">
              <el-form-item label="真实姓名" prop="realname">
                <el-input v-model="form.realname" placeholder="请输入真实姓名" />
              </el-form-item>
              <el-form-item label="身份证号" prop="idcard">
                <el-input v-model="form.idcard" placeholder="请输入身份证号" />
              </el-form-item>
              <el-form-item label="身份证照片" prop="idcard_img1">
                <div class="idcard_images">
                  <div class="items">
                    <!-- 正面照 -->
                    <el-upload
                      :show-file-list="false"
                      :http-request="handleUpload"
                      :data="{ field: 'idcard_img1' }"
                      v-loading="upLoading.idcard_img1"
                    >
                      <div class="item item1">
                        <div class="img">
                          <img v-if="form.idcard_img1" :src="form.idcard_img1" />
                          <img v-else src="@/assets/images/identity_card1.png" />
                        </div>
                        <div class="btn">{{ form.idcard_img1 ? '重新上传' : '上传正面照' }}</div>
                      </div>
                    </el-upload>
                    <!-- 反面照 -->
                    <el-upload
                      :show-file-list="false"
                      :http-request="handleUpload"
                      :data="{ field: 'idcard_img2' }"
                      v-loading="upLoading.idcard_img2"
                    >
                      <div class="item item2">
                        <div class="img">
                          <img v-if="form.idcard_img2" :src="form.idcard_img2" />
                          <img v-else src="@/assets/images/identity_card2.png" />
                        </div>
                        <div class="btn">{{ form.idcard_img2 ? '重新上传' : '上传反面照' }}</div>
                      </div>
                    </el-upload>
                  </div>
                </div>
              </el-form-item>
              <el-form-item>
                <el-button type="primary" @click="onSubmit">提交认证</el-button>
              </el-form-item>
            </el-form>
          </template>
          <template v-else-if="loginUser.realname_auth == 1">
            <div class="audit_result">
              <img src="@/assets/images/审核中.png">
              <p class="message">信息审核中</p>
            </div>
          </template>
          <template v-else-if="loginUser.realname_auth == 2">
            <div class="audit_result">
              <img src="@/assets/images/审核失败.png">
              <p class="message">失败原因：{{ loginUser.realname_auth_log?.message || '无' }}</p>
              <div class="btns">
                <el-button type="primary" @click="resetAuth">重新审核</el-button>
              </div>
            </div>
          </template>
          <template v-else-if="loginUser.realname_auth == 3">
            <div class="audit_result">
              <img src="@/assets/images/已审核.png">
              <p class="message">实名认证成功</p>
            </div>
          </template>
        </template>
      </div>
    </div>
    <!-- main box end -->
  </div>
</div>
<Foot />
</template>

<script setup>
defineOptions({ name: 'AccountRealnameAuth' });
import Top from '@/components/Top.vue';
import TopNav from '@/components/account/TopNav.vue';
import LeftMenu from '@/components/account/LeftMenu.vue';
import Foot from '@/components/Foot.vue';
import { ref, onMounted } from 'vue';
import http from '@/utils/http';
import { ElMessage, ElLoading, ElMessageBox } from 'element-plus';

const dataLoading = ref(true);
const loginUser = ref(null);
const formRef = ref(null);
const form = ref({
  realname: '',
  idcard: '',
  idcard_img1: '',
  idcard_img2: '',
});
// 身份证照片上传验证
const validateIdcard = (rule, value, callback) => {
  if (!value) {
    callback(new Error('请上传身份证正面照'));
  } else if (!form.value.idcard_img2) {
    callback(new Error('请上传身份证反面照'));
  } else {
    callback();
  }
};
const rules = {
  realname: [
    { required: true, message: '请输入真实姓名', trigger: 'blur' },
  ],
  idcard: [
    { required: true, message: '请输入身份证号', trigger: 'blur' },
    // { pattern: /^\d{17}[\dXx]$/, message: '请输入正确的身份证号', trigger: 'blur' },
  ],
  idcard_img1: [
    { required: true, validator: validateIdcard, trigger: 'change' },
  ],
};

onMounted(() => {
  getLoginUser();
});

const getLoginUser = () => {
  const loading = ElLoading.service({ lock: true, background: 'rgba(0, 0, 0, 0.7)' });
  http.post('/account/getUser').then(res => {
    loginUser.value = res.data;
  }).finally(() => {
    loading.close();
    dataLoading.value = false;
  });
};

// 上传loading状态
const upLoading = ref({ idcard_img1: false, idcard_img2: false });

// 上传前校验
const beforeUpload = (file) => {
  // const isImage = ['image/jpeg', 'image/png', 'image/webp'].includes(file.type);
  // const isLt5M = file.size / 1024 / 1024 < 5;
  // if (!isImage) {
  //   ElMessage.error('只能上传 JPG/PNG/WebP 格式的图片！');
  //   return false;
  // }
  // if (!isLt5M) {
  //   ElMessage.error('图片大小不能超过 5MB！');
  //   return false;
  // }
  return true;
};

// 执行上传
const handleUpload = (options) => {
  const field = options.data.field;
  upLoading.value[field] = true;
  http.post('/upload', { file: options.file }, { headers: { 'Content-Type': 'multipart/form-data' } }).then((res) => {
    if (res.code == 200) {
      form.value[field] = res.data.url;
      // 上传成功后清除身份证照片的错误提示
      formRef.value?.clearValidate('idcard_img1');
      ElMessage.success('上传成功');
    } else {
      ElMessage.error(res.message || '上传失败');
    }
  }).finally(() => {
    upLoading.value[field] = false;
  });
};

// 提交认证
const onSubmit = () => {
  formRef.value.validate((valid) => {
    if (!valid) return;
    const loading = ElLoading.service({ lock: true, background: 'rgba(0, 0, 0, 0.7)' });
    http.post('/account/realnameAuth', form.value).then((res) => {
      if (res.code == 200) {
        ElMessage.success('提交成功');
        getLoginUser();
      } else {
        ElMessage.error(res.message || '提交失败');
      }
    }).finally(() => {
      loading.close();
    });
  });
};

const resetAuth = () => {
  ElMessageBox.confirm('确认重新审核？').then(() => {
    const loading = ElLoading.service({ lock: true, background: 'rgba(0, 0, 0, 0.7)' });
    http.post('/account/realnameAuthReset', form.value).then((res) => {
      if (res.code == 200) {
        ElMessage.success('操作成功');
        getLoginUser();
      } else {
        ElMessage.error(res.message || '提交失败');
      }
    }).finally(() => {
      loading.close();
    });
  }).catch(() => {});
}
</script>

<style scoped></style>

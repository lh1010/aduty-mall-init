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
          <router-link class="item" to="/account/realname_auth">实名认证</router-link>
          <router-link class="item on" to="/account/company_auth">企业认证</router-link>
        </div>
        <template v-if="!dataLoading">
          <template v-if="loginUser.company_auth == 0">
            <el-form :model="form" ref="formRef" :rules="rules" label-width="auto">
              <el-form-item label="企业全称" prop="company_name">
                <el-input v-model="form.company_name" placeholder="请输入企业全称" />
              </el-form-item>
              <el-form-item label="信用代码" prop="social_credit_code">
                <el-input v-model="form.social_credit_code" placeholder="请输入信用代码" />
              </el-form-item>
              <el-form-item label="营业执照" prop="business_license">
                <!-- 上传容器 start -->
                <div class="up">
                  <div class="up-box" v-loading="upLoading">
                    <el-upload
                      ref="uploadRef"
                      class="up-trigger"
                      :show-file-list="false"
                      :http-request="handleUp"
                      :before-upload="beforeUp"
                      accept="image/jpeg,image/png,image/gif,image/webp"
                    >
                      <!-- 图片预览 已上传时显示图片 -->
                      <img v-if="form.business_license" :src="form.business_license" class="up-preview" />
                      <!-- 上传触发区域 无图片时显示加号图标 -->
                      <el-icon v-else class="up-icon"><Plus /></el-icon>
                    </el-upload>
                    <!-- 悬浮遮罩 鼠标移入时显示替换/移除按钮 -->
                    <div v-if="form.business_license" class="up-mask">
                      <div class="up-mask-item" @click.stop="handleReplaceUpImg">
                        <el-icon><Edit /></el-icon>
                        <span>替换</span>
                      </div>
                      <div class="up-mask-item" @click.stop="handleRemoveUpImg">
                        <el-icon><Delete /></el-icon>
                        <span>移除</span>
                      </div>
                    </div>
                  </div>
                  <!-- <div class="up-side">
                    <span class="up-tip">支持 JPG/PNG/GIF/WebP 格式，大小不超过 2MB</span>
                  </div> -->
                </div>
                <!-- 上传容器 end -->
              </el-form-item>
              <el-form-item>
                <el-button type="primary" @click="onSubmit">提交认证</el-button>
              </el-form-item>
            </el-form>
          </template>
          <template v-else-if="loginUser.company_auth == 1">
            <div class="audit_result">
              <img src="@/assets/images/审核中.png">
              <p class="message">信息审核中</p>
            </div>
          </template>
          <template v-else-if="loginUser.company_auth == 2">
            <div class="audit_result">
              <img src="@/assets/images/审核失败.png">
              <p class="message">失败原因：{{ loginUser.company_auth_log?.message || '无失败原因' }}</p>
              <div class="btns">
                <el-button type="primary" @click="resetAuth">重新审核</el-button>
              </div>
            </div>
          </template>
          <template v-else-if="loginUser.company_auth == 3">
            <div class="audit_result">
              <img src="@/assets/images/已审核.png">
              <p class="message">已成功企业认证</p>
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
  company_name: '',
  social_credit_code: '',
  business_license: '',
});
const rules = {
  company_name: [
    { required: true, message: '请输入企业全称', trigger: 'blur' },
  ],
  social_credit_code: [
    { required: true, message: '请输入信用代码', trigger: 'blur' },
  ],
  business_license: [
    { required: true, message: '请上传营业执照', trigger: 'change' },
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
const upLoading = ref(false);

// 上传前校验
const beforeUp = (file) => {
  // const isImage = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'].includes(file.type);
  // const isLt2M = file.size / 1024 / 1024 < 2;
  // if (!isImage) {
  //   ElMessage.error('头像只能上传 JPG/PNG/GIF/WebP 格式的图片！');
  //   return false;
  // }
  // if (!isLt2M) {
  //   ElMessage.error('头像大小不能超过 2MB！');
  //   return false;
  // }
  return true;
};

// 执行上传
const handleUp = async (options) => {
  upLoading.value = true;
  http.post('/upload', { file: options.file }, {headers: {'Content-Type': 'multipart/form-data'}}).then((res) => {
    if (res.code == 200) {
      form.value.business_license = res.data.url;
      // 上传成功后清除营业执照的错误提示
      formRef.value?.clearValidate('business_license');
    } else {
      ElMessage.error(res.message || '头像上传失败');
    }
  }).finally(() => {
    upLoading.value = false;
  });
};

const uploadRef = ref(null);

// 替换图片
const handleReplaceUpImg = () => {
  uploadRef.value?.$el.querySelector('input[type="file"]')?.click();
};

// 移除图片
const handleRemoveUpImg = () => {
  form.value.business_license = '';
};

// 提交认证
const onSubmit = () => {
  formRef.value.validate((valid) => {
    if (!valid) return;
    const loading = ElLoading.service({ lock: true, background: 'rgba(0, 0, 0, 0.7)' });
    http.post('/account/companyAuth', form.value).then((res) => {
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
    http.post('/account/companyAuthReset', form.value).then((res) => {
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

<style scoped>
.up {
  display: flex;
  align-items: center;
}
.up-box {
  position: relative;
  display: inline-block;
}
.up-trigger {
  display: flex;
  align-items: center;
}
.up-icon,
.up-preview {
  width: 100px;
  height: 100px;
  border: 1px dashed #d9d9d9;
  border-radius: 2px;
  padding: 4px;
}
.up-icon {
  font-size: 28px;
  color: #8c939d;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: border-color 0.3s;
}
.up-icon:hover {
  border-color: #409eff;
}
.up-preview {
  object-fit: cover;
  cursor: pointer;
  transition: opacity 0.3s;
}
.up-preview:hover {
  opacity: 0.8;
}
.up-mask {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.55);
  border-radius: 2px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  opacity: 0;
  transition: opacity 0.3s;
}
.up-box:hover .up-mask {
  opacity: 1;
}
.up-mask-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2px;
  color: #fff;
  font-size: 10px;
  cursor: pointer;
}
.up-mask-item .el-icon {
  font-size: 16px;
}
.up-side {
  display: flex;
  align-items: center;
  margin-left: 10px;
}
.up-tip {
  font-size: 12px;
  color: #999;
}
</style>

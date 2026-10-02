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
          <router-link class="item on" to="/account/user_info">个人信息</router-link>
          <router-link class="item" to="/account/user_password">修改密码</router-link>
          <router-link class="item" to="/account/user_contact">联系方式</router-link>
        </div>
        <el-form :model="form" ref="formRef" :rules="rules" label-width="auto" v-loading="loading">
          <el-form-item label="用户ID">
            <el-text>{{ loginUser?.id }}</el-text>
          </el-form-item>
          <el-form-item label="登录手机">
            <el-text>{{ loginUser?.phone }}</el-text>
          </el-form-item>
          <el-form-item label="昵称" prop="nickname">
            <el-input v-model="form.nickname" placeholder="请输入昵称" />
          </el-form-item>
          <el-form-item label="头像" prop="avatar">
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
                  <img v-if="form.avatar" :src="form.avatar" class="up-preview" />
                  <!-- 上传触发区域 无图片时显示加号图标 -->
                  <el-icon v-else class="up-icon"><Plus /></el-icon>
                </el-upload>
                <!-- 悬浮遮罩 鼠标移入时显示替换/移除按钮 -->
                <div v-if="form.avatar" class="up-mask">
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
          <el-form-item label="性别" prop="sex">
            <el-radio-group v-model="form.sex">
              <el-radio value="男">男</el-radio>
              <el-radio value="女">女</el-radio>
            </el-radio-group>
          </el-form-item>
          <el-form-item label="个人简介" prop="description">
            <el-input v-model="form.description" :rows="2" type="textarea" placeholder="请输入个人简介" />
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
defineOptions({ name: 'AccountUserInfo' });
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
  nickname: '',
  avatar: '',
  sex: '',
  description: '',
});
const rules = {
  nickname: [
    { required: true, message: '请输入昵称', trigger: 'blur' },
    { min: 2, max: 8, message: '昵称长度在 2 到 8 个字符之间', trigger: 'blur' },
  ],
  sex: [
    { required: true, message: '请选择性别', trigger: 'change' },
  ],
};

onMounted(() => {
  getLoginUser();
});

const getLoginUser = async () => {
  http.post('/account/getUser').then((res) => {
    loginUser.value = { ...res.data };
    Object.assign(form.value, loginUser.value);
  }).finally(() => {
    loading.value = false;
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
      form.value.avatar = res.data.url;
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
  form.value.avatar = '';
};

const onSubmit = () => {
  formRef.value.validate((valid) => {
    if (!valid) return;
    const loading = ElLoading.service({ lock: true, background: 'rgba(0, 0, 0, 0.7)' });
    http.post('/account/updateUser', form.value).then((res) => {
      if (res.code == 200) {
        ElMessage.success('更新成功');
      } else {
        ElMessage.error(res.message || '更新失败');
      }
    }).finally(() => {
      loading.close();
    });
  });
};
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
  width: 70px;
  height: 70px;
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

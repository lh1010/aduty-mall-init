<template>
	<view class="page">
    <CustomTop topTitle="企业认证"></CustomTop>
    <view class="container" v-if="!loading">
      <form class="aduty-form" v-if="loginUser.company_auth == 0">
        <view class="aduty-form-box">
          <view class="aduty-form-cell">
            <view class="aduty-form-cell-box">
              <view class="aduty-form-cell-hd">
                <view class="aduty-form-label">企业全称</view>
              </view>
              <view class="aduty-form-cell-bd">
                <input v-model="form.company_name" class="aduty-form-input" type="text" placeholder="请输入企业全称" />
              </view>
            </view>
          </view>
          <view class="aduty-form-cell">
            <view class="aduty-form-cell-box">
              <view class="aduty-form-cell-hd">
                <view class="aduty-form-label">信用代码</view>
              </view>
              <view class="aduty-form-cell-bd">
                <input v-model="form.social_credit_code" class="aduty-form-input" type="text" placeholder="请输入信用代码" />
              </view>
            </view>
          </view>
        </view>
        <view class="aduty-form-box">
					<view class="aduty-form-box-top"><span class="title">营业执照</span></view>
          <view class="aduty-form-cell">
            <view class="aduty-form-cell-bd">
              <view class="upload-box">
                <view class="upload-item" @click="uploadFun">
                  <!-- 有图 -->
                  <image v-if="form.business_license" class="upload-image" mode="aspectFill" :src="form.business_license" />
                  <!-- 无图 -->
                  <view v-else class="upload-placeholder"><i class="iconfont icon-paishe"></i></view>
                  <!-- 删除 -->
                  <view class="upload-delete" v-if="form.business_license" @click.stop="deleteImage">
                    <i class="iconfont icon-guanbicopy"></i>
                  </view>
                </view>
              </view>
            </view>
          </view>
        </view>
        <view class="aduty-form-action">
          <button class="aduty-btn aduty-btn-primary" hover-class="aduty-btn-hover" @click="formSubmit">提交信息</button>
        </view>
      </form>

      <view class="status_box" v-if="loginUser.company_auth != 0">
        <view class="alert" v-if="loginUser.company_auth == 1">
          <view class="alert-title">审核中</view>
        </view>
				<template v-if="loginUser.company_auth == 2">
					<view class="alert" v-if="loginUser.company_auth == 2">
						<view class="alert-title">认证失败</view>
						<view class="alert-desc">失败原因：{{ loginUser.company_auth_log && loginUser.company_auth_log.message ? loginUser.company_auth_log.message : '无' }}</view>
					</view>
					<button class="aduty-btn aduty-btn-primary" hover-class="aduty-btn-hover" style="margin-top: 30rpx;" @click="companyAuthReset">重新认证</button>
				</template>
        <view class="alert alert-success" v-if="loginUser.company_auth == 3">
          <view class="alert-title">认证成功</view>
        </view>
      </view>
    </view>
	</view>
</template>

<script setup>
import { ref } from 'vue';
import { onLoad } from '@dcloudio/uni-app';
import http from "@/utils/http.js";
import CustomTop from "@/components/CustomTop.vue";

const loading = ref(true);
const loginUser = ref({});
const form = ref({ company_name: '', social_credit_code: '', business_license: '' });

onLoad(() => {
  uni.setNavigationBarTitle({ title: '企业认证' });
  uni.showLoading();
  getLoginUser();
});

const getLoginUser = () => {
  http.post('/account/getLoginUser').then(res => {
    loading.value = false;
    uni.hideLoading();
    loginUser.value = res.data;
  });
};

const formSubmit = () => {
	if (!form.value.company_name) {
		uni.showToast({ title: '请输入企业全称', icon: 'none' });
		return;
	}
	if (!form.value.social_credit_code) {
		uni.showToast({ title: '请输入信用代码', icon: 'none' });
		return;
	}
	if (!form.value.business_license) {
		uni.showToast({ title: '请上传营业执照', icon: 'none' });
		return;
	}
  uni.showModal({
    content: '确认提交？',
    success(res) {
      if (res.confirm) {
        uni.showLoading();
        http.post('/account/companyAuth', form.value).then(res => {
          uni.hideLoading();
          if (res.code == 200) {
            getLoginUser();
            uni.showToast({ icon: 'none', title: '操作成功' });
          } else if (res.code == 400) {
            uni.showToast({ title: res.message, icon: 'none' });
          }
        });
      }
    }
  });
};

const companyAuthReset = () => {
  uni.showModal({
    content: '确认操作？',
    success(res) {
      if (res.confirm) {
        uni.showLoading();
        http.post('/account/companyAuthReset').then(res => {
          uni.hideLoading();
          if (res.code == 200) {
            getLoginUser();
          } else if (res.code == 400) {
            uni.showToast({ title: res.message, icon: 'none' });
          }
        });
      }
    }
  });
};

const uploadFun = () => {
  uni.chooseImage({
    count: 1,
    sizeType: ['original', 'compressed'],
    sourceType: ['album', 'camera'],
    success(res) {
      uni.showLoading();
			const formData = { source: 'auth_company' };
			const tempFilePaths = res.tempFilePaths;
      http.upload('/upload', tempFilePaths[0], formData).then(res => {
        uni.hideLoading();
        if (res.code == 200) {
          form.value.business_license = res.data.url;
        } else {
          uni.showToast({ title: res.message || '上传失败', icon: 'none' });
        }
      });
    }
  });
};

const deleteImage = () => {
  form.value.business_license = '';
};
</script>

<style>
page {
  padding-bottom: 40rpx;
}
.aduty-form-box {
  margin-top: 30rpx;
}
.status_box {
  margin-top: 50rpx;
}
.alert {
  padding: 20rpx 30rpx;
  border-radius: 4px;
  font-size: 14px;
	background-color: #fff3e0;
  color: #f57c00;
}
.alert-title {
  font-weight: bold;
}
.alert-desc {
  margin-top: 8rpx;
  font-size: 12px;
  opacity: 0.8;
}
/* upload 上传 start */
.upload-box {
  display: flex;
  flex-wrap: wrap;
}
.upload-item {
  width: 200rpx;
  height: 200rpx;
  position: relative;
}
.upload-image {
  width: 100%;
  height: 100%;
	display: block;
}
.upload-placeholder {
  width: 100%;
  height: 100%;
  border: 2rpx solid #e5e5e5;
  background-color: #f8f8f8;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #ccc;
  font-size: 60rpx;
}
.upload-delete {
  position: absolute;
  top: 0;
  right: 0;
  width: 40rpx;
  height: 40rpx;
  background-color: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: 20rpx;
}
/* upload 上传 end */
</style>

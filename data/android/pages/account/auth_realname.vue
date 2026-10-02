<template>
	<view class="page">
    <CustomTop topTitle="身份认证"></CustomTop>
    <view class="container" v-if="!loading">
      <form class="aduty-form" v-if="loginUser.realname_auth == 0">
        <view class="aduty-form-box">
          <view class="aduty-form-cell">
            <view class="aduty-form-cell-box">
              <view class="aduty-form-cell-hd">
                <view class="aduty-form-label">真实姓名</view>
              </view>
              <view class="aduty-form-cell-bd">
                <input v-model="form.realname" class="aduty-form-input" type="text" placeholder="请输入真实姓名" />
              </view>
            </view>
          </view>
          <view class="aduty-form-cell">
            <view class="aduty-form-cell-box">
              <view class="aduty-form-cell-hd">
                <view class="aduty-form-label">身份证号</view>
              </view>
              <view class="aduty-form-cell-bd">
                <input v-model="form.idcard" class="aduty-form-input" type="text" placeholder="请输入身份证号" />
              </view>
            </view>
          </view>
        </view>
        <view class="images">
          <view class="top_title">上传身份证照片</view>
          <view class="img_items">
            <view class="img_item" @click="uploadFun(1);">
              <view class="image">
                <image class="img" :src="form.idcard_img1 || '/static/images/identity_card1.png'" />
              </view>
              <view class="btn">上传正面照</view>
            </view>
            <view class="img_item" @click="uploadFun(2);">
              <view class="image">
                <image class="img" :src="form.idcard_img2 || '/static/images/identity_card2.png'" />
              </view>
              <view class="btn">上传反面照</view>
            </view>
          </view>
        </view>
        <view class="aduty-form-action">
          <button class="aduty-btn aduty-btn-primary" hover-class="aduty-btn-hover" @click="formSubmit">提交信息</button>
        </view>
      </form>

      <view class="status_box" v-if="loginUser.realname_auth != 0">
        <view class="alert" v-if="loginUser.realname_auth == 1">
          <view class="alert-title">审核中</view>
        </view>
				<template v-if="loginUser.realname_auth == 2">
					<view class="alert" >
					  <view class="alert-title">认证失败</view>
					  <view class="alert-desc">失败原因：{{ loginUser.realname_auth_log?.message || '无' }}</view>
					</view>
					<button class="aduty-btn aduty-btn-primary" hover-class="aduty-btn-hover" style="margin-top: 30rpx;" @click="realnameAuthReset">重新认证</button>
				</template>
        <view class="alert" v-if="loginUser.realname_auth == 3">
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
const form = ref({ realname: '', idcard: '', idcard_img1: '', idcard_img2: '' });

onLoad(() => {
  uni.setNavigationBarTitle({ title: '身份认证' });
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

const uploadFun = (ident = 1) => {
  uni.chooseImage({
    count: 1,
    sizeType: ['original', 'compressed'],
    sourceType: ['album', 'camera'],
    success(res) {
      uni.showLoading();
			const formData = { source: 'auth_realname' };
      const tempFilePaths = res.tempFilePaths;
      http.upload('/upload', tempFilePaths[0], formData).then(res => {
        uni.hideLoading();
        if (res.code == 200) {
          if (ident == 1) {
            form.value.idcard_img1 = res.data.url;
          }
          if (ident == 2) {
            form.value.idcard_img2 = res.data.url;
          }
        } else {
          uni.showToast({ title: res.message, icon: 'none' });
        }
      });
    }
  });
};

const formSubmit = () => {
	if (form.value.realname == '') {
		uni.showToast({ title: '请输入真实姓名', icon: 'none' });
		return;
	}
	if (form.value.idcard == '') {
		uni.showToast({ title: '请输入身份证号', icon: 'none' });
		return;
	}
	if (form.value.idcard_img1 == '' || form.value.idcard_img2 == '') {
		uni.showToast({ title: '请上传身份证照片', icon: 'none' });
		return;
	}
  uni.showModal({
    content: '确认提交？',
    success(res) {
      if (res.confirm) {
        uni.showLoading();
        http.post('/account/realnameAuth', form.value).then(res => {
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

const realnameAuthReset = () => {
  uni.showModal({
    content: '确认操作？',
    success(res) {
      if (res.confirm) {
        uni.showLoading();
        http.post('/account/realnameAuthReset').then(res => {
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
</script>

<style>
.aduty-form-box {
  margin-top: 30rpx;
}

.images {
  margin-top: 30rpx;
}
.images .top_title {
	font-size: 12px;
	color: #999;
}
.images .img_items {
  display: flex;
  justify-content: space-between;
  margin-top: 30rpx;
}
.images .img_item {
  width: 48%;
}
.images .img_item .image {
  width: 100%;
  height: 130px;
  line-height: 130px;
  background-color: #e7efff;
  position: relative;
}
.images .img_item .img {
  max-width: 85%;
  max-height: 70%;
  position: absolute;
  top: 0;
  bottom: 0;
  left: 0;
  right: 0;
  margin: auto;
}
.images .btn {
  background-color: #3c7bfb;
  color: #fff;
  text-align: center;
  height: 70rpx;
  line-height: 70rpx;
  border-radius: 0;
	font-size: 12px;
	opacity: .8;
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
</style>

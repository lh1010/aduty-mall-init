<template>
  <view class="page">
    <CustomTop topTitle="联系方式"></CustomTop>
    <view class="container" v-if="!loading">
      <form class="aduty-form">
        <view class="aduty-form-box">
        	<view class="aduty-form-box-top"><span class="title">微信</span></view>
          <view class="aduty-form-cell">
            <view class="aduty-form-cell-box">
              <view class="aduty-form-cell-bd">
                <input v-model="form.weixin" class="aduty-form-input" type="text" placeholder="请输入微信" />
              </view>
            </view>
          </view>
        </view>
        <view class="aduty-form-box">
        	<view class="aduty-form-box-top"><span class="title">手机</span></view>
          <view class="aduty-form-cell">
            <view class="aduty-form-cell-box">
              <view class="aduty-form-cell-bd">
                <input v-model="form.phone" class="aduty-form-input" type="text" placeholder="请输入手机" />
              </view>
            </view>
          </view>
        </view>
        <view class="aduty-form-box">
        	<view class="aduty-form-box-top"><span class="title">Q Q</span></view>
          <view class="aduty-form-cell">
            <view class="aduty-form-cell-box">
              <view class="aduty-form-cell-bd">
                <input v-model="form.qq" class="aduty-form-input" type="text" placeholder="请输入QQ" />
              </view>
            </view>
          </view>
        </view>
        <view class="aduty-form-box">
        	<view class="aduty-form-box-top"><span class="title">电话</span></view>
          <view class="aduty-form-cell">
            <view class="aduty-form-cell-box">
              <view class="aduty-form-cell-bd">
                <input v-model="form.telphone" class="aduty-form-input" type="text" placeholder="请输入电话" />
              </view>
            </view>
          </view>
        </view>
        <view class="aduty-form-action">
          <button class="aduty-btn aduty-btn-primary" hover-class="aduty-btn-hover" @click="formSubmit">确认提交</button>
        </view>
      </form>
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
const form = ref({ weixin: '', phone: '', qq: '', telphone: '' });

onLoad(() => {
  uni.setNavigationBarTitle({ title: '联系方式' });
  getLoginUser();
});

const getLoginUser = () => {
  uni.showLoading();
  http.post('/account/getUser').then(res => {
    uni.hideLoading();
    loading.value = false;
    loginUser.value = { ...res.data };
    Object.assign(form.value, loginUser.value.contact);
  });
};

const formSubmit = () => {
  uni.showLoading();
  http.post('/account/updateUserContact', form.value).then(res => {
    uni.hideLoading();
    if (res.code == 200) {
      getLoginUser();
      uni.showToast({ icon: 'success', title: '保存成功' });
    } else if (res.code == 400) {
      uni.showToast({ icon: 'none', title: res.message });
    }
  });
};
</script>

<style>
.aduty-form-box {
  margin-top: 30rpx;
}
</style>

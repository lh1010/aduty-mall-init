<template>
	<view>
    <CustomTop topTitle="卡密"></CustomTop>
    <view class="container">
      <view class="aduty-form">
        <view class="aduty-form-box">
          <view class="aduty-form-box-top">卡密内容</view>
          <view class="aduty-form-cell">
            <view class="aduty-form-cell-box">
              <view class="aduty-form-cell-bd">
                <input class="aduty-form-input" type="text" placeholder="请输入卡密内容" v-model="form.key" />
              </view>
            </view>
          </view>
        </view>
        <view class="aduty-form-action">
          <button class="aduty-btn aduty-btn-primary" hover-class="aduty-btn-hover" @click="formSubmit">确认兑换</button>
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

onLoad(() => {
  uni.setNavigationBarTitle({ title: '卡密' });
});

const form = ref({ key: '' });

const formSubmit = () => {
	if (!form.value.key) {
    uni.showToast({ title: '请输入卡密内容', icon: 'none' });
    return;
  }
  uni.showModal({
    content: '确认兑换？',
    success(res) {
      if (res.confirm) {
        uni.showLoading();
        http.post('/account/exchangeCdkey', form.value).then(res => {
          uni.hideLoading();
          if (res.code == 200) {
            uni.showToast({ title: '兑换成功', icon: 'success' });
          } else {
            uni.showModal({ content: res.message || '兑换失败', showCancel: false });
          }
        });
      }
    }
  });
};
</script>

<style>
page {
  padding-bottom: 30rpx;
}
.aduty-form-box {
  margin-top: 30rpx;
}
</style>

<template>
	<view>
    <CustomTop topTitle="设置密码"></CustomTop>
    <view class="container" v-if="!loading">
      <form class="aduty-form">
        <template v-if="loginUser.password != ''">
          <view class="aduty-form-box">
						<view class="aduty-form-box-top"><span class="title">旧密码</span></view>
            <view class="aduty-form-cell">
              <view class="aduty-form-cell-box">
                <view class="aduty-form-cell-bd">
                  <input v-model="form.password_old" class="aduty-form-input" type="password" password placeholder="请输入旧密码" />
                </view>
              </view>
            </view>
          </view>
          <view class="aduty-form-box">
						<view class="aduty-form-box-top"><span class="title">新密码</span></view>
            <view class="aduty-form-cell">
              <view class="aduty-form-cell-box">
                <view class="aduty-form-cell-bd">
                  <input v-model="form.password" class="aduty-form-input" type="password" password placeholder="请输入新密码" />
                </view>
              </view>
            </view>
          </view>
          <view class="aduty-form-box">
						<view class="aduty-form-box-top"><span class="title">确认新密码</span></view>
            <view class="aduty-form-cell">
              <view class="aduty-form-cell-box">
                <view class="aduty-form-cell-bd">
                  <input v-model="form.password_confirm" class="aduty-form-input" type="password" password placeholder="请输入确认新密码" />
                </view>
              </view>
            </view>
          </view>
        </template>
        <template v-if="loginUser.password == ''">
          <view class="aduty-form-box">
						<view class="aduty-form-box-top"><span class="title">登录密码</span></view>
            <view class="aduty-form-cell">
              <view class="aduty-form-cell-box">
                <view class="aduty-form-cell-bd">
                  <input v-model="form.password" class="aduty-form-input" type="password" password placeholder="请输入登录密码" />
                </view>
              </view>
            </view>
          </view>
          <view class="aduty-form-box">
						<view class="aduty-form-box-top"><span class="title">确认密码</span></view>
            <view class="aduty-form-cell">
              <view class="aduty-form-cell-box">
                <view class="aduty-form-cell-bd">
                  <input v-model="form.password_confirm" class="aduty-form-input" type="password" password placeholder="请输入确认密码" />
                </view>
              </view>
            </view>
          </view>
        </template>
        <view class="aduty-form-action">
          <button class="aduty-btn aduty-btn-primary" hover-class="aduty-btn-hover" @click="formSubmit">确认提交</button>
        </view>
      </form>
    </view>
	</view>
</template>

<script setup>
import { ref } from 'vue';
import { onLoad, onShow } from '@dcloudio/uni-app';
import http from "@/utils/http.js";
import CustomTop from "@/components/CustomTop.vue";

onLoad(() => {
  uni.setNavigationBarTitle({ title: '设置密码' });
});

const loading = ref(true);
const loginUser = ref({});
const form = ref({ password_old: '', password: '', password_confirm: '' });

onShow(() => {
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
  uni.showLoading();
  http.post('/account/updateUserPassword', form.value).then(res => {
    uni.hideLoading();
    if (res.code == 200) {
      getLoginUser();
      uni.showToast({ title: '保存成功', icon: 'none' });
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

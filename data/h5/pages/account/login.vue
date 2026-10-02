<template>
  <view>
    <CustomTop :topTitle="topTitle"></CustomTop>
    <view class="container">
      <view class="login">
				<template v-if="type == 'password'">
					<form class="form" @submit="formSubmit">
						<view class="form-cell">
							<view class="form-cell-box inputbox">
								<input type="text" class="input" name="phone" placeholder="手机号" />
							</view>
						</view>
						<view class="form-cell">
							<view class="form-cell-box inputbox">
								<input name="password" class="aduty-form-input" type="password" placeholder="密码" />
							</view>
						</view>
						<view class="remind">
							<radio color="#f4645f" :checked="readAgreementStatus" @click="setReadAgreement" />
							<view class="txt">阅读并同意<span class="link" @click="jumpPage('/pages/article/show?type=user_agreement')">《用户协议》</span></view>
						</view>
						<button class="aduty-btn aduty-btn-primary" hover-class="aduty-btn-hover" formType="submit" :style="{ marginTop: '15px' }">立即登录</button>
					</form>
					<view class="other_way">
						<view class="ow_item" @click="setType('sms')">短信登录</view>
						<view class="ow_item" @click="jumpPage('/pages/account/register');">立即注册</view>
					</view>
				</template>
				<template v-if="type == 'sms'">
					<form class="form" @submit="formSubmit">
						<view class="form-cell">
							<view class="form-cell-box inputbox">
								<input type="text" class="input" name="phone" placeholder="手机号" v-model="form.phone" />
							</view>
						</view>
						<view class="form-cell">
							<view class="form-cell-box inputbox">
								<input name="code" class="input" type="text" placeholder="验证码" />
								<span class="btn" @click="sendCode">{{ sendCodeText }}</span>
							</view>
						</view>
						<view class="remind">
							<radio color="#f4645f" :checked="readAgreementStatus" @click="setReadAgreement" />
							<view class="txt">阅读并同意<span class="link" @click="jumpPage('/pages/article/show?type=user_agreement')">《用户协议》</span></view>
						</view>
						<button class="aduty-btn aduty-btn-primary" hover-class="aduty-btn-hover" formType="submit" :style="{ marginTop: '15px' }">立即登录</button>
					</form>
					<view class="other_way">
						<view class="ow_item" @click="setType('password')">密码登录</view>
						<view class="ow_item" @click="jumpPage('/pages/account/register');">立即注册</view>
					</view>
				</template>
				<template>
					<view class="wxapplogin none" v-if="!readAgreementStatus" @click="showAgreementErrorMsg">
						<view class="box">
							<image class="img" src="/static/images/phone.png" />
							<view class="txt">手机号快捷登录</view>
							<button class="btn">获取手机号码</button>
						</view>
					</view>
					<view class="wxapplogin none" v-if="readAgreementStatus">
						<view class="box">
							<image class="img" src="/static/images/phone.png" />
							<view class="txt">手机号快捷登录</view>
							<button class="btn" open-type="getPhoneNumber" @getphonenumber="decryptPhoneNumber">获取手机号码</button>
						</view>
					</view>
				</template>
      </view>
    </view>
  </view>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { onLoad, onShow } from '@dcloudio/uni-app';
import CustomTop from "@/components/CustomTop.vue";
import http from "@/utils/http.js";
import util from "@/utils/util.js";

const type = ref(''); // password|sms
const topTitle = computed(() => {
  return type.value == 'password' ? '密码登录' : '短信登录';
});
watch(topTitle, (val) => {
  uni.setNavigationBarTitle({ title: val });
});

const form = ref({ phone: '' });
const sendCodeText = ref('发送验证码');
const sendSeconds = ref(60);
const sendDisabled = ref(false);

const readAgreementStatus = ref(true);
const code2seesion = ref('');
const wxmpOpenid = ref('');

onLoad((options) => {
	type.value = options.type || 'password';
  // 微信内打开
  if (util.isWx() && options.wxmp_openid != undefined) {
    wxmpOpenid.value = options.wxmp_openid;
  }
});

onShow(() => {
  // 微信内打开 公众号登录
  if (util.isWx()) {
    if (wxmpOpenid.value != '') {
      uni.setStorageSync('wxmp_openid', wxmpOpenid.value);
      return;
    }
    let wxmpOpenidStorage = uni.getStorageSync('wxmp_openid');
    if (wxmpOpenidStorage != '') return;

    var pages = getCurrentPages();
    let currentPage = pages[pages.length - 1]['$page']['fullPath'];
    http.post('/common/getConfig').then((res) => {
      window.location.href = res.data.app_url + '/api/account/wxmp_login?url_ident=' + currentPage;
    });
  }
});

// 切换登录方式
const setType = (value) => {
  type.value = value;
	util.updateUrl({ type: value });
};

// 阅读并同意协议
const setReadAgreement = () => {
  readAgreementStatus.value = !readAgreementStatus.value;
};

// 发送验证码
const sendCode = () => {
	if (sendDisabled.value) return;
  if (!readAgreementStatus.value) {
    uni.showToast({ icon: 'none', title: '请先阅读并同意协议' });
    return;
  }
  if (!form.value.phone) {
    uni.showToast({ icon: 'none', title: '请输入手机号' });
    return;
  }
  uni.showLoading();
  http.post('/account/sendCode', { phone: form.value.phone }).then(res => {
    uni.hideLoading();
    if (res.code == 200) {
      sendDisabled.value = true;
      let timer = setInterval(() => {
        if (sendSeconds.value === 0) {
          clearInterval(timer);
          sendCodeText.value = '发送验证码';
          sendSeconds.value = 60;
          sendDisabled.value = false;
          return;
        }
        sendCodeText.value = sendSeconds.value + '秒后重试';
        sendSeconds.value--;
      }, 1000);
      uni.showToast({ icon: 'none', title: '验证码已发送' });
    } else if (res.code == 400) {
      uni.showToast({ title: res.message, icon: 'none' });
    }
  });
};

const formSubmit = (e) => {
  if (!readAgreementStatus.value) {
    uni.showToast({ title: '请先阅读并同意协议', icon: 'none' });
    return;
  }
  uni.showLoading();
	let url = '/account/login_password';
	if (type.value == 'sms') {
    url = '/account/login';
  }
  let params = e.detail.value;
  params.code2seesion = code2seesion.value;
  if (util.isWx()) {
    params.type = 'wxmp';
    params.wxmp_openid = uni.getStorageSync('wxmp_openid');
  }
  http.post(url, params).then(res => {
    uni.hideLoading();
    if (res.code == 200) {
      uni.setStorageSync('token', res.data.token);
      uni.showToast({
        title: '登录成功',
        duration: 1500,
        success: function() {
          setTimeout(function() {
            uni.switchTab({ url: '/pages/account/index' });
          }, 1500);
        }
      });
    } else {
      uni.showToast({ title: res.message || '登录失败', icon: 'none' });
    }
  });
};

const wxappLogin1 = () => {
  uni.login({
    success: function(loginRes) {
      if (loginRes.code) {
        uni.showLoading();
        http.post('/account/wxapp_login1', { code: loginRes.code }).then(res => {
          uni.hideLoading();
          if (res.code == 400) {
            uni.showToast({ title: res.message, icon: 'none' });
            return;
          }
          code2seesion.value = res.data.code2seesion;
        });
      } else {
        console.log(loginRes);
        uni.showToast({ title: '登录失败', icon: 'none' });
      }
    }
  });
};

const showAgreementErrorMsg = () => {
  uni.showToast({ icon: 'none', title: '请先阅读并同意协议' });
};

const decryptPhoneNumber = (e) => {
  // 用户取消授权
  if (e.detail.errMsg == 'getPhoneNumber:fail user deny') {
    uni.showToast({ title: '取消授权', icon: 'none' });
    return;
  }
  // 邀请用户ID
  let inviteUserId = uni.getStorageSync('inviteUserId');
  let params = {
    inviteUserId: inviteUserId,
    iv: e.detail.iv,
    encryptedData: e.detail.encryptedData,
    code2seesion: code2seesion.value
  };
  uni.showLoading({ title: '登录中' });
  http.post('/account/wxapp_login2', params).then(res => {
    uni.hideLoading();
    if (res.code == 400) {
      uni.showToast({ title: res.message, icon: 'none' });
      return;
    }
    uni.setStorageSync('token', res.data.token);
    uni.switchTab({ url: '/pages/account/index' });
    // 通知来源页 loginSuccess()
    var pages = getCurrentPages();
    var prevPage = pages[pages.length - 2];
    if (prevPage.loginSuccess != undefined) {
      prevPage.loginSuccess();
    }
  });
};

const jumpPage = (url) => {
  uni.navigateTo({ url: url });
};
</script>

<style>
@import url("account.css");
page {
  background-color: #ffffff;
}
</style>

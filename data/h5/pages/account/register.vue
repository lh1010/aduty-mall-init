<template>
  <view>
    <CustomTop :topTitle="'注册'"></CustomTop>
    <view class="container">
      <view class="login">
        <form class="form" @submit="formSubmit">
          <view class="form-cell">
            <view class="form-cell-box inputbox">
              <input name="nickname" class="input" type="text" placeholder="昵称" />
            </view>
          </view>
          <view class="form-cell">
            <view class="form-cell-box inputbox">
              <input name="phone" v-model="form.phone" class="input" type="text" placeholder="手机号" />
            </view>
          </view>
          <view class="form-cell">
            <view class="form-cell-box inputbox">
              <input name="code" class="input" type="text" placeholder="验证码" />
              <span class="btn" @click="sendCode">{{ sendCodeText }}</span>
            </view>
          </view>
          <view class="form-cell">
            <view class="form-cell-box inputbox">
              <input name="password" class="input" type="password" placeholder="密码" />
            </view>
          </view>
          <view class="form-cell">
            <view class="form-cell-box inputbox">
              <input name="password_confirm" class="input" type="password" placeholder="重复密码" />
            </view>
          </view>
          <view class="remind">
            <radio color="#f4645f" :checked="readAgreementStatus" @click="setReadAgreement" />
            <view class="txt">阅读并同意<span class="link" @click="jumpPage('/pages/article/show?type=user_agreement')">《用户协议》</span></view>
          </view>
          <button class="aduty-btn aduty-btn-primary" hover-class="aduty-btn-hover" formType="submit" :style="{ marginTop: '15px' }">立即注册</button>
        </form>
        <view class="other_way">
          <view class="ow_item" @click="jumpPage('/pages/account/login_password');">已有账号？立即登录</view>
        </view>
        <view class="wxapplogin none" :style="{ marginTop: '50px' }" v-if="!readAgreementStatus" @click="showAgreementErrorMsg">
          <view class="box">
            <image class="img" src="/static/images/phone.png" />
            <view class="txt">手机号快捷登录</view>
            <button class="btn">获取手机号码</button>
          </view>
        </view>
        <view class="wxapplogin none" :style="{ marginTop: '50px' }" v-if="readAgreementStatus">
          <view class="box">
            <image class="img" src="/static/images/phone.png" />
            <view class="txt">手机号快捷登录</view>
            <button class="btn" open-type="getPhoneNumber" @getphonenumber="decryptPhoneNumber">获取手机号码</button>
          </view>
        </view>
      </view>
    </view>
  </view>
</template>

<script setup>
import { ref } from 'vue';
import { onLoad, onShow } from '@dcloudio/uni-app';
import CustomTop from "@/components/CustomTop.vue";
import http from "@/utils/http.js";
import util from "@/utils/util.js";

const form = ref({ phone: '' });
const readAgreementStatus = ref(false);
const sendCodeText = ref('获取验证码');
const sendSeconds = ref(60);
const sendDisabled = ref(false);
const code2seesion = ref('');
const wxmpOpenid = ref('');

onLoad((options) => {
  if (options.invite_code != undefined) {
    uni.setStorageSync('invite_code', options.invite_code);
  } else if (options.scene != undefined) {
    let scene = decodeURIComponent(options.scene);
    let obj = util.urlToObj(scene);
    if (obj.invite_code != undefined) {
      uni.setStorageSync('invite_code', obj.invite_code);
    }
  }

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
    uni.showToast({ icon: 'none', title: '请先阅读并同意协议' });
    return;
  }
  uni.showLoading();
  let params = e.detail.value;
  params.code2seesion = code2seesion.value;
  // 微信内打开
  if (util.isWx()) {
    params.type = 'wxmp';
    params.wxmp_openid = uni.getStorageSync('wxmp_openid');
  }
  params.invite_code = uni.getStorageSync('invite_code');
  http.post('/account/register', params).then(res => {
    uni.hideLoading();
    if (res.code == 200) {
      uni.setStorageSync('token', res.data.token);
      uni.showToast({
        title: '注册成功',
        duration: 1500,
        success: function() {
          setTimeout(function() {
            uni.switchTab({ url: '/pages/account/index' });
          }, 1500);
        }
      });
    } else if (res.code == 400) {
      uni.showToast({ title: res.message, icon: 'none' });
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
  background-color: #fff;
}
.ubtn {
  padding: 5px !important;
}
</style>

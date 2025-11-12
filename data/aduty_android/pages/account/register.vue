<template>
	<view>
    <CustomTop top_title="注册"></CustomTop>
    <view class="container">
      <view class="login">
        <form class="aduty-fm" @submit="formSubmit" ref="form">
          <view class="aduty-fm-item">
            <view class="aduty-fm-inputbox">
              <input name="nickname" class="aduty-fm-input" type="text" placeholder="昵称" />
            </view>
          </view>
          <view class="aduty-fm-item">
          	<view class="aduty-fm-inputbox">
          	  <input name="phone" v-model="form.phone" class="aduty-fm-input" type="text" placeholder="手机号" />
          	</view>
          </view>
          <view class="aduty-fm-item">
          	<view class="aduty-fm-inputbox">
          	  <input name="code" class="aduty-fm-input" type="text" placeholder="验证码" />
          		<span class="btn" @click="sendCode">{{luckSend_btnTxt}}</span>
          	</view>
          </view>
          <view class="aduty-fm-item">
            <view class="aduty-fm-inputbox">
              <input name="password" class="aduty-fm-input" type="password" placeholder="密码" />
            </view>
          </view>
          <view class="aduty-fm-item">
            <view class="aduty-fm-inputbox">
              <input name="password_confirm" class="aduty-fm-input" type="password" placeholder="重复密码" />
            </view>
          </view>
          <view class="remind">
            <radio color="#f4645f" :checked="read_agreement_status" @click="setReadAgreement" />
            <view class="txt">阅读并同意<span class="link" @click="jumpPage('/pages/article/show?type=user_agreement')">《用户协议》</span></view>
          </view>
          <button class="aduty-btn aduty-btn-default" formType="submit" :style="{ marginTop: '15px' }">立即登录</button>
        </form>
        <view class="other_way">
          <view class="ow_item ow_item1" @click="jumpPage('/pages/account/login_password');">已有账号？立即登录</view>
        </view>
        <view class="wxapplogin none" :style="{ marginTop: '50px' }" v-if="!read_agreement_status" @click="showAgreementErrorMsg">
          <view class="box">
            <image class="img" src="/static/images/phone.png" />
            <view class="txt">手机号快捷登录</view>
            <button class="btn">获取手机号码</button>
          </view>
        </view>
        <view class="wxapplogin none" :style="{ marginTop: '50px' }" v-if="read_agreement_status">
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

<script>
import { request } from "@/utils/http.js"
import CustomTop from "@/components/CustomTop.vue"
import util from "@/utils/util.js"

export default {
  components: { CustomTop },

  data() {
    return {
      form: {
        phone: ''
      },
      read_agreement_status: false,
      luckSend_btnTxt: '获取验证码',
      luckSend_seconds: 60,
      luckSend_disabled: false,
      code2seesion: '',
    }
  },

	onLoad(options) {
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
	  if (util.is_wx() && options.wxmp_openid != undefined) {
	    this.wxmp_openid = options.wxmp_openid;
	  }
	},

  onShow() {
  	// 微信内打开 公众号登录
    if (util.is_wx()) {
      if (this.wxmp_openid != undefined) {
        uni.setStorageSync('wxmp_openid', this.wxmp_openid);
        return false;
      }

      let wxmp_openid = uni.getStorageSync('wxmp_openid');
      if (wxmp_openid != '') return false;

      var pages = getCurrentPages() // 获取栈实例
      let currentRoute  = pages[pages.length - 1].route; // 获取当前页面路由
      let currentPage = pages[pages.length - 1]['$page']['fullPath'] //当前页面路径(带参数)
      request.post('/common/getConfig').then((res) => {
        window.location.href = res.data.app_url + '/api/account/wxmp_login?url_ident=' + currentPage;
      })
    }

  	// 小程序登录
    //this.wxapp_login1();
  },

  methods: {
    formSubmit(e) {
      if (!this.read_agreement_status) {
        uni.showToast({ icon: 'none', title: '请先阅读并同意协议' });
        return false;
      }
      uni.showLoading();
      let params = e.detail.value;
      params.code2seesion = this.code2seesion;
			// 微信内打开
			if (util.is_wx()) {
			  params.type = 'wxmp';
			  params.wxmp_openid = uni.getStorageSync('wxmp_openid');
			}
			params.invite_code = uni.getStorageSync('invite_code');
      request.post('/account/register', params).then(res => {
        uni.hideLoading();
        if (res.code == 200) {
          uni.setStorageSync('user_token', res.data.user_token);
          uni.showToast({
            title: '注册成功',
            duration: 1500,
            success: function() {
              setTimeout(function(){
                uni.switchTab({ url: '/pages/account/index' });
              }, 1500)
            }
          });
        } else if (res.code == 400) {
          uni.showToast({ title: res.message, icon: 'none' });
        }
      })
    },

    sendCode: function() {
			if (!this.read_agreement_status) {
			  uni.showToast({ icon: 'none', title: '请先阅读并同意协议' });
			  return false;
			}
      if (this.luckSend_disabled) return false;
      uni.showLoading({});
      request.post('/account/sendCode', {phone: this.form.phone}).then(res => {
        uni.hideLoading();
        if (res.code == 200) {
          this.luckSend_disabled = true;
          let timer = setInterval(() => {
            this.luckSend_btnTxt = this.luckSend_seconds + '秒再重试';
            this.luckSend_seconds--;
            if (this.luckSend_seconds === 0){
              clearInterval(timer);
              this.luckSend_btnTxt = '发送验证码';
              this.luckSend_seconds = 60;
              this.luckSend_disabled = false
            }
          }, 1000);
          uni.hideLoading();
          uni.showToast({ icon: 'none', title: '验证码已发送' });
        } else if (res.code == 400) {
          uni.showToast({ title: res.message, icon: 'none' });
        }
      })
    },

    setReadAgreement: function(e) {
      let a = this.read_agreement_status ? false : true;
      this.read_agreement_status = a;
    },

    wxapp_login1: function() {
      let that = this;
      uni.login({
        success: function (loginRes) {
          if (loginRes.code) {
            uni.showLoading();
            request.post('/account/wxapp_login1', {code: loginRes.code}).then(res => {
              uni.hideLoading();
              // 异常
              if (res.code == 400) {
                uni.showToast({
                  title: res.message,
                  icon: 'none',
                });
                return false;
              }
              // 成功
              that.code2seesion = res.data.code2seesion;
            })
          } else {
            console.log(loginRes);
            uni.showToast({
              title: '登录失败',
              icon: 'none'
            });
          }
        }
      })
    },

    showAgreementErrorMsg: function() {
      uni.showToast({ icon: 'none', title: '请先阅读并同意协议' });
      return false;
    },

    decryptPhoneNumber: function(e) {
      var that = this;
      // 用户取消授权
      if (e.detail.errMsg == 'getPhoneNumber:fail user deny'){
        uni.showToast({title: '取消授权', icon: 'none'});
        return false;
      }
      // 邀请用户ID
      let inviteUserId = uni.getStorageSync('inviteUserId');
      let params = {};
      params.inviteUserId = inviteUserId;
      params.iv = e.detail.iv;
      params.encryptedData = e.detail.encryptedData;
      params.code2seesion = that.code2seesion;
      uni.showLoading({title: '登录中'});
      request.post('/account/wxapp_login2', params).then(res => {
        uni.hideLoading();
        // 异常
        if (res.code == 400) {
          uni.showToast({ title: res.message, icon: 'none' });
          return false;
        }
        // 成功
        uni.setStorageSync('user_token', res.data.user_token);
        //uni.navigateBack({ delta: 1 });
        uni.switchTab({ url: '/pages/account/index' });
        // 通知来源页 loginSuccess()
        var pages = getCurrentPages(); var prevPage = pages[pages.length - 2];
        if (prevPage.loginSuccess != undefined) {
          prevPage.loginSuccess();
        }
      })
    },

    jumpPage: function(url) {
      uni.navigateTo({ url: url });
    }
  }
}
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

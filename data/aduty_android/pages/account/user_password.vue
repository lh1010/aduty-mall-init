<template>
	<view>
    <CustomTop top_title="设置密码"></CustomTop>
    <view class="container" v-if="!loading">
      <form class="aduty-form" @submit="formSubmit">
        <view v-if="user.password != ''">
          <view class="aduty-form-box">
						<view class="stitle"><span class="txt">旧密码</span></view>
            <view class="aduty-form-cell">
              <view class="aduty-form-cell-box">
                <view class="aduty-form-cell-bd">
                  <input name="password_old" class="aduty-form-input" type="password" placeholder="请输入旧密码" />
                </view>
              </view>
            </view>
          </view>
          <view class="aduty-form-box">
						<view class="stitle"><span class="txt">新密码</span></view>
            <view class="aduty-form-cell">
              <view class="aduty-form-cell-box">
                <view class="aduty-form-cell-bd">
                  <input name="password" class="aduty-form-input" type="password" placeholder="请输入新密码" />
                </view>
              </view>
            </view>
          </view>
          <view class="aduty-form-box">
						<view class="stitle"><span class="txt">确认新密码</span></view>
            <view class="aduty-form-cell">
              <view class="aduty-form-cell-box">
                <view class="aduty-form-cell-bd">
                  <input name="password_confirm" class="aduty-form-input" type="password" placeholder="请输入确认新密码" />
                </view>
              </view>
            </view>
          </view>
        </view>
        <view v-if="user.password == ''">
          <view class="aduty-form-box">
						<view class="stitle"><span class="txt">登录密码</span></view>
            <view class="aduty-form-cell">
              <view class="aduty-form-cell-box">
                <view class="aduty-form-cell-bd">
                  <input name="password" class="aduty-form-input" type="password" placeholder="请输入登录密码" />
                </view>
              </view>
            </view>
          </view>
          <view class="aduty-form-box">
						<view class="stitle"><span class="txt">确认密码</span></view>
            <view class="aduty-form-cell">
              <view class="aduty-form-cell-box">
                <view class="aduty-form-cell-bd">
                  <input name="password_confirm" class="aduty-form-input" type="password" placeholder="请输入确认密码" />
                </view>
              </view>
            </view>
          </view>
        </view>
        <view class="aduty-form-action">
          <button class="aduty-btn aduty-btn-default" formType="submit">确认提交</button>
        </view>
      </form>
    </view>
	</view>
</template>

<script>
import { request } from "@/utils/http.js"
import CustomTop from "@/components/CustomTop.vue"
export default {
  components: { CustomTop },

  onShow: function() {
		uni.showLoading();
    this.getLoginUser();
  },

  data() {
    return {
      loading: true,
      user: {}
    }
  },
  methods: {
    getLoginUser: function() {
      request.post('/account/getLoginUser').then(res => {
				this.loading = false;
        uni.hideLoading();
        this.user = res.data;
      })
    },

    formSubmit: function(e) {
      let that = this;
      uni.showLoading();
      let params = e.detail.value;
      request.post('/account/updateUserPassword', params).then(res => {
        uni.hideLoading();
        if (res.code == 200) {
          that.getLoginUser();
          uni.showToast({ title: '保存成功', icon: 'none' });
        } else if (res.code == 400) {
          uni.showToast({ icon: 'none', title: res.message });
        }
      })
    },
  }
}
</script>

<style>
page {
  padding-bottom: 60rpx;
}
.aduty-form-box {
	margin-top: 30rpx;
}
.aduty-form .btnbox {
  margin-top: 30rpx;
}
</style>

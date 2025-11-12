<template>
  <view class="page">
    <CustomTop top_title="联系方式"></CustomTop>
    <view class="container" v-if="!loading">
      <form class="aduty-form" @submit="formSubmit">
        <view class="aduty-form-box">
        	<view class="stitle"><span class="txt">微信</span></view>
          <view class="aduty-form-cell">
            <view class="aduty-form-cell-box">
              <view class="aduty-form-cell-bd">
                <input name="weixin" class="aduty-form-input" type="text" placeholder="请输入微信" :value="user_contact.weixin" />
              </view>
            </view>
          </view>
        </view>
        <view class="aduty-form-box">
        	<view class="stitle"><span class="txt">手机</span></view>
          <view class="aduty-form-cell">
            <view class="aduty-form-cell-box">
              <view class="aduty-form-cell-bd">
                <input name="phone" class="aduty-form-input" type="text" placeholder="请输入手机" :value="user_contact.phone" />
              </view>
            </view>
          </view>
        </view>
        <view class="aduty-form-box">
        	<view class="stitle"><span class="txt">Q Q</span></view>
          <view class="aduty-form-cell">
            <view class="aduty-form-cell-box">
              <view class="aduty-form-cell-bd">
                <input name="qq" class="aduty-form-input" type="text" placeholder="请输入QQ" :value="user_contact.qq" />
              </view>
            </view>
          </view>
        </view>
        <view class="aduty-form-box">
        	<view class="stitle"><span class="txt">电话</span></view>
          <view class="aduty-form-cell">
            <view class="aduty-form-cell-box">
              <view class="aduty-form-cell-bd">
                <input name="telphone" class="aduty-form-input" type="text" placeholder="请输入电话" :value="user_contact.telphone" />
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

  data() {
    return {
			loading: true,
      user_contact: {},
    }
  },

  onLoad: function (options) {
    this.getUserContact();
  },

  methods: {
    getUserContact: function() {
      uni.showLoading();
      request.post('/account/getUserContact').then(res => {
        uni.hideLoading();
        this.loading = false;
        this.user_contact = res.data;
      })
    },

    formSubmit: function(e) {
      uni.showLoading();
      let params = e.detail.value;
      request.post('/account/updateUserContact', params).then(res => {
        uni.hideLoading();
        if (res.code == 200) {
					this.getUserContact();
          uni.showToast({ icon: 'success', title: '保存成功' })
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
  padding-bottom: 30rpx;
}
.aduty-form-box {
  margin-top: 30rpx;
}
</style>

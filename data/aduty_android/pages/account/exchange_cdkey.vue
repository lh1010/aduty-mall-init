<template>
	<view>
    <CustomTop top_title="卡密"></CustomTop>
    <view class="container">
      <form class="aduty-form" @submit="formSubmit">
        <view class="aduty-form-box">
          <view class="stitle"><span class="txt">卡密内容</span></view>
          <view class="aduty-form-cell">
            <view class="aduty-form-cell-box">
              <view class="aduty-form-cell-bd">
                <input name="key" class="aduty-form-input" type="text" placeholder="请输入卡密内容" />
              </view>
            </view>
          </view>
        </view>
        <view class="aduty-form-action">
          <button class="aduty-btn aduty-btn-default" formType="submit">确认兑换</button>
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
    }
  },

  methods: {
    formSubmit: function(e) {
      let that = this;
      uni.showModal({
        content: '确认兑换？',
        success (res) {
          if (res.confirm) {
            uni.showLoading();
            let params = e.detail.value;
            request.post('/account/exchangeCdkey', params).then(res => {
              uni.hideLoading();
              if (res.code == 200) {
                uni.showToast({ title: '兑换成功', icon: 'success' });
              } else if (res.code == 400) {
                uni.showModal({ content: res.message, showCancel: false });
              }
            })
          }
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

<template>
	<view>
    <CustomTop top_title="身份认证"></CustomTop>
    <view class="container" v-if="!loading">
      <form class="aduty-form" @submit="formSubmit" v-if="user.realname_auth == 0">
        <view class="aduty-form-box">
          <view class="aduty-form-cell">
            <view class="aduty-form-cell-box">
              <view class="aduty-form-cell-hd">
                <view class="aduty-form-label">真实姓名</view>
              </view>
              <view class="aduty-form-cell-bd">
                <input name="realname" class="aduty-form-input" type="text" placeholder="请输入真实姓名" value="" />
              </view>
            </view>
          </view>
          <view class="aduty-form-cell">
            <view class="aduty-form-cell-box">
              <view class="aduty-form-cell-hd">
                <view class="aduty-form-label">身份证号</view>
              </view>
              <view class="aduty-form-cell-bd">
                <input name="idcard" class="aduty-form-input" type="text" placeholder="请输入身份证号" value="" />
              </view>
            </view>
          </view>
        </view>
        <view class="images">
          <view class="top_title">上传身份证照片</view>
          <view class="img_items">
            <view class="img_item" @click="uploadIdCard(1);">
              <view class="image">
                <image class="img" :src="identity_card1 != '' ? identity_card1 : '/static/images/identity_card1.png'" />
              </view>
              <view class="btn">上传正面照</view>
            </view>
            <view class="img_item" @click="uploadIdCard(2);">
              <view class="image">
                <image class="img" :src="identity_card2 != '' ? identity_card2 : '/static/images/identity_card2.png'" />
              </view>
              <view class="btn">上传反面照</view>
            </view>
          </view>
        </view>
        <view class="aduty-form-action">
          <button class="aduty-btn aduty-btn-default" formType="submit">提交信息</button>
        </view>
      </form>

      <view class="status_box" v-if="user.realname_auth != 0">
        <view class="" v-if="user.realname_auth == 1">
          <u-alert title="审核中,请耐心等待~" type="warning"></u-alert>
        </view>
        <view class="" v-if="user.realname_auth == 2">
          <u-alert title="审核失败,请重新认证~" type="error"></u-alert>
          <view style="margin-top: 10px;"><u-alert :description="'失败原因：' + (user.realname_auth_log && user.realname_auth_log.message ? user.realname_auth_log.message : '无')" type="error"></u-alert></view>
          <button class="aduty-btn aduty-btn-default" style="margin-top: 30rpx;" @click="realnameAuthReset">重新审核</button>
        </view>
        <view class="" v-if="user.realname_auth == 3">
          <u-alert title="已成功实名认证~" type="success"></u-alert>
        </view>
      </view>
    </view>
	</view>
</template>

<script>
import { request, upload } from "@/utils/http.js"
import CustomTop from "@/components/CustomTop.vue"

export default {
  components: { CustomTop },

  data() {
    return {
      loading: true,
      user: {},
      identity_card1: '',
      identity_card2: '',
    }
  },

  onLoad: function() {
    uni.showLoading();
    this.getLoginUser();
  },

  methods: {
    getLoginUser: function() {
      request.post('/account/getLoginUser').then(res => {
        this.loading = false;
        uni.hideLoading();
        this.user = res.data;
      })
    },

    uploadIdCard: function(ident = 1) {
      let that = this;
      uni.chooseImage({
        count: 1,
        sizeType: ['original', 'compressed'],
        sourceType: ['album', 'camera'],
        success (res) {
          uni.showLoading();
          let tempFilePaths = res.tempFilePaths;
          let formData = {type: 'user'};
          upload(tempFilePaths[0], formData).then(res => {
            uni.hideLoading();
            if (res.code == 200) {
              if (ident == 1) {
                that.identity_card1 = res.data.url;
              }
              if (ident == 2) {
                that.identity_card2 = res.data.url;
              }
            } else {
              uni.showToast({title: res.message, icon: 'none'});
            }
          })
        }
      })
    },

    formSubmit: function(e) {
      let that = this;
      uni.showModal({
        content: '确认提交？',
        success (res) {
          if (res.confirm) {
            uni.showLoading();
            let params = e.detail.value;
            params.idcard_img1 = that.identity_card1;
            params.idcard_img2 = that.identity_card2;
            request.post('/account/realnameAuth', params).then(res => {
              uni.hideLoading();
              if (res.code == 200) {
            		that.getLoginUser();
                uni.showToast({ icon: 'none', title: '操作成功' })
              } else if (res.code == 400) {
                uni.showToast({ title: res.message, icon: 'none' });
              }
            })
          }
        }
      })
    },

    realnameAuthReset: function() {
      let that = this;
      uni.showModal({
        content: '确认操作？',
        success (res) {
          if (res.confirm) {
            uni.showLoading();
            request.post('/account/realnameAuthReset').then(res => {
              uni.hideLoading();
              if (res.code == 200) {
                that.getLoginUser();
              } else if (res.code == 400) {
                uni.showToast({ title: res.message, icon: 'none' });
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
  padding-bottom: 60rpx;
}
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
	opacity: .7;
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
</style>

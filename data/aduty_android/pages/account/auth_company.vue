<template>
	<view>
    <CustomTop top_title="企业认证"></CustomTop>
    <view class="container" v-if="!loading">
      <form class="aduty-form" @submit="formSubmit" v-if="user.company_auth == 0">
        <view class="aduty-form-box">
          <view class="aduty-form-cell">
            <view class="aduty-form-cell-box">
              <view class="aduty-form-cell-hd">
                <view class="aduty-form-label">企业全称</view>
              </view>
              <view class="aduty-form-cell-bd">
                <input name="company_name" class="aduty-form-input" type="text" placeholder="请输入企业全称" value="" />
              </view>
            </view>
          </view>
          <view class="aduty-form-cell">
            <view class="aduty-form-cell-box">
              <view class="aduty-form-cell-hd">
                <view class="aduty-form-label">信用代码</view>
              </view>
              <view class="aduty-form-cell-bd">
                <input name="social_credit_code" class="aduty-form-input" type="text" placeholder="请输入信用代码" value="" />
              </view>
            </view>
          </view>
        </view>
        <view class="aduty-form-box">
					<view class="stitle"><span class="txt">营业执照</span></view>
          <view class="aduty-form-cell">
            <view class="aduty-form-cell-bd">
              <view>
                <u-upload
                  :fileList="fileList_business_license"
                  @afterRead="afterRead_images"
                  @delete="delete_images"
                  multiple
                  name="business_license"
                  :maxCount="1"
                />
              </view>
            </view>
          </view>
        </view>
        <view class="aduty-form-action">
          <button class="aduty-btn aduty-btn-default" formType="submit">提交信息</button>
        </view>
      </form>

      <view class="status_box" v-if="user.company_auth != 0">
        <view class="" v-if="user.company_auth == 1">
          <u-alert title="审核中,请耐心等待~" type="warning"></u-alert>
        </view>
        <view class="" v-if="user.company_auth == 2">
          <u-alert title="审核失败,请重新认证~" type="error"></u-alert>
          <view style="margin-top: 10px;"><u-alert :description="'失败原因：' + (user.company_auth_log && user.company_auth_log.message ? user.company_auth_log.message : '无')" type="error"></u-alert></view>
          <button class="aduty-btn aduty-btn-default" style="margin-top: 30rpx;" @click="companyAuthReset">重新审核</button>
        </view>
        <view class="" v-if="user.company_auth == 3">
          <u-alert title="已成功企业认证~" type="success"></u-alert>
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
      fileList_business_license: [],
      business_license: '',
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

    formSubmit: function(e) {
      let that = this;
      uni.showModal({
        content: '确认提交？',
        success (res) {
          if (res.confirm) {
            uni.showLoading();
            let params = e.detail.value;
            params.business_license = that.business_license;
            request.post('/account/companyAuth', params).then(res => {
              uni.hideLoading();
              if (res.code == 200) {
                that.getLoginUser();
                uni.showToast({ icon: 'none', title: '操作成功' });
              } else if (res.code == 400) {
                uni.showToast({ title: res.message, icon: 'none' });
              }
            })
          }
        }
      })
    },

    companyAuthReset: function() {
      let that = this;
      uni.showModal({
        content: '确认操作？',
        success (res) {
          if (res.confirm) {
            uni.showLoading();
            request.post('/account/companyAuthReset').then(res => {
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

    afterRead_images: function(event) {
      let lists = [].concat(event.file);
      let fileListLen = this[`fileList_${event.name}`].length;
      lists.map((item) => {
        this[`fileList_${event.name}`].push({
          ...item,
          status: 'uploading',
          message: '上传中'
        })
      })

      for (let i = 0; i < lists.length; i++) {
        upload(lists[i].url).then(res => {
          let item = this[`fileList_${event.name}`][fileListLen];
          this[`fileList_${event.name}`].splice(fileListLen, 1, Object.assign(item, {
            status: 'success',
            message: '',
            id: 0,
            url: res.data.url
          }))
          this[`${event.name}`] = res.data.url
          fileListLen++
        });
      }
    },

    delete_images: function(event) {
      this[`fileList_${event.name}`].splice(event.index, 1);
      this[`${event.name}`] = '';
    },
  }
}
</script>

<style>
page {
  padding-bottom: 40rpx;
}
.aduty-form-box {
  margin-top: 30rpx;
}
.status_box {
  margin-top: 50rpx;
}
</style>

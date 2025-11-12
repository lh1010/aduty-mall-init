<template>
	<view class="page">
    <CustomTop top_title="个人信息"></CustomTop>
    <form @submit="formSubmit" v-if="!loading">
      <view class="aduty-form">
        <view class="aduty-form-box aduty-form-right">
          <view class="aduty-form-cell aduty-form-avatar" @click="uploadAvatar">
            <view class="aduty-form-cell-box">
              <view class="aduty-form-cell-hd">头像</view>
              <view class="aduty-form-cell-bd">
                <image mode="aspectFill" :src="avatar" class="avatar" />
              </view>
              <view class="aduty-form-cell-ed"><i class="iconfont icon-youbian"></i></view>
            </view>
          </view>
          <view class="aduty-form-cell">
            <view class="aduty-form-cell-box">
              <view class="aduty-form-cell-hd">昵称</view>
              <view class="aduty-form-cell-bd">
                <input type="text" class="aduty-form-input" name="nickname" placeholder="请输入昵称" :value="loginUser.nickname" />
              </view>
            </view>
          </view>
          <view class="aduty-form-cell" @click="setSexShow">
            <view class="aduty-form-cell-box">
              <view class="aduty-form-cell-hd">性别</view>
              <view class="aduty-form-cell-bd">
                {{ sex ? sex : '请选择' }}
                <input hidden="true" name="sex" :value="sex" />
              </view>
              <view class="aduty-form-cell-ed"><i class="iconfont icon-youbian"></i></view>
            </view>
          </view>
          <view class="aduty-form-cell" @click="setCityShow">
            <view class="aduty-form-cell-box">
              <view class="aduty-form-cell-hd">地区</view>
              <view class="aduty-form-cell-bd">
                {{ city_name ? city_name : '请选择' }}
                <input hidden="true" name="city_id" :value="city_id" />
              </view>
              <view class="aduty-form-cell-ed"><i class="iconfont icon-youbian"></i></view>
            </view>
          </view>
        </view>
      </view>
      <view class="aduty-form-action">
        <button class="aduty-btn aduty-btn-default" formType="submit">保存资料</button>
        <button class="aduty-btn aduty-btn-secondary btn_logout" @click="logout">退出登录</button>
      </view>
    </form>

    <view class="user_id" v-if="!loading">
      <span class="span">USER ID</span>
      <span class="span">{{loginUser.id}}</span>
    </view>

    <u-picker
      :show="set_sex_show"
      :columns="set_sex_columns"
      @confirm="setSexConfirm"
      @close="setSexClose"
      @cancel="setSexClose"
      :closeOnClickOverlay="true"
    >
    </u-picker>
    <City
      :city_show="city_show"
      :use_all="false"
      :use_country="false"
      @setCityHide="setCityHide"
      @setCityReceive="setCityReceive"
    >
    </City>
	</view>
</template>

<script>
import { request, upload } from "@/utils/http.js"
import City from "@/components/City.vue"
import CustomTop from "@/components/CustomTop.vue"

export default {
  components: {
    City,
    CustomTop
  },

  data() {
    return {
      loading: true,
      loginUser: {},

      avatar: '',

      sex: '',
      set_sex_show: false,
      set_sex_columns: [],

      city_show: false,
      city_id: '',
      city_name: '',
    }
  },

  onShow: function() {
    this.getLoginUser();
  },

  methods: {
    getLoginUser: function() {
      uni.showLoading();
      request.post('/account/getLoginUser').then(res => {
        this.loading = false;
        uni.hideLoading();
        if (res.data.id) {
          this.loginUser = res.data;
          this.avatar = res.data.avatar;
          this.sex = res.data.sex;
        }
      })
    },

    formSubmit: function(e) {
      uni.showLoading();
      let params = e.detail.value;
      params.avatar = this.avatar;
      params.sex = this.sex;
      params.city_id = this.city_id;
      request.post('/account/updateUser', params).then(res => {
        uni.hideLoading();
        if (res.code == 200) {
          uni.showToast({ icon: 'none', title: '操作成功' });
        } else if (res.code == 400) {
          uni.showToast({ title: res.message, icon: 'none' });
        }
      })
    },

    uploadAvatar: function() {
      var that = this;
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
              that.avatar = res.data.url;
            } else {
              uni.showToast({title: res.message, icon: 'none'});
            }
          })
        }
      })
    },

    setSexShow: function() {
      this.set_sex_columns = [['男', '女']];
      this.set_sex_show = true;
    },

    setSexClose: function() {
      this.set_sex_show = false;
    },

    setSexConfirm: function(event) {
      this.sex = event.value[0];
      this.setSexClose();
    },

    setCityShow: function() {
      this.city_show = true;
    },

    setCityHide: function() {
      this.city_show = false;
    },

    setCityReceive: function(city_id, city_name) {
      this.city_name = city_name;
      this.city_id = city_id;
    },

    logout: function() {
      uni.showModal({
        title: '提示',
        content: '确认退出？',
        success (res) {
          if (res.confirm) {
            uni.showLoading({title: '退出中'});
            request.post('/account/logout').then((res) => {
              uni.removeStorageSync('user_token');
              uni.navigateBack({ delta: 1 });
            });
          }
        }
      })
    },

    switchTab: function(url) {
      uni.switchTab({ url: url });
    },

    jumpPage: function(url) {
      uni.navigateTo({ url: url });
    }
  }
}
</script>

<style>
.aduty-form-action {
  width: 700rpx;
  margin: 0 auto;
  margin-top: 50rpx;
}
.aduty-form-action .btn_logout {
  margin-top: 20rpx;
}

.user_id {
  text-align: center;
  opacity: .5;
  letter-spacing: 3px;
  color: #999;
  margin-top: 60rpx;
  font-size: 28rpx;
}
.user_id .span {
  margin-right: 8rpx;
}
</style>

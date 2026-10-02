<template>
	<view class="page">
    <CustomTop topTitle="个人信息"></CustomTop>
    <form v-if="!loading">
      <view class="aduty-form">
        <view class="aduty-form-box aduty-form-right">
          <view class="aduty-form-cell aduty-form-avatar" @click="uploadFun">
            <view class="aduty-form-cell-box">
              <view class="aduty-form-cell-hd"><view class="aduty-form-label">头像</view></view>
              <view class="aduty-form-cell-bd">
                <image mode="aspectFill" :src="form.avatar" class="avatar" />
              </view>
              <view class="aduty-form-cell-ed"><i class="iconfont icon-youbian"></i></view>
            </view>
          </view>
          <view class="aduty-form-cell">
            <view class="aduty-form-cell-box">
              <view class="aduty-form-cell-hd"><view class="aduty-form-label">昵称</view></view>
              <view class="aduty-form-cell-bd">
                <input type="text" class="aduty-form-input" v-model="form.nickname" placeholder="请输入昵称" />
              </view>
            </view>
          </view>
          <view class="aduty-form-cell">
            <picker mode="selector" :range="sexRange" :value="sexIndex" @change="onSexChange">
              <view class="aduty-form-cell-box">
                <view class="aduty-form-cell-hd"><view class="aduty-form-label">性别</view></view>
                <view class="aduty-form-cell-bd">
                  {{ form.sex ? form.sex : '请选择' }}
                </view>
                <view class="aduty-form-cell-ed"><i class="iconfont icon-youbian"></i></view>
              </view>
            </picker>
          </view>
          <view class="aduty-form-cell" @click="openPopupCity">
            <view class="aduty-form-cell-box">
              <view class="aduty-form-cell-hd">地区</view>
              <view class="aduty-form-cell-bd">
                {{ form.city_name ? form.city_name : '请选择' }}
              </view>
              <view class="aduty-form-cell-ed"><i class="iconfont icon-youbian"></i></view>
            </view>
          </view>
        </view>
      </view>
      <view class="aduty-form-action">
        <button class="aduty-btn aduty-btn-primary" hover-class="aduty-btn-hover" @click="formSubmit">保存资料</button>
        <button class="aduty-btn aduty-btn-secondary" hover-class="aduty-btn-hover" @click="logout">退出登录</button>
      </view>
    </form>

    <view class="user_id" v-if="!loading">
      <span class="span">USER ID</span>
      <span class="span">{{loginUser.id}}</span>
    </view>

    <City ref="cityRef" :useAll="false" :useCountry="false" @setCity="setCity" />
	</view>
</template>

<script setup>
import { ref, computed } from 'vue';
import { onLoad, onShow } from '@dcloudio/uni-app';
import City from "@/components/City.vue";
import CustomTop from "@/components/CustomTop.vue";
import http from "@/utils/http.js";

onLoad(() => {
  uni.setNavigationBarTitle({ title: '个人信息' });
});

const loading = ref(true);
const loginUser = ref({});
const form = ref({ nickname: '', sex: '', city_id: '', city_name: '', avatar: '' });
const sexRange = ref(['男', '女']);
const sexIndex = computed(() => {
  const idx = sexRange.value.indexOf(form.value.sex);
  return idx === -1 ? 0 : idx;
});

const cityRef = ref(null);
const openPopupCity = () => {
  cityRef.value?.openPopupCity();
};
const setCity = (id, name) => {
  form.value.city_id = id;
  form.value.city_name = name;
};

onShow(() => {
  getLoginUser();
});

const getLoginUser = () => {
  uni.showLoading();
  http.post('/account/getLoginUser').then(res => {
    loading.value = false;
    uni.hideLoading();
    if (res.data.id) {
      loginUser.value = res.data;
      form.value.nickname = res.data.nickname;
      form.value.sex = res.data.sex;
      form.value.city_id = res.data.city_id;
      form.value.city_name = res.data.city_name;
      form.value.avatar = res.data.avatar;
    }
  });
};

const formSubmit = () => {
  uni.showLoading();
  http.post('/account/updateUser', form.value).then(res => {
    uni.hideLoading();
    if (res.code == 200) {
      uni.showToast({ title: '操作成功', icon: 'none' });
    } else {
      uni.showToast({ title: res.message || '操作失败', icon: 'none' });
    }
  });
};

const uploadFun = () => {
  uni.chooseImage({
    count: 1,
    sizeType: ['original', 'compressed'],
    sourceType: ['album', 'camera'],
    success(res) {
      uni.showLoading();
			const formData = { source: 'user_avatar' };
      const tempFilePaths = res.tempFilePaths;
      http.upload('/upload', tempFilePaths[0], formData).then(res => {
        uni.hideLoading();
        if (res.code == 200) {
          form.value.avatar = res.data.url;
        } else {
          uni.showToast({ title: res.message || '操作失败', icon: 'none' });
        }
      });
    }
  });
};

const onSexChange = (e) => {
  form.value.sex = sexRange.value[e.detail.value];
};

const logout = () => {
  uni.showModal({
    content: '确认退出？',
    success(res) {
      if (res.confirm) {
        uni.showLoading();
        http.post('/account/logout').then(() => {
          uni.removeStorageSync('token');
          uni.navigateBack({ delta: 1 });
        });
      }
    }
  });
};
</script>

<style>
.aduty-form-action {
  width: 700rpx;
  margin: 0 auto;
  margin-top: 50rpx;
}
.aduty-form-action .aduty-btn:last-child {
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

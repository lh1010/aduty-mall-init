<template>
	<view class="page">
		<CustomTop topTitle="添加地址"></CustomTop>
		<view class="address_create">
			<form class="aduty-form" v-if="!result">
				<view class="aduty-form-box">
				  <view class="aduty-form-cell">
            <view class="aduty-form-cell-box">
              <view class="aduty-form-cell-hd">
                <view class="aduty-form-label">联系人姓名</view>
              </view>
              <view class="aduty-form-cell-bd">
                <input v-model="form.name" class="aduty-form-input" type="text" placeholder="请输入姓名" />
              </view>
            </view>
				  </view>
					<view class="aduty-form-cell">
            <view class="aduty-form-cell-box">
              <view class="aduty-form-cell-hd">
                <view class="aduty-form-label">联系人电话</view>
              </view>
              <view class="aduty-form-cell-bd">
                <input v-model="form.phone" class="aduty-form-input" type="text" placeholder="请输入电话" />
              </view>
            </view>
					</view>
					<view class="aduty-form-cell">
            <view class="aduty-form-cell-box">
              <view class="aduty-form-cell-hd">
                <view class="aduty-form-label">所在地区</view>
              </view>
              <view class="aduty-form-cell-bd" @click="openPopupAddress">
                {{ regions != '' ? regions : '请选择' }}
              </view>
              <view class="aduty-form-cell-ed">
                <i class="iconfont icon-youbian"></i>
              </view>
            </view>
					</view>
					<view class="aduty-form-cell">
            <view class="aduty-form-cell-box">
              <view class="aduty-form-cell-hd">
                <view class="aduty-form-label">详细地址</view>
              </view>
              <view class="aduty-form-cell-bd">
                <input v-model="form.detailed_address" class="aduty-form-input" type="text" placeholder="请输入详细地址" />
              </view>
            </view>
					</view>
					<view class="aduty-form-cell">
					  <view class="aduty-form-cell-box">
							<view class="aduty-form-cell-hd">
							  <view class="aduty-form-label">默认地址</view>
							</view>
					    <view class="aduty-form-cell-bd">
								<radio color="#f4645f" :checked="defaultStatus" @click="setDefaultStatus" />
					    </view>
					  </view>
					</view>
				</view>
				<view class="container">
					<view class="btnbox">
					  <button class="aduty-btn-primary" hover-class="aduty-btn-hover" @click="formSubmit">保存地址</button>
					</view>
				</view>
			</form>
			<view class="result" v-if="result">
				<view class="txt"><i class="iconfont icon-yuanxingxuanzhong"></i>操作成功</view>
				<view class="btns">
					<button class="aduty-btn aduty-btn-primary" hover-class="aduty-btn-hover" @click="goBack">返回上一页</button>
				</view>
			</view>
		</view>

		<uni-popup ref="popupRef" type="bottom">
			<view class="popup_address">
				<view class="btop">
					<span class="stitle">选择地址</span>
					<i class="iconfont icon-guanbicopy close" @click="closePopupAddress"></i>
				</view>
				<view class="tabs">
					<!-- 已选省份tab 有列表才显示 -->
					<view class="item" :class="addressLevel == 1 ? 'on' : ''" v-if="provinceList.length > 0" @click="resetAddress(1)">
						{{ province.name || '请选择' }}
					</view>
					<!-- 已选城市tab -->
					<view class="item" :class="addressLevel == 2 ? 'on' : ''" v-if="cityList.length > 0" @click="resetAddress(2)">
						{{ city.name || '请选择' }}
					</view>
					<!-- 已选区县tab -->
					<view class="item" :class="addressLevel == 3 ? 'on' : ''" v-if="districtList.length > 0" @click="resetAddress(3)">
						{{ district.name || '请选择' }}
					</view>
				</view>
				<view class="items">
					<!-- 显示当前级别的选项列表 -->
					<view class="item" :class="item.id == currentSelected.id ? 'on' : ''" v-for="item in currentList" :key="item.id" @click="selectRegion(item)">
						{{item.name}}
					</view>
				</view>
			</view>
		</uni-popup>
	</view>
</template>

<script setup>
import { ref, computed } from 'vue';
import { onLoad } from '@dcloudio/uni-app';
import http from "@/utils/http.js";
import CustomTop from "@/components/CustomTop.vue";

const defaultStatus = ref(false);
const result = ref(false);
const form = ref({ name: '', phone: '', detailed_address: '' });

const popupRef = ref(null);
// 地址选择级别 1=省 2=市 3=区
const addressLevel = ref(1);
// 已选中的省市区 {id, name}
const province = ref({ id: '', name: '' });
const city = ref({ id: '', name: '' });
const district = ref({ id: '', name: '' });
// 省市区选项列表
const provinceList = ref([]);
const cityList = ref([]);
const districtList = ref([]);
// 已选地区文案 如 广东省,深圳市,南山区
const regions = ref('');

const currentList = computed(() => {
  if (addressLevel.value == 1) return provinceList.value;
  if (addressLevel.value == 2) return cityList.value;
  return districtList.value;
});

const currentSelected = computed(() => {
  if (addressLevel.value == 1) return province.value;
  if (addressLevel.value == 2) return city.value;
  return district.value;
});

onLoad(() => {
  uni.setNavigationBarTitle({ title: '添加地址' });
  getCityList(0, 1);
});

// 获取省市区列表
const getCityList = (pid, level) => {
  http.post('/common/getCityList', { pid }).then(res => {
		uni.hideLoading();
    if (level == 1) {
      provinceList.value = res.data;
    } else if (level == 2) {
      cityList.value = res.data;
    } else {
      districtList.value = res.data;
    }
  });
};

// 点击选择省市区选项
const selectRegion = (item) => {
  const level = addressLevel.value;
  // 记录当前级别的选中值
  currentSelected.value.id = item.id;
  currentSelected.value.name = item.name;
  if (level == 3) {
    // 区选完 拼接文案并关闭弹窗
    regions.value = [province.value.name, city.value.name, district.value.name].join(',');
    closePopupAddress();
    return;
  }
  // 清空下级的选择和列表
  if (level == 1) {
    city.value = { id: '', name: '' };
    district.value = { id: '', name: '' };
    cityList.value = [];
    districtList.value = [];
  } else {
    district.value = { id: '', name: '' };
    districtList.value = [];
  }
  // 进入下一级并加载列表
  addressLevel.value = level + 1;
  uni.showLoading();
  getCityList(item.id, addressLevel.value);
};

// 切换级别 点击tab
const resetAddress = (level) => {
  addressLevel.value = level;
};

// 打开地址弹窗
const openPopupAddress = () => {
  popupRef.value?.open();
};

// 关闭地址弹窗
const closePopupAddress = () => {
  popupRef.value?.close();
};

const setDefaultStatus = () => {
  defaultStatus.value = !defaultStatus.value;
};

const formSubmit = () => {
	if (!form.value.name) {
		uni.showToast({ title: '请输入联系人姓名', icon: 'none' });
		return;
	}
	if (!form.value.phone) {
		uni.showToast({ title: '请输入联系人电话', icon: 'none' });
		return;
	}
	if (!/^1[3-9]\d{9}$/.test(form.value.phone)) {
		uni.showToast({ title: '请输入正确的手机号', icon: 'none' });
		return;
	}
	if (!regions.value) {
		uni.showToast({ title: '请选择所在地区', icon: 'none' });
		return;
	}
	if (!form.value.detailed_address) {
		uni.showToast({ title: '请输入详细地址', icon: 'none' });
		return;
	}
  uni.showLoading();
  const params = {
    ...form.value,
    province_id: province.value.id,
    province_name: province.value.name,
    city_id: city.value.id,
    city_name: city.value.name,
    district_id: district.value.id,
    district_name: district.value.name,
    default: defaultStatus.value ? 1 : 0,
  };
  http.post('/address/store', params).then(res => {
    uni.hideLoading();
    if (res.code == 200) {
      result.value = true;
    } else if (res.code == 400) {
      uni.showToast({ title: res.message || '操作失败', icon: 'none' });
    }
  });
};

const goBack = () => {
  uni.navigateBack();
};
</script>

<style>
@import url("address.css");
.page {
	padding-bottom: 30rpx;
}
</style>

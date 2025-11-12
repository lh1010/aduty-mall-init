<template>
	<view class="page">
		<CustomTop top_title="修改地址"></CustomTop>
		<view class="address_create" v-if="!loading">
			<form class="aduty-form" @submit="formSubmit" v-if="!result">
				<view class="aduty-form-box">
				  <view class="aduty-form-cell">
            <view class="aduty-form-cell-box">
              <view class="aduty-form-cell-hd">
                <view class="aduty-form-label">联系人姓名</view>
              </view>
              <view class="aduty-form-cell-bd">
                <input name="name" class="weui-input" type="text" :value="address.name" placeholder="请输入联系人姓名" />
              </view>
            </view>
				  </view>
					<view class="aduty-form-cell">
            <view class="aduty-form-cell-box">
              <view class="aduty-form-cell-hd">
                <view class="aduty-form-label">联系人电话</view>
              </view>
              <view class="aduty-form-cell-bd">
                <input name="phone" class="aduty-form-input" type="text" :value="address.phone" placeholder="请输入联系人电话" />
              </view>
            </view>
					</view>
					<view class="aduty-form-cell">
            <view class="aduty-form-cell-box">
              <view class="aduty-form-cell-hd">
                <view class="aduty-form-label">所在地区</view>
              </view>
              <view class="aduty-form-cell-bd" @click="onPopupShow_address">
                {{ regions != '' ? regions : '请选择' }}
                <input hidden="true" name="province_id" :value="province_id" />
              	<input hidden="true" name="province_name" :value="province_name" />
              	<input hidden="true" name="city_id" :value="city_id" />
              	<input hidden="true" name="city_name" :value="city_name" />
              	<input hidden="true" name="district_id" :value="district_id" />
              	<input hidden="true" name="district_name" :value="district_name" />
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
                <input name="detailed_address" class="weui-input" type="text" :value="address.detailed_address" placeholder="请输入详细地址" />
              </view>
            </view>
					</view>
				</view>
				<view class="remind">
					<radio color="#f4645f" :checked="default_status" @click="setDefaultStatus" />
					<view class="txt">设为默认地址</view>
				</view>
				<view class="container">
					<view class="btnbox">
					  <button class="aduty-btn btn" formType="submit">保存地址</button>
					</view>
				</view>
			</form>

			<view class="result" v-if="result">
				<view class="txt"><i class="iconfont icon-yuanxingxuanzhong"></i>操作成功</view>
				<view class="btns">
					<button class="aduty-btn aduty-btn-default" @click="goBack">返回上一页</button>
				</view>
			</view>
		</view>

		<u-popup
		  :show="popupShow_address"
			@close="onPopupClose_address"
		  mode="bottom"
		>
			<view class="popup_address">
				<view class="btop">
					<span class="stitle">选择地址</span>
					<i class="iconfont iconguanbicopy close" @click="onPopupClose_address"></i>
				</view>
				<view class="tabs">
					<view class="item" :class="popup_address_location == 1 ? 'on' : ''" v-if="province_res.length > 0" @click="resetAddress(1)">
						{{ province_name || '请选择' }}
					</view>
					<view class="item" :class="popup_address_location == 2 ? 'on' : ''" v-if="city_res.length > 0" @click="resetAddress(2)">
						{{ city_name || '请选择' }}
					</view>
					<view class="item" :class="popup_address_location == 3 ? 'on' : ''" v-if="district_res.length > 0" @click="resetAddress(3)">
						{{ district_name || '请选择' }}
					</view>
				</view>
				<view class="items">
					<view v-if="popup_address_location == 1">
						<view
							class="item"
							:class="item.id == province_id ? 'on' : ''"
							v-for="(item, index) in province_res"
							:key="index"
							@click="setAddress(item.id, item.name, 1)"
						>
							{{item.name}}
						</view>
					</view>
					<view v-if="popup_address_location == 2">
						<view
							class="item"
							:class="item.id == city_id ? 'on' : ''"
							v-for="(item, index) in city_res"
							:key="index"
							@click="setAddress(item.id, item.name, 2)"
						>
							{{item.name}}
						</view>
					</view>
					<view v-if="popup_address_location == 3">
						<view
							class="item"
							:class="item.id == district_id ? 'on' : ''"
							v-for="(item, index) in district_res"
							:key="index"
							@click="setAddress(item.id, item.name, 3)"
						>
							{{item.name}}
						</view>
					</view>
				</view>
			</view>
		</u-popup>

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
			address: {},
			default_status: false,
			result: false,

			popupShow_address: false,
			popup_address_location: 1,
			province_id: '',
			province_name: '',
			city_id: '',
			city_name: '',
			district_id: '',
			district_name: '',
			province_res: [],
			city_res: [],
			district_res: [],
			regions: '',
		}
	},

	onLoad(options) {
		this.id = options.id;
		this.getAddress();
	},

	methods: {
		formSubmit: function(e) {
			uni.showLoading();
			let params = e.detail.value;
			params.id = this.address.id;
			params.default = this.default_status ? 1 : 0;
			request.post('/address/update', params).then(res => {
				uni.hideLoading();
				if (res.code == 200) {
					this.result = true;
				} else if (res.code == 400) {
					uni.showToast({ title: res.message, icon: 'none' });
				}
			})
		},

		getAddress: function() {
			uni.showLoading();
			request.post('/address/getAddress', {id: this.id}).then(res => {
				uni.hideLoading();
				this.loading = false;
				this.address = res.data;

				this.province_id = this.address.province_id;
				this.province_name = this.address.province_name;
				this.city_id = this.address.city_id;
				this.city_name = this.address.city_name;
				this.district_id = this.address.district_id;
				this.district_name = this.address.district_name;
				this.regions = this.province_name + ',' + this.city_name + ',' + this.district_name;

				this.getCityList(0, 1);
				this.getCityList(this.province_id, 2);
				this.getCityList(this.city_id, 3);
				this.popup_address_location = 3;

				this.default_status = this.address.default == 1 ? true : false;
			})
		},

		getCityList: function(pid, popup_address_location) {
			this.citys = [];
			let params = {};
			params.pid = pid;
			request.post('/common/getCityList', params).then(res => {
				uni.hideLoading();
				if (popup_address_location == 1) {
					this.province_res = res.data;
				}
				if (popup_address_location == 2) {
					this.city_res = res.data;
				}
				if (popup_address_location == 3) {
					this.district_res = res.data;
				}
			})
		},

		setAddress: function(id, name, popup_address_location) {
			if (popup_address_location == 1) {
				this.popup_address_location = 2;
				this.province_id = id;
				this.province_name = name;

				this.city_id = '';
				this.city_name = '';
				this.district_id = '';
				this.district_name = '';
				this.city_res = [];
				this.district_res = [];
			} else if (popup_address_location == 2) {
				this.popup_address_location = 3;
				this.city_id = id;
				this.city_name = name;

				this.district_id = '';
				this.district_name = '';
				this.district_res = [];
			} else if (popup_address_location == 3) {
				this.district_id = id;
				this.district_name = name;

				this.regions = this.province_name + ',' + this.city_name + ',' + this.district_name;
				this.onPopupClose_address();
				return false;
			}
			uni.showLoading();
			this.getCityList(id, this.popup_address_location);
			this.$forceUpdate();
		},

		resetAddress: function(popup_address_location) {
			this.popup_address_location = popup_address_location;
		},

		onPopupShow_address: function() {
			this.popupShow_address = true;
		},

		onPopupClose_address: function() {
			this.popupShow_address = false;
		},

		setDefaultStatus: function() {
			this.default_status = this.default_status ? false : true;
		},

		goBack: function() {
		  uni.navigateBack();
		},

		switchTab: function(url) {
			uni.switchTab({ url: url });
		},

		jumpPage: function(url) {
			uni.navigateTo({ url: url })
		}
	}
}
</script>

<style>
@import url("address.css");
.page {
	padding-bottom: 30rpx;
}
.btnbox {
	margin-top: 50rpx;
}
.remind {
  margin: 0 auto;
  display: flex;
	margin-top: 30rpx;
	background-color: #fff;
	padding: 30rpx;
}
.remind .txt {
  margin-left: 3px;
  padding-top: 3px;
}
.remind .link {
  color: #f4645f;
}
</style>

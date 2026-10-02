<template>
	<view class="page">
		<CustomTop topTitle="我的地址"></CustomTop>
		<view class="address_list" v-if="!dataListLoading">
			<view v-if="dataList.length > 0">
				<view class="items">
					<uni-swipe-action>
						<uni-swipe-action-item :rightOptions="options" @click="delAction(item.id)" v-for="(item, index) in dataList" :key="index">
							<view class="item">
								<view class="info">
									<view class="vm100">
										<span class="default" v-if="item.default == 1">[默认]</span>
										{{item.province_name}} {{item.city_name}} {{item.district_name}} {{item.detailed_address}}
									</view>
									<view class="vm101">{{item.name}} {{item.phone}}</view>
								</view>
								<view class="action">
									<i class="iconfont icon-bianji" @click="uni.navigateTo({url: '/pages/address/edit?id=' + item.id})"></i>
									<i class="iconfont icon-shanchu1" @click="delAction(item.id)"></i>
								</view>
							</view>
						</uni-swipe-action-item>
					</uni-swipe-action>
				</view>
			</view>
			<view class="aduty-empty" v-if="dataList.length == 0">
				<image class="aduty-empty-img" src="/static/images/empty.png" mode="aspectFit"></image>
				<text class="aduty-empty-text">暂无内容</text>
			</view>
			<view class="footbtn_blank"></view>
			<view class="footbtn" @click="uni.navigateTo({url: '/pages/address/create'})">添加新地址</view>
		</view>
	</view>
</template>

<script setup>
import { ref } from 'vue';
import { onLoad, onShow, onPullDownRefresh } from '@dcloudio/uni-app';
import http from "@/utils/http.js";
import CustomTop from "@/components/CustomTop.vue";

onLoad(() => {
  uni.setNavigationBarTitle({ title: '我的地址' });
});

const dataList = ref([]);
const dataListLoading = ref(true);
const options = ref([{
  text: '删除',
  style: { backgroundColor: '#f56c6c' }
}]);

onShow(() => {
  uni.showLoading();
  getList();
});

onPullDownRefresh(() => {
  getList();
});

const getList = () => {
  http.post('/address/getList').then(res => {
    uni.stopPullDownRefresh();
    uni.hideLoading();
    dataListLoading.value = false;
    dataList.value = res.data;
  });
};

const delAction = (id) => {
  uni.showModal({
    content: '确定删除？',
    success(res) {
      if (res.confirm) {
        uni.showLoading();
        http.post('/address/delete', { id }).then((res) => {
          if (res.code == 200) {
						uni.showToast({ icon: 'none', title: '删除成功' });
            const idx = dataList.value.findIndex(item => item.id == id);
						dataList.value.splice(idx, 1);
          } else if (res.code == 400) {
            uni.showToast({ title: res.message || '删除失败', icon: 'none' });
          }
        });
      }
    }
  });
};
</script>

<style>
@import url("address.css");
</style>

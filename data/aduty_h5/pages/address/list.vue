<template>
	<view class="page">
		<CustomTop top_title="我的地址"></CustomTop>
		<view class="address_list" v-if="!data_list_loading">
			<view v-if="data_list.length > 0">
				<view class="items">
					<u-swipe-action>
						<u-swipe-action-item :options="options1" @click="delAction(item.id)" v-for="(item, index) in data_list" :key="index">
							<view class="item">
								<view class="info">
									<view class="vm1">
										<span class="default" v-if="item.default == 1">默认</span>
										{{item.province_name}} {{item.city_name}} {{item.district_name}} {{item.detailed_address}}
									</view>
									<view class="vm2">
										{{item.name}} {{item.phone}}
									</view>
								</view>
								<i class="iconfont icon-bianji" @click="jumpPage('/pages/address/edit?id=' + item.id)"></i>
							</view>
						</u-swipe-action-item>
					</u-swipe-action>
				</view>
			</view>
			<u-empty
				v-if="data_list.length == 0"
				mode="data"
				icon="/static/images/empty.png"
				text="暂无内容~"
			>
			</u-empty>
			<view class="footbtn_blank"></view>
			<view class="footbtn" @click="jumpPage('/pages/address/create')">添加新地址</view>
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
			data_list: [],
			data_list_loading: true,
			options1: [{
				text: '删除',
				style: {
					backgroundColor: '#f56c6c'
				}
			}],
		}
	},

	onShow() {
    uni.showLoading();
		this.getAddresses();
	},

  onPullDownRefresh: function() {
    uni.showLoading();
    this.getAddresses();
  },

	methods: {
		getAddresses: function() {
		  let params = this.params;
		  request.post('/address/getAddresses', params).then(res => {
		    uni.stopPullDownRefresh();
		    uni.hideLoading();
		    this.data_list_loading = false;
				this.data_list = res.data;
		  })
		},

		delAction(id) {
			let that = this;
			uni.showModal({
				content: '确定删除？',
				success (res) {
					if (res.confirm) {
						uni.showLoading();
						request.post('/address/delete', {id: id}).then((res) => {
							if (res.code == 200) {
								that.deleteAfter(id);
								uni.showToast({
									icon: 'none',
									title: '删除成功',
									mask: true
								});
							} else if (res.code == 400) {
								uni.showToast({ title: res.message, icon: 'none' });
							}
						});
					}
				}
			})
		},

		// 删除后
		deleteAfter: function(id) {
			for (var i = 0; i < this.data_list.length; i++) {
				if (this.data_list[i].id == id) {
					this.data_list.splice(i, 1);
				}
			}
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
</style>

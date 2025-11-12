<template>
	<view>
    <CustomTopIndex :top_title="top_title"></CustomTopIndex>
		<view class="banner" v-if="banner_list.length > 0">
			<swiper class="swiper"
				circular
				:autoplay="true"
				:style="{height: `${swiper_height}`}"
			>
				<swiper-item
					v-for="(item, index) in banner_list"
					:key="index"
					@click="jumpPage(item.open_mode == 1 ? item.url : '/pages/index/out?url=' + item.url)"
				>
					<view class="swiper-item">
						<image class="img" mode="widthFix" :src="item.image" @load="onLoadImg" />
					</view>
				</swiper-item>
			</swiper>
		</view>
		<view class="sudoku" v-if="sudoku_list.length > 0">
			<view class="container">
				<view
					class="item"
					v-for="(item, index) in sudoku_list"
					:key="index"
					@click="item.open_mode == 1 ? jumpPage(item.url) : switchTab(item.url)"
				>
					<image class="img" :src="item.image" />
					<view class="txt">{{item.title}}</view>
				</view>
			</view>
		</view>
		<view class="prolist" v-if="!data_list_loading">
			<view class="container">
				<view v-if="data_list.length > 0">
					<view class="items">
						<view class="item" @click="jumpPage('/pages/product/show?sku=' + item.sku + '&product_id=' + item.id)" v-for="(item, index) in data_list" :key="index">
							<image class="cover" :src="item.cover" mode="aspectFit" />
							<view class="info">
								<view class="name">
									<span class="txt">{{item.name}}</span>
								</view>
								<view class="price aduty-text-price">¥{{item.price}}</view>
							</view>
						</view>
					</view>
					<view class="uloadmore"><u-loadmore :status="loadmore_status" /></view>
				</view>
				<u-empty
					v-if="data_list.length == 0"
					mode="data"
					icon="/static/images/empty.png"
					text="暂无内容~"
				>
				</u-empty>
			</view>
		</view>
	</view>
</template>

<script>
import CustomTopIndex from "@/components/CustomTopIndex.vue"
import { request } from "@/utils/http.js"
export default {
	components: { CustomTopIndex },

	data() {
		return {
      top_title: '',
			logined: false,
			data_list: [],
			data_list_loading: true,
			loadmore_status: 'loadmore',
			loadmore_finished: false,
			params: {
				page_size: 15,
				page: 1,
			},
			banner_list: [],
			sudoku_list: [],
			swiper_height: 0,
			config: {},
		}
	},

	onLoad() {
		uni.showLoading();

		request.post('/common/getConfig').then(res => {
			this.top_title = res.data.app_name;
			uni.setNavigationBarTitle({ title: res.data.app_name });
      this.config = res.data;
		})

		request.post('/common/getAdver', {code: 'h5_index_banner'}).then(res => {
			if (res.data.values) {
				this.banner_list = res.data.values;
			}
		})

		request.post('/common/getAdver', {code: 'h5_index_sudoku'}).then(res => {
		  if (res.data.values) {
		    this.sudoku_list = res.data.values;
		  }
		})

		this.getInit();
	},

	onShow: function () {},

	onReachBottom() {
	  this.getMore();
	},

	onPullDownRefresh: function() {
	  this.getInit();
	},

	methods: {
		getList: function() {
			let params = this.params;
			request.post('/product/getList', params).then(res => {
				uni.stopPullDownRefresh();
				uni.hideLoading();
				this.data_list_loading = false;

				// 返回数据为空
				if (res.data.total == 0) {
					this.data_list = [];
					return false;
				}

				// 组装数据
				if (res.data.current_page == 1) {
					this.data_list = res.data.data;
				} else {
					this.data_list = this.data_list.concat(res.data.data);
				}

				// 最后一页
				if (this.params.page == res.data.last_page) {
					this.loadmore_finished = true;
					this.loadmore_status = 'nomore';
					return false;
				}

				this.params.page = parseInt(res.data.current_page) + parseInt(1);
				this.loadmore_status = 'loadmore';
				this.loadmore_finished = false;
			})
		},

		// 初始化请求
		getInit: function() {
			this.data_list_loading = true;
			this.loadmore_status = 'loadmore';
			this.params.page = 1;
			this.getList();
		},

		// 加载更多
		getMore: function() {
			if (!this.loadmore_finished) {
				this.loadmore_status = 'loading';
				this.getList();
			}
		},

    onLoadImg: function(e) {
      //获取可使用窗口宽度
      var width = uni.getSystemInfoSync().windowWidth;
      //获取图片实际高度
      var imgheight = e.detail.height;
      //获取图片实际宽度
      var imgwidth = e.detail.width;
      var height = width * imgheight / imgwidth + "px";
      this.swiper_height = height;
    },

		switchTab: function(url) {
			uni.switchTab({ url: url });
		},

		jumpPage: function(url) {
			uni.navigateTo({ url: url });
		},
	}
}
</script>

<style>
@import url("index.css");
@import url("../product/product.css");
page {
	padding-bottom: 30rpx;
}
</style>

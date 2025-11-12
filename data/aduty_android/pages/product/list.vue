<template>
	<view class="page">
		<CustomTop :top_title="top_title"></CustomTop>
    <view class="top_search">
      <view class="container">
        <view class="top_search_box">
          <view class="icon"><i class="iconfont icon-sousuoxiao"></i></view>
          <view class="input"><input class="weui-input" type="text" placeholder="请输入搜索内容" v-model="params.k" @confirm="doSearch" /></view>
        </view>
      </view>
    </view>
    <view class="top_search_blank"></view>
    <view class="wnav">
      <view class="sv_nav">
      	<view class="items box">
      		<view class="item svItem" :class="params.order == 1 ? 'on' : ''" @click="setOrder(1)">默认</view>
					<view class="item svItem" :class="params.order == 2 ? 'on' : ''" @click="setOrder(2)">最新</view>
					<view class="item svItem" :class="[4, 5].includes(params.order) ? 'on' : ''" @click="setOrderPrice()">
						价格
						<i class="iconfont icon-shangxiayidong" v-if="![4, 5].includes(params.order)"></i>
						<i class="iconfont icon-jiantou_xiangshang" v-if="params.order == 4"></i>
						<i class="iconfont icon-jiantou_xiangxia" v-if="params.order == 5"></i>
					</view>
					<view class="item svItem" :class="params.order == 3 ? 'on' : ''" @click="setOrder(3)">销量</view>
      	</view>
      </view>
    </view>
		<view class="wnav_blank"></view>

		<view class="product_list" v-if="!data_list_loading">
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
import { request } from "@/utils/http.js"
import CustomTop from "@/components/CustomTop.vue"

export default {
  components: { CustomTop },

  data() {
    return {
      top_title: '商品列表',
			data_list: [],
			data_list_loading: true,
			loadmore_status: 'loadmore',
			loadmore_finished: false,
			params: {
				page_size: 15,
				page: 1,
        K: '',
        order: '',
        category_id: '',
			},
      category: {},
		}
  },

	onLoad(options) {
    Object.assign(this.params, options);

		uni.showLoading();
		this.getInit();

    if (options.category_id != undefined) {
    	request.post('/product/getCategory', { id: options.category_id }).then(res => {
    	  this.category = res.data;
        this.top_title = res.data.name;
        uni.setNavigationBarTitle({ title: res.data.name });
    	})
    }
	},

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

		setOrder: function(order) {
			this.order = order;
			uni.showLoading();
			this.params.order = this.order;
			this.getInit();
		},

		setOrderPrice: function() {
			if (![4, 5].includes(this.order)) {
				this.order = 4;
			} else if (this.order == 4) {
				this.order = 5;
			} else if (this.order = 5) {
				this.order = 4;
			}
			uni.showLoading();
			this.params.order = this.order;
			this.getInit();
		},

    doSearch: function() {
      uni.showLoading();
      this.getInit();
    },

		switchTab: function(url) {
			uni.switchTab({ url: url });
		},

		jumpPage: function(url) {
			uni.navigateTo({ url: url })
		}
	},
}
</script>

<style>
@import url("product.css");
page {
	padding-bottom: 30rpx;
	background-color: #fff;
}
</style>

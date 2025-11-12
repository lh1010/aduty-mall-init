<template>
  <view class="account_index">
    <view class="customtop">
      <view class="item left"></view>
      <view class="item title">购物车</view>
      <view class="item right" @click="setManage">
				<span v-if="logined && manage_ident == 0">编辑</span>
				<span v-if="logined && manage_ident == 1">完成</span>
			</view>
    </view>
    <view class="customtop_blank"></view>

    <view class="cart" v-if="!loading">
			<view class="empty_data" v-if="!logined">
				<image class="cart_img" src="/static/images/foot_icon_cart.png" mode="scaleToFill" />
				<view class="msg">登录后可同步购物车的产品～</view>
				<view class="btn" @click="jumpPage('/pages/account/login_password')">请先登录</view>
			</view>
			<view v-if="logined && !data_loading">
				<view class="empty_data" v-if="products.length == 0">
					<image class="cart_img" src="/static/images/foot_icon_cart.png" mode="scaleToFill" />
					<view class="msg">购物车空空如也～</view>
					<view class="btn" @click="switchTab('/pages/index/index')">去逛逛</view>
				</view>
				<view class="data_list" v-if="products.length > 0">
					<view class="items">
						<view class="item_product" v-for="(item, index) in products" :key="index">
							<i
								class="iconfont icon-yuanxingweixuanzhong icon_selected"
								:class="item.selected == 1 ? 'on' : ''"
								@click="setSelected_product(item.sku)"
							>
							</i>
							<image class="cover" :src="item.cover" mode="scaleToFill" @click="jumpPage('/pages/product/show?id=' + item.id + '&sku=' + item.sku)" />
							<view class="info" @click="jumpPage('/pages/product/show?id=' + item.id + '&sku=' + item.sku)">
								<view class="name">
									<span class="txt">{{item.name}}</span>
								</view>
                <view class="types" v-if="item.specifications.length > 0">
                  <view class="types_item" v-for="(item_specification, index_specification) in item.specifications" :key="index_specification">
                    {{item_specification.specification_name}}-{{item_specification.specification_option}}
                  </view>
                </view>
								<view class="pricebox">
									<span class="price">¥{{item.price}}</span>
									<span class="number">x{{item.count}}</span>
								</view>
							</view>
						</view>
					</view>

					<view class="foot_action" v-if="manage_ident == 0">
						<view class="left">
							<view class="selectbox">
								<i
									class="iconfont icon-yuanxingweixuanzhong icon_selected"
									:class="totalData.all_selected == 1 ? 'on' : ''"
									@click="setSelected_all"
								>
								</i>
								全选
							</view>
						</view>
						<view class="right">
							<view class="pricebox"><span class="em">合计：</span><span class="price">¥{{totalData.total_price}}</span></view>
							<view class="btn" @click="jumpPage('/pages/checkout/cart')">去结算</view>
						</view>
					</view>
					<view class="foot_action" v-if="manage_ident == 1">
						<view class="left">
							<view class="selectbox">
								<i
									class="iconfont icon-yuanxingweixuanzhong icon_selected"
									:class="totalData.all_selected == 1 ? 'on' : ''"
									@click="setSelected_all"
								>
								</i>
								全选
							</view>
						</view>
						<view class="right">
							<view class="btn btn-delete" @click="deleteCart">删除选中</view>
						</view>
					</view>
				</view>
			</view>
    </view>
  </view>
</template>

<script>
import { request } from "@/utils/http.js"

export default {
  data() {
    return {
      loading: true,
			logined: false,
			data_loading: true,
			products: [],
			totalData: {},
			manage_ident: 0,
    }
  },

  onLoad: function(options) {
    uni.showLoading();
    request.post('/common/getConfig').then(res => {
      this.config = res.data;
    })
  },

  onShow: function () {
    this.getLoginUser();
  },

  onPullDownRefresh: function() {
    this.getCartData();
  },

  methods: {
    getLoginUser: function() {
      request.post('/account/getLoginUser').then(res => {
        this.loading = false;
        if (res.data.id) {
          this.logined = true;
          this.getCartData();
        } else {
          uni.hideLoading();
          this.logined = false;
        }
      })
    },

		getCartData: function() {
			request.post('/order/getCartData').then(res => {
        uni.stopPullDownRefresh();
        uni.hideLoading();
				this.data_loading = false;
				this.products = res.data.products;
				this.totalData = res.data.totalData;
			})
		},

		setSelected_product: function(sku) {
      this.products.forEach((item, index) => {
      	if (this.products[index].sku == sku) {
      		let selected = this.products[index].selected;
      		this.products[index].selected = selected == 1 ? 0 : 1;
      	}
      })
			this.checkSelected();
			this.setSelected_store();
		},

		setSelected_all() {
			let all_selected = this.totalData.all_selected;
			if (all_selected == 1) {
				this.totalData.all_selected = 0;
        this.products.forEach((item_product, index_product) => {
        	this.products[index_product].selected = 0;
        })
			} else {
				this.totalData.all_selected = 1;
        this.products.forEach((item_product, index_product) => {
        	this.products[index_product].selected = 1;
        })
			}
			this.checkSelected();
			this.setSelected_store();
		},

		checkSelected: function() {
			let i1 = 1;
      this.products.forEach((item, index) => {
      	if (item.selected != 1) {
      		i1 = 0;
      	}
      })
			this.totalData.all_selected = i1;
		},

		setSelected_store: function() {
			let selected_skus = [];
      this.products.forEach((item, index) => {
      	if (item.selected == 1) {
      		selected_skus.push(item.sku);
      	}
      })
			uni.showLoading();
			request.post('/product/selectCart', { skus: selected_skus }).then(res => {
			  uni.hideLoading();
				this.getCartData();
			})
		},

		deleteCart: function() {
			let selected_skus = [];
			this.products.forEach((item_product, index_product) => {
				if (item_product.selected == 1) {
					selected_skus.push(item_product.sku);
				}
			})
			let that = this;
			uni.showModal({
				content: '确认删除选中的商品？',
				success: function (res) {
					if (res.confirm) {
						uni.showLoading();
						request.post('/product/deleteCart', { skus: selected_skus }).then(res => {
						  uni.hideLoading();
							that.getCartData();
						})
					}
				}
			});
		},

		setManage: function() {
			this.manage_ident = this.manage_ident == 1 ? 0 : 1;
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
@import url("cart.css");
page {
  padding-bottom: 30rpx;
}
</style>

<template>
	<view class="page">
		<CustomTop top_title="商品"></CustomTop>
    <view class="product_show" v-if="!loading">
      <view class="top_image">
        <template v-if="product.images.length > 0">
          <swiper class="swiper" circular :autoplay="true" :indicator-dots="true">
            <swiper-item v-for="(item, index) in product.images" :key="index">
              <view class="swiper-item">
                <image class="img" mode="aspectFill" :src="item.image" @click="previewImages(item.image)" />
              </view>
            </swiper-item>
          </swiper>
        </template>
        <template v-if="product.images.length == 0">
          <image class="img" :src="product.cover" mode="aspectFill"></image>
        </template>
      </view>
      <view class="proinfo">
        <view class="name">
          <view class="txt">{{product.name}}</view>
        </view>
        <!-- <view class="types">
          <view class="types_item">测试分类啊</view>
        </view> -->
        <view class="pricebox aduty-text-price">
          ¥{{product.sku.price}}
        </view>
      </view>
      <view class="specification pagebox" v-if="product.specification_type == '多规格'" @click="onPopupShow_buy">
        <view class="spebox">
          <view class="label">已选择</view>
          <view class="specification_items">
            <view class="specification_item" v-for="(item, index) in product.sku.specifications" :key="index">
              {{item.specification_name}}-{{item.specification_option}}
            </view>
          </view>
          <i class="iconfont icon-youbian"></i>
        </view>
      </view>
      <view class="content pagebox">
        <view class="attributes" v-if="product.attributes.length > 0">
          <view class="items">
            <view class="item" v-for="(item, index) in product.attributes" :key="index">
              <view class="vm100">{{item.attribute_name}}</view>
              <view class="vm101">{{item.attribute_value}}</view>
            </view>
          </view>
        </view>
        <view class="bd">
          <u-parse :content="product.content" :selectable="true" @preview="preview" />
        </view>
      </view>

      <view class="foot_action_blank"></view>
      <view class="foot_action" v-if="!loading">
      	<view class="container">
      		<view class="left">
      			<view class="item" @click="switchTab('/pages/index/index')">
      				<i class="iconfont icon-shouye"></i>
      				<view class="txt">首页</view>
      			</view>
      			<view class="item" @click="jumpPage('/pages/article/show?type=contact')">
      				<i class="iconfont icon-kefu11"></i>
      				<view class="txt">客服</view>
      			</view>
      			<view class="item" @click="switchTab('/pages/cart/index')">
      				<i class="iconfont icon-gouwuche1"></i>
      				<view class="txt">购物车</view>
      			</view>
      		</view>
      		<view class="right">
      			<view class="btns">
      				<view class="btn btn_cart" @click="onPopupShow_buy()">加入购物车</view>
      				<view class="btn btn_onekeybuy" @click="onPopupShow_buy()">立即购买</view>
      			</view>
      		</view>
      	</view>
      </view>

      <u-popup
        mode="bottom"
        round="3"
        :show="popupShow_buy"
        @close="onPopupClose_buy"
        customStyle="background-color: transparent;"
      >
        <view class="popup_buy">
          <view class="btop">
            <view></view>
            <view></view>
            <i class="iconfont icon-quxiao" @click="onPopupClose_buy"></i>
          </view>
          <view class="box">
            <view class="ppinfo">
              <image :src="product.sku.cover" mode="aspectFill" class="cover"></image>
              <view class="infobox">
                <view class="name">{{product.name}}</view>
                <view class="pricebox aduty-text-price">
                  ¥{{product.sku.price}}
                </view>
                <view class="stockbox">库存 {{product.sku.stock}}</view>
                <view class="numbox">
                  <view class="dec" @click="setCount()('dec')"><i class="iconfont icon-jian1"></i></view>
                  <input class="input" v-model="count" />
                  <view class="inc" @click="setCount('inc')"><i class="iconfont icon-jiahao1"></i></view>
                </view>
              </view>
            </view>
            <view class="sitems" v-if="product.specification_type == '多规格'">
              <view class="sitem" v-for="(item, index) in product.specifications" :key="index">
                <view class="stitle">{{item.specification_name}}</view>
                <view class="options">
                  <view
                    class="option"
                    :class="{
                      'on': item_option.selected == 1,
                      'invalid': item_option.valid != 1
                    }"
                    v-for="(item_option, index_option) in item.options"
                    :key="index_option"
                    @click="setSku(item_option)"
                  >
                    {{item_option.specification_option}}
                  </view>
                </view>
              </view>
            </view>
          </view>
          <view class="bfoot">
            <view class="btns">
              <view class="btn btn_cart" @click="addCart()">加入购物车</view>
              <view class="btn btn_onekeybuy" @click="onekeybuy()">立即购买</view>
            </view>
          </view>
        </view>
      </u-popup>
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
      logined: false,
      id: '',
      sku: '',
      product: {},
      count: 1,
      popupShow_buy: false,
		}
	},

	onLoad(options) {
    let id = '';
		if (options.id != undefined) {
			id = options.id;
		} else if (options.scene != undefined) {
			let scene = decodeURIComponent(options.scene);
			let obj = util.urlToObj(scene);
			if (obj.id != undefined) {
				id = obj.id;
			}
		}
		this.id = id;

    let sku = '';
    if (options.sku != undefined) {
    	sku = options.sku;
    } else if (options.scene != undefined) {
    	let scene = decodeURIComponent(options.scene);
    	let obj = util.urlToObj(scene);
    	if (obj.sku != undefined) {
    		sku = obj.sku;
    	}
    }
    this.sku = sku;

    uni.showLoading();
    this.getShow();
  },

  onShow() {
    this.getLoginUser();
  },

	methods: {
    getShow: function() {
      request.post('/product/getShow', { sku: this.sku }).then(res => {
        uni.hideLoading();
        if (!res.data || !res.data.id) {
          uni.showModal({
            showCancel: false,
            content: '内容不存在',
            success: function (res_modal) {
              if (res_modal.confirm) {
                uni.navigateBack();
              }
            }
          });
          return false;
        }

        this.loading = false;
        let content = res.data.content;
        content = content.replace(/<img/gi, '<img style="max-width:100%; height:auto; display: block;"');
        res.data.content = content;
        this.product = res.data;
        this.updateUrl({sku: res.data.sku.sku})
        // 图片
        let fileList_images = [];
        if (res.data.images && res.data.images.length > 0) {
          res.data.images.forEach((item, index) => {
            let file = {
              thumb: item.image,
              type: "image",
              url: item.image,
              id: item.id
            };
            fileList_images.push(file);
          })
        }
        this.fileList_images = fileList_images;
      })
    },

    onekeybuy: function() {
      if (!this.logined) {
        this.jumpPage('/pages/account/login_password');
        return false;
      }
      this.jumpPage('/pages/checkout/onekeybuy?sku=' + this.product.sku.sku + '&count=' + this.count);
    },

    addCart: function() {
      uni.showLoading();
      let params = {
        sku: this.product.sku.sku,
        count: this.count
      };
			request.post('/product/addCart', params).then(res => {
				uni.hideLoading();
				if (res.code == 200) {
					uni.showToast({ title: res.message, icon: 'none' });
				} else if (res.code == 400) {
					uni.showToast({ title: res.message, icon: 'none' });
				}
			})
    },

    getLoginUser: function() {
      request.post('/account/getLoginUser').then(res => {
        this.logined =  res.data.id ? true : false;
      })
    },

    setSku: function(e) {
      if (e.valid != 1 || e.selected == 1) {
        return false;
      }

      this.sku = e.sku;
      this.getShow();

      this.product.skus.forEach((item, index) => {
        if (item.sku == e.sku) {
          this.product.sku = item;
          this.updateUrl({sku: item.sku});
        }
      })

      this.product.specifications.forEach((item, index) => {
        if (e.specification_id == item.specification_id) {
          item.options.forEach((item_option, index_option) => {
            item_option.selected = 0;
          })
        }
      })
      e.selected = 1;
    },

    updateUrl: function(newParams) {
      try {
        const hash = window.location.hash || '#';
        const path = hash.split('?')[0].substring(1);
        const query = hash.split('?')[1] || '';

        const params = {};
        query.split('&').forEach(pair => {
          const [key, value] = pair.split('=');
          if (key) params[key] = value || '';
        });

        Object.keys(newParams).forEach(key => {
          if (newParams[key] === null || newParams[key] === undefined) {
            delete params[key];
          } else {
            params[key] = newParams[key];
          }
        });

        const newQuery = Object.keys(params)
          .map(key => `${key}=${params[key]}`)
          .join('&');
        const newHash = `#${path}${newQuery ? '?' + newQuery : ''}`;

        if (window.location.hash !== newHash) {
          history.replaceState(null, null, newHash);
        }
      } catch (e) {
        console.error('Update URL failed:', e);
      }
    },

    setCount: function(ident) {
      if (ident == 'inc') {
        this.count += 1;
      }
      if (ident == 'dec') {
        if (this.count <= 1) {
          this.count = 1;
          return false;
        }
        this.count -= 1;
      }
    },

    onPopupShow_buy: function() {
      this.popupShow_buy = true;
    },

    onPopupClose_buy: function() {
      this.popupShow_buy = false;
    },

    previewImage: function(current) {
      let urls = [];
      urls.push(current);
      uni.previewImage({
        current: current,
        urls: urls
      })
    },

    previewImages: function(current) {
      let urls = [];
      this.product.images.map((item, index) => {
        urls.push(item.image);
      })
      uni.previewImage({
        current: current,
        urls: urls
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
@import url("product.css");
page {
	padding-bottom: 30rpx;
}
</style>

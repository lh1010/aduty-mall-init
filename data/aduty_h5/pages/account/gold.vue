<template>
  <view>
    <CustomTop top_title="我的金币"></CustomTop>
    <view class="main" v-if="!loading">
    	<view class="main_container">
    		<view class="number">
    			<view class="icon"><i class="iconfont icon-jinbi"></i></view>
    			<view class="txt">{{user.gold}}</view>
    		</view>
    		<view class="description">小金币有大用途，多领一些存起来吧~</view>
				<view class="actionbox">
					<span class="btn" @click="jumpPage('/pages/account/gold_pay')">充值金币</span>
					<span class="btn" @click="jumpPage('/pages/account/exchange_cdkey')">卡密兑换</span>
				</view>
    	</view>
    </view>
    <view class="log" v-if="!data_list_loading">
    	<view class="title">金币记录</view>
    	<view v-if="data_list.length > 0">
    		<view class="items">
    			<view class="item" v-for="(item, index) in data_list" :key="index">
    				<view class="info">
    					<view class="txt">{{item.description}}</view>
    					<view class="ident">{{item.ident == 'inc' ? '+' : '-'}}{{item.gold}}</view>
    				</view>
    				<view class="date">{{item.created_at}}</view>
    			</view>
    		</view>
    		<view class="uloadmore" @click="getMore"><u-loadmore :status="loadmore_status" loadmore-text="下拉/点击加载更多" /></view>
      </view>
    	<u-empty
    	  v-if="data_list.length == 0"
    	  mode="data"
    	  icon="http://cdn.uviewui.com/uview/empty/data.png"
    	  text="暂无记录~"
    	>
    	</u-empty>
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
			loading: true,
      user: {},
      data_list: [],
			data_list_loading: true,
      loadmore_status: 'loadmore',
      loadmore_finished: false,
      params: {
        page_size: 15,
        page: 1,
      },
    }
  },

  onShow: function() {
    this.getUser();
		this.getInit();
  },

  onReachBottom() {
    this.getMore();
  },

  methods: {
    getUser: function() {
      uni.showLoading();
      request.post('/account/getLoginUser').then(res => {
        uni.hideLoading();
        this.user = res.data;
        this.loading = false;
      })
    },

    getList: function() {
      request.post('/account/getGoldLogsPaginate', this.params).then(res => {
				this.data_list_loading = false;
        if (res.data.total == 0) return false;

        if (res.data.current_page == 1) {
          this.data_list = res.data.data;
        } else {
          this.data_list = this.data_list.concat(res.data.data);
        }

        if (this.params.page == res.data.last_page) {
          this.loadmore_finished = true;
          this.loadmore_status = 'nomore';
          return false;
        }

        let params = this.params;
        params.page = parseInt(res.data.current_page) + parseInt(1);
        this.loadmore_status = 'loadmore';
        this.loadmore_finished = false;
        this.params = params;
      })
    },

		getInit: function() {
			uni.showLoading()
			this.data_list = [];
			this.data_list_loading = true;
			this.loadmore_status = 'loadmore';
			this.loadmore_finished = false;
			this.params.page = 1;
			this.getList();
		},

    getMore: function() {
      if (!this.loadmore_finished) {
        this.loadmore_status = 'loading';
        this.getList();
      }
    },

    jumpPage: function(url) {
      uni.navigateTo({ url: url });
    }
  }
}
</script>

<style>
page {
	padding-bottom: 30rpx;
}
.main {
  width: 690rpx;
  margin: 0 auto;
  margin-top: 30rpx;
  border-radius: 10rpx;
  background-color: #61e7ce;
}
.main_container {
  padding: 30rpx 30rpx 50rpx 30rpx;
  overflow: hidden;
  position: relative;
}
.main .number {
  overflow: hidden;
	display: flex;
	align-items: center;
}
.main .number .icon {
  margin-right: 10rpx;
  font-size: 16px;
}
.main .number .txt {
  font-size: 40px;
}
.main .actionbox {
	margin-top: 50rpx;
}
.main .actionbox .btn {
  margin-left: 6px;
	font-size: 14px;
	background-color: #333;
	color: #61e7ce;
	border-radius: 5rpx;
	padding: 8px 15px;
}
.main .actionbox .btn:first-child {
  margin-left: 0;
}
.main .description {
  font-size: 12px;
}

.iospay {
  width: 690rpx;
  margin: 0 auto;
  margin-top: 30rpx;
  border-radius: 10rpx;
  background-color: #1989fa;
  color: #ffffff;
  font-size: 24rpx;
}
.iospay .box {
  padding: 30rpx;
  overflow: hidden;
  position: relative;
}
.iospay .box .contact {
  margin-top: 20rpx;
}
.iospay .box .contact .btn {
  margin-left: 8px;
  background-color: #ffffff;
  color: #000000;
  padding: 2px 6px;
  border-radius: 3px;
  font-size: 24rpx;
}

.log {
  width: 690rpx;
  margin: 0 auto;
  margin-top: 50rpx;
}
.log .title {
  font-weight: bold;
  letter-spacing: 2px;
  margin-bottom: 30rpx;
  position: relative;
  padding-left: 30rpx;
  color: #555;
}
.log .title::before {
  position: absolute;
  top: 12rpx;
  left: 0;
  width: 10rpx;
  height: 24rpx;
  border-radius: 32rpx;
  background: #61e7ce;
  content: '';
}
.log .items {
  background-color: #fff;
  padding: 30rpx;
  border-radius: 10rpx;
  margin-bottom: 30rpx;
}
.log .items .item:not(:last-child) {
  border-bottom: 1px solid #f5f5f5;
  padding-bottom: 30rpx;
  margin-bottom: 30rpx;
}
.log .items .item .info {
  overflow: hidden;
}
.log .items .item .info .txt {
  float: left;
}
.log .items .item .info .ident {
  float: right;
  color: #61e7ce;
}
.log .items .item .date {
  font-size: 12px;
  color: #999;
  margin-top: 10rpx;
}
</style>

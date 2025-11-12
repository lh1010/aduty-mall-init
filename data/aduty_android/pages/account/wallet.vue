<template>
  <view>
    <CustomTop top_title="我的钱包"></CustomTop>
    <view class="container" v-if="!loading">
      <view class="wallet_top pagebox">
        <view class="txt">
          <view>钱包余额</view>
          <view class="price">{{user.wallet}}</view>
        </view>
        <view class="btns">
          <span class="aduty-btn aduty-btn-default" @click="jumpPage('/pages/account/wallet_withdraw')">提现</span>
					<span class="aduty-btn aduty-btn-payment" @click="jumpPage('/pages/account/wallet_pay')">充值</span>
        </view>
      </view>
      <view class="log_list pagebox">
      	<view class="stitle"><span class="txt">钱包明细</span></view>
        <view v-if="!data_list_loading">
          <view class="items" v-if="data_list.length > 0">
          	<view class="item" v-for="(item, index) in data_list" :key="index">
          		<view class="info">
          			<view class="txt">{{item.description}}</view>
          			<view class="ident">{{item.ident == 'inc' ? '+' : '-'}}{{item.price}}</view>
          		</view>
          		<view class="date">{{item.created_at}}</view>
          	</view>
            <u-loadmore :status="loadmore_status" />
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
      data_list: [],
      data_list_loading: true,
      loadmore_status: 'loadmore',
      loadmore_finished: false,
      params: {
        page_size: 15,
        page: 1,
      },
      user: {},
    }
  },

  onShow: function() {
    this.getUser();
    this.getList();
  },

  // 加载更多
  onReachBottom() {
    this.getMore();
  },

  // 下拉刷新
  onPullDownRefresh: function() {
    uni.showLoading();
    this.getInit();
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
      request.post('/account/getWalletLogsPaginate', this.params).then(res => {
        uni.stopPullDownRefresh();
        this.data_list_loading = false;
        if (res.data.total == 0) return false;

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
      this.data_list = [];
      this.loadmore_status = 'loadmore';
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
.pagebox {
	margin-top: 30rpx;
  background-color: #fff;
  padding: 30rpx;
}
.pagebox .stitle {
  font-weight: bold;
  margin-bottom: 30rpx;
  padding-bottom: 30rpx;
  border-bottom: 1px solid #f5f5f5;
}
.wallet_top {
	text-align: center;
	padding-top: 40rpx !important;
	padding-bottom: 50rpx !important;
}
.wallet_top .txt .price {
	font-size: 68rpx;
}
.wallet_top .txt .price i {
	font-size: 32rpx;
	margin-right: 6px;
	font-style: normal;
}
.wallet_top .btns {
	margin-top: 32rpx;
  display: flex;
  justify-content: center;
}
.wallet_top .btns .aduty-btn {
	width: 300rpx;
  margin: 0 5px;
}

.log_list .items .item {
	border-bottom: 1px solid #f5f5f5;
  padding-bottom: 30rpx;
  margin-bottom: 30rpx;
}
.log_list .items .info {
	overflow: hidden;
}
.log_list .items .info .txt {
	float: left;
	max-width: 80%;
}
.log_list .items .info .ident {
	float: right;
	color: #FF0000;
}
.log_list .items .item .date {
	font-size: 24rpx;
	color: #999;
	margin-top: 5px;
}
</style>

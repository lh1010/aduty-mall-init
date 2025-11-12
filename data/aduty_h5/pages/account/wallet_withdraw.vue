<template>
  <view>
    <CustomTop top_title="钱包提现"></CustomTop>
    <view class="container" v-if="!loading">
      <form class="aduty-form" @submit="formSubmit">
        <view class="aduty-form-box">
          <view class="aduty-form-cell">
            <view class="aduty-form-cell-box">
              <view class="aduty-form-cell-hd">
                <view class="aduty-form-label">钱包余额</view>
              </view>
              <view class="aduty-form-cell-bd">
                <input class="aduty-form-input" type="text" disabled="true" :value="'¥' + user.wallet" />
              </view>
            </view>
          </view>
          <view class="aduty-form-cell">
            <view class="aduty-form-cell-box">
              <view class="aduty-form-cell-hd">
                <view class="aduty-form-label">提现金额</view>
              </view>
              <view class="aduty-form-cell-bd">
                <input name="price" class="aduty-form-input" type="text" placeholder="¥" value="" />
              </view>
            </view>
          </view>
          <view class="aduty-form-cell">
            <view class="aduty-form-cell-box">
              <view class="aduty-form-cell-hd">
                <view class="aduty-form-label">支付宝账号</view>
              </view>
              <view class="aduty-form-cell-bd">
                <input name="alipay_account" class="aduty-form-input" type="text" placeholder="请输入支付宝账号" value="" />
              </view>
            </view>
          </view>
          <view class="aduty-form-cell">
            <view class="aduty-form-cell-box">
              <view class="aduty-form-cell-hd">
                <view class="aduty-form-label">账号名字</view>
              </view>
              <view class="aduty-form-cell-bd">
                <input name="alipay_name" class="aduty-form-input" type="text" placeholder="请输入支付宝账号名字" value="" />
              </view>
            </view>
          </view>
        </view>
        <view class="aduty-form-action">
          <button class="aduty-btn aduty-btn-default" formType="submit">提交信息</button>
        </view>
      </form>

			<view class="msgbox" v-if="config.withdrawal.min > 0 || config.withdrawal.max > 0 || config.withdrawal.today_count > 0 || config.withdrawal.rate > 0">
        <view class="item" v-if="config.withdrawal.rate > 0">提现手续费：{{config.withdrawal.rate * 100}}%</view>
        <view class="item" v-if="config.withdrawal.min > 0">单次最小提现金额：{{config.withdrawal.min}}元</view>
        <view class="item" v-if="config.withdrawal.max > 0">单次最大提现金额：{{config.withdrawal.max}}元</view>
        <view class="item" v-if="config.withdrawal.today_count > 0">每天最多可提现次数：{{config.withdrawal.today_count}}次</view>
      </view>

      <view class="log_list" v-if="!data_list_loading">
        <view class="stitle">提现记录</view>
        <view v-if="data_list.length > 0">
					<view class="items">
						<view class="item" v-for="(item, index) in data_list" :key="index">
							<view>申请金额：{{item.price}}</view>
							<view>手续费：{{item.commission_price}}</view>
							<view>
								审核状态：
								<span v-if="item.status == 0">审核中</span>
								<span v-if="item.status == 1">审核成功</span>
								<span v-if="item.status == 2">
								  审核失败
								  <i class="iconfont icon-wenhao icon_question" @click="showStatusMessage(item.message != '' ? item.message : '无留言');"></i>
								</span>
							</view>
							<view>申请时间：{{item.created_at}}</view>
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
			data_list_loading: true,
      data_list: [],
      loadmore_status: 'loadmore',
      loadmore_finished: false,
      params: {
        page_size: 15,
        page: 1,
      },
			config: {
        withdrawal: {}
      },
    }
  },

	onLoad: function() {
    request.post('/common/getConfig').then(res => {
      this.config = res.data;
    })
		this.getUser();
		this.getInit();
  },

  onReachBottom() {
    this.getMore();
  },

  methods: {
    formSubmit: function(e) {
      let that = this;
      uni.showModal({
      	content: '确认提交？',
      	success: function (res) {
      		if (res.confirm) {
      			uni.showLoading();
      			let params = e.detail.value;
      			request.post('/account/walletWithdraw', params).then(res => {
      			  uni.hideLoading();
      			  if (res.code == 200) {
      					that.getUser();
      					that.getInit();
      			    uni.showToast({ icon: 'none', title: '申请成功，等待系统审核~' });
      			  } else if (res.code == 400) {
      			    uni.showToast({ title: res.message, icon: 'none' });
      			  }
      			})
      		}
      	}
      });
    },

    getUser: function() {
      uni.showLoading();
      request.post('/account/getLoginUser').then(res => {
        uni.hideLoading();
        this.user = res.data;
        this.loading = false;
      })
    },

    getList: function() {
      request.post('/account/getWalletWithdrawalLogsPaginate', this.params).then(res => {
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

    showStatusMessage: function(message) {
      uni.showModal({
        showCancel: false,
        confirmText: '我知道了',
        content: message
      })
    }
  }
}
</script>

<style>
page {
	padding-bottom: 30rpx;
}
.aduty-form-box {
	margin-top: 30rpx;
}
.log_list {
  margin-top: 30rpx;
	background-color: #fff;
	padding: 30rpx 30rpx 50rpx 30rpx;
	font-size: 14px;
	border-radius: 3px;
}
.log_list .stitle {
	margin-bottom: 30rpx;
	font-weight: bold;
	padding-bottom: 30rpx;
	border-bottom: 1px solid #f5f5f5;
}
.log_list .item {
	border-bottom: 1px solid #f5f5f5;
	margin-bottom: 30rpx;
	padding-bottom: 30rpx;
}
.icon_question {
  margin-left: 3px;
}
.msgbox {
  color: #ff0000;
  margin-top: 30rpx;
  padding: 30rpx;
  border-radius: 3px;
  font-size: 12px;
	background-color: #fff;
}
</style>

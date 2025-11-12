<template>
	<view>
		<CustomTop top_title="买入订单"></CustomTop>
		<view class="sv_nav">
		  <scroll-view class="scroll-view" scroll-x="true" :scroll-left="sv_left" :scroll-with-animation="true">
		    <view class="box" :style="{'width': sv_totalWidth + 'px'}">
          <view class="item svItem" :class="params.status == '' ? 'on' : ''" @click="svChange(1, '')">全部订单</view>
		      <view
            class="item svItem"
            v-for="(item, key, index) in config.mall.order_status"
            :key="index"
            :class="params.status == key ? 'on' : ''"
            @click="svChange(index + 1, key)"
          >
            {{item}}
          </view>
		    </view>
		  </scroll-view>
		</view>
		<view class="sv_nav_blank"></view>

		<view class="order_list" v-if="!data_list_loading">
			<view class="">
        <view v-if="data_list.length > 0">
          <view class="items">
          	<view class="item" v-for="(item, index) in data_list" :key="index">
          		<view class="top" @click="jumpPage('/pages/order/show?id=' + item.id)">
          			<view class="number">订单编号：{{item.number}}</view>
								<view class="bright">
									<span class="status">{{item.status_str}}</span>
								</view>
          		</view>
							<view class="snaps">
								<view class="snaps_item" v-for="(item_snap, index_snap) in item.snaps" :key="index_snap" @click="jumpPage('/pages/order/show?id=' + item.id)">
									<image class="cover" :src="item_snap.cover" mode="aspectFit" />
									<view class="info">
										<view class="name">{{item_snap.name}}</view>
                    <view class="spes" v-if="item_snap.specifications.length > 0">
                      <view class="spes_item" v-for="(item_specification, index_specification) in item_snap.specifications" :key="index_specification">
                        {{item_specification.specification_name}}-{{item_specification.specification_option}}
                      </view>
                    </view>
										<view class="pricebox">
											<span class="price">¥{{item_snap.price}}</span>
											<span class="number">x{{item_snap.count}}</span>
										</view>
									</view>
								</view>
							</view>
          		<view class="types">
								<view class="types_item">
									<span class="span1">合计金额：</span><span class="span2 aduty-text-price">¥{{item.total_price}}</span>
								</view>
          		</view>
          		<view class="item_foot" v-if="[0, 20].includes(item.status)">
          			<view class="btns">
									<span class="btn" @click="receiveOrder(item.id)" v-if="item.status == '20'">确认收货</span>
									<span class="btn" @click="cancelOrder(item.id)" v-if="item.status == 0">取消订单</span>
          				<span class="btn btn_pay" @click="jumpPage('/pages/checkout/pay?order_ids=' + item.id)" v-if="item.status == 0">去支付</span>
          			</view>
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

		<u-popup
      mode="center"
      round="3"
      :show="contact_popup_show"
      @close="onClose_contact_popup"
      customStyle="background-color: transparent;"
    >
      <view class="contact_popup">
        <view class="box">
          <view class="stitle">店铺联系方式</view>
					<view class="items">
						<view class="item">
							<span class="span1">微信：</span>
							<span class="span2">{{ contact.weixin ? contact.weixin : '未填写' }}</span>
							<span class="span3" @click="copy(contact.weixin)" v-if="contact.weixin">复制</span>
						</view>
						<view class="item">
							<span class="span1">手机：</span>
							<span class="span2">{{ contact.phone ? contact.phone : '未填写' }}</span>
							<span class="span3" @click="copy(contact.phone)" v-if="contact.phone">复制</span>
						</view>
						<view class="item">
							<span class="span1">Q Q：</span>
							<span class="span2">{{ contact.qq ? contact.qq : '未填写' }}</span>
							<span class="span3" @click="copy(contact.qq)" v-if="contact.qq">复制</span>
						</view>
						<view class="item">
							<span class="span1">电话：</span>
							<span class="span2">{{ contact.telphone ? contact.telphone : '未填写' }}</span>
							<span class="span3" @click="copy(contact.telphone)" v-if="contact.telphone">复制</span>
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
			data_list: [],
			data_list_loading: true,
			loadmore_status: 'loadmore',
			loadmore_finished: false,
			params: {
				page_size: 15,
				page: 1,
        status: '',
			},
      config: {
        mall: {},
      },
      sv_totalWidth: 1000,
      sv_navLength: 0,
      sv_itemWidth: 0,
      sv_left: 0,
      sv_current: 0,
			contact_popup_show: false,
			contact: {},
		}
	},

	onLoad(options) {
    if (options.status != undefined) {
    	this.params.status = options.status;
    }

    request.post('/common/getConfig').then(res => {
      this.config = res.data;
      let sv_navLength = 1;
      for (var i in res.data.mall.order_status) {
        sv_navLength += 1;
      }
      this.sv_navLength = sv_navLength;
    }).then(() => {
      this.initSv();
    }).then(() => {
      if (options.status != undefined) {
        let i = 0;
        for (var key in this.config.mall.order_status) {
          if (key == options.status) {
            this.setSv(i);
          }
          i++;
        }
      }
    })

    uni.showLoading();
		this.getInit();
	},

	onReachBottom() {
	  this.getMore();
	},

	onPullDownRefresh: function() {
	  this.getInit();
	},

	methods: {
		// 请求接口，获取列表数据
		getList: function() {
			let params = this.params;
			request.post('/order/getList', params).then(res => {
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
			uni.showLoading()
			this.data_list = [];
			this.data_list_loading = true;
			this.loadmore_status = 'loadmore';
			this.loadmore_finished = false;
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

    // 菜单滑动
    initSv: function() {
      this.$nextTick(() => {
      	let dom = uni.createSelectorQuery().select(".svItem");
      	dom.boundingClientRect((data) => {
      		let num = this.sv_navLength;
      		this.sv_itemWidth = data.width;
      		this.sv_totalWidth = num * this.sv_itemWidth;
      	}).exec()
      })
      this.$forceUpdate();
    },

    setSv: function(index) {
      this.sv_current = index;
      this.sv_left = this.sv_itemWidth * (index - 1);
    },

    // 点击tab切换高亮，并进行滑动，（index-1）是为了点击项显示在第二栏的位置
    svChange(index, value){
      this.sv_current = index;
      this.sv_left = this.sv_itemWidth * (index - 1);
      this.params.status = value;
      this.getInit();
      this.$forceUpdate();
    },

		// 取消订单
		cancelOrder: function(id) {
			let that = this;
			uni.showModal({
				content: '确定取消？',
				success (res) {
					if (res.confirm) {
						uni.showLoading();
						request.post('/order/cancelOrder', {order_id: id}).then((res) => {
							uni.hideLoading();
							if (res.code == 200) {
								uni.showToast({ icon: 'none', title: '操作成功' });
								let data_list = that.data_list;
								for (var i = 0; i < data_list.length; i++) {
									if (data_list[i].id == id) {
										data_list[i].status = -10;
										data_list[i].status_str = '已取消';
									}
								}
								that.data_list = data_list;
								uni.showToast({ icon: 'none', title: '取消成功' });
							} else if (res.code == 400) {
								uni.showToast({ title: res.message, icon: 'none' });
							}
						});
					}
				}
			})
		},

		// 收货
		receiveOrder: function(id) {
			let that = this;
			uni.showModal({
				content: '确定操作？',
				success (res) {
					if (res.confirm) {
						uni.showLoading();
						request.post('/order/receiveOrder', {order_id: id}).then((res) => {
							uni.hideLoading();
							if (res.code == 200) {
								uni.showToast({ icon: 'none', title: '操作成功' });
								let data_list = that.data_list;
								for (var i = 0; i < data_list.length; i++) {
									if (data_list[i].id == id) {
										data_list[i].status = 30;
										data_list[i].status_str = '已完成';
									}
								}
								that.data_list = data_list;
							} else if (res.code == 400) {
								uni.showToast({ title: res.message, icon: 'none' });
							}
						});
					}
				}
			})
		},

		getContact: function(id) {
			uni.showLoading();
			request.post('/shop/getContact', {shop_id: id}).then((res) => {
				uni.hideLoading();
				this.contact = res.data;
				this.contact_popup_show = true;
			})
		},

		onClose_contact_popup: function() {
      this.contact_popup_show = false;
    },

		copy: function(content) {
		  uni.setClipboardData({ data: content });
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
@import url("order.css");
page {
	padding-bottom: 30rpx;
}
</style>

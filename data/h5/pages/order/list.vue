<template>
	<view>
		<CustomTop topTitle="买入订单"></CustomTop>
		<view class="sv_nav">
		  <scroll-view class="scroll-view" scroll-x="true" :scroll-left="svLeft" :scroll-with-animation="true">
		    <view class="box" :style="{'width': svTotalWidth + 'px'}">
          <view class="item svItem" :class="params.status == '' ? 'on' : ''" @click="svChange(1, '')">全部订单</view>
		      <view
            class="item svItem"
            v-for="(item, key, index) in config.mall.order_status"
            :key="index"
            :class="params.status == key ? 'on' : ''"
            @click="svChange(index + 1, key)"
          >
            {{ item }}
          </view>
		    </view>
		  </scroll-view>
		</view>
		<view class="sv_nav_blank"></view>

		<view class="order_list" v-if="!dataListLoading">
			<view class="">
        <view v-if="dataList.length > 0">
          <view class="items">
          	<view class="item" v-for="(item, index) in dataList" :key="index">
          		<view class="top" @click="uni.navigateTo({url:'/pages/order/show?id=' + item.id})">
          			<view class="number">订单编号：{{ item.number }}</view>
								<view class="bright">
									<span class="status">{{ item.status_show }}</span>
								</view>
          		</view>
							<view class="snaps">
								<view class="snaps_item" v-for="(item_snap, index_snap) in item.snaps" :key="index_snap" @click="uni.navigateTo({url:'/pages/order/show?id=' + item.id})">
									<image class="cover" :src="item_snap.cover" mode="aspectFit" />
									<view class="info">
										<view class="name">{{ item_snap.name }}</view>
                    <view class="spes" v-if="item_snap.specifications.length > 0">
                      <view class="spes_item" v-for="(item_specification, index_specification) in item_snap.specifications" :key="index_specification">
                        {{ item_specification.specification_name }}-{{ item_specification.specification_option }}
                      </view>
                    </view>
										<view class="pricebox">
											<span class="price">¥{{ item_snap.price }}</span>
											<span class="number">x{{ item_snap.count }}</span>
										</view>
									</view>
								</view>
							</view>
          		<view class="types">
								<view class="types_item">
									<span class="span1">合计金额：</span><span class="span2 aduty-text-price">¥{{ item.total_price }}</span>
								</view>
          		</view>
          		<view class="item_foot" v-if="[0, 20].includes(item.status)">
          			<view class="btns">
									<span class="btn" @click="receiveOrder(item.id)" v-if="item.status == 20">确认收货</span>
									<span class="btn btn_cancel" @click="cancelOrder(item.id)" v-if="item.status == 0">取消订单</span>
          				<span class="btn btn_pay" @click="uni.navigateTo({url:'/pages/checkout/pay?order_ids=' + item.id})" v-if="item.status == 0">去支付</span>
          			</view>
          		</view>
          	</view>
          </view>
          <view class="uloadmore"><uni-load-more :status="loadmoreStatus" /></view>
        </view>
				<view class="aduty-empty" v-if="dataList.length == 0">
					<image class="aduty-empty-img" src="/static/images/empty.png" mode="aspectFit"></image>
					<text class="aduty-empty-text">暂无内容</text>
				</view>
			</view>
		</view>

		<uni-popup ref="contactPopupRef" type="center">
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
    </uni-popup>
	</view>
</template>

<script setup>
import { ref, nextTick } from 'vue';
import { onLoad, onReachBottom, onPullDownRefresh } from '@dcloudio/uni-app';
import http from "@/utils/http.js";
import CustomTop from "@/components/CustomTop.vue";
import util from "@/utils/util.js";

const dataList = ref([]);
const dataListLoading = ref(true);
const loadmoreStatus = ref('loadmore');
const loadmoreFinished = ref(false);
const params = ref({ page: 1, page_size: 15, status: '' });
const config = ref({ mall: {} });
const svTotalWidth = ref(1000);
const svItemWidth = ref(0);
const svLeft = ref(0);
const contactPopupRef = ref(null);
const contact = ref({});

onLoad((options) => {
  uni.setNavigationBarTitle({ title: '买入订单' });
  if (options.status != undefined) {
    params.value.status = options.status;
  }
	getConfig();
  uni.showLoading();
  getInit();
});

onReachBottom(() => {
  getMore();
});

onPullDownRefresh(() => {
  getInit();
});

const svChange = (index, value) => {
  svLeft.value = svItemWidth.value * (index - 1);
  params.value.status = value;
	util.updateUrl(params.value, ['page', 'page_size']);
  getInit();
};

const getConfig = () => {
	http.post('/common/getConfig').then(res => {
    config.value = res.data;
    let svNavLen = Object.keys(res.data.mall.order_status).length + 1;
    nextTick(() => {
      uni.createSelectorQuery().select(".svItem").boundingClientRect((data) => {
        if (!data) return;
        svItemWidth.value = data.width;
        svTotalWidth.value = svNavLen * data.width;
        // 如果有初始 status，滚动到对应位置
        if (params.value.status) {
          let i = 0;
          for (var key in config.value.mall.order_status) {
            if (key == params.value.status) {
              svLeft.value = data.width * i;
            }
            i++;
          }
        }
      }).exec();
    });
  });
}

const getList = () => {
  http.post('/order/getList', params.value).then(res => {
    uni.stopPullDownRefresh();
    uni.hideLoading();
    dataListLoading.value = false;
    if (res.data.total == 0) {
      dataList.value = [];
      return;
    }
    if (params.value.page == 1) {
      dataList.value = res.data.data;
    } else {
      dataList.value = dataList.value.concat(res.data.data);
    }
    if (params.value.page >= res.data.last_page) {
      loadmoreFinished.value = true;
      loadmoreStatus.value = 'nomore';
      return;
    }
    params.value.page = parseInt(res.data.current_page) + 1;
    loadmoreStatus.value = 'loadmore';
    loadmoreFinished.value = false;
  });
};

const getInit = () => {
  uni.showLoading();
  dataList.value = [];
  dataListLoading.value = true;
  loadmoreStatus.value = 'loadmore';
  loadmoreFinished.value = false;
  params.value.page = 1;
  getList();
};

const getMore = () => {
  if (!loadmoreFinished.value) {
    loadmoreStatus.value = 'loading';
    getList();
  }
};

const cancelOrder = (id) => {
  uni.showModal({
    content: '确定取消？',
    success(res) {
      if (res.confirm) {
        uni.showLoading();
        http.post('/order/cancelOrder', { order_id: id }).then(res => {
          uni.hideLoading();
          if (res.code == 200) {
            uni.showToast({ icon: 'none', title: '操作成功' });
            let order = dataList.value.find(item => item.id == id);
						order.status = -10;
						order.status_show = '已取消';
          } else if (res.code == 400) {
            uni.showToast({ title: res.message, icon: 'none' });
          }
        });
      }
    }
  });
};

const receiveOrder = (id) => {
  uni.showModal({
    content: '确定操作？',
    success(res) {
      if (res.confirm) {
        uni.showLoading();
        http.post('/order/receiveOrder', { order_id: id }).then(res => {
          uni.hideLoading();
          if (res.code == 200) {
            uni.showToast({ icon: 'none', title: '操作成功' });
						let order = dataList.value.find(item => item.id == id);
						order.status = 30;
						order.status_show = '已完成';
          } else if (res.code == 400) {
            uni.showToast({ title: res.message, icon: 'none' });
          }
        });
      }
    }
  });
};

const getContact = (id) => {
  uni.showLoading();
  http.post('/shop/getContact', { shop_id: id }).then(res => {
    uni.hideLoading();
    contact.value = res.data;
    contactPopupRef.value.open();
  });
};

const copy = (content) => {
  uni.setClipboardData({ data: content });
};
</script>

<style>
@import url("order.css");
page {
	padding-bottom: 30rpx;
}
</style>

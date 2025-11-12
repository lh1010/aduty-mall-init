<template>
	<u-popup
    :show="city_show"
    @close="setCityHide"
    mode="bottom"
  >
    <view class="city">
      <view class="city_top">
        <view class="box">
          <view class="title">选择地区</view>
          <i class="iconfont icon-guanbicopy close" @click="setCityHide"></i>
        </view>
      </view>
      <scroll-view scroll-y="true" :scroll-into-view="toView" @scroll="onScroll" class="city_list">
        <view v-if="use_all">
          <view class="first">全部</view>
          <view class="bd" @click="setCityReceive(0, '全部');">
            <view class="a">全部</view>
          </view>
        </view>

        <view v-if="use_country">
          <view class="first">全国</view>
          <view class="bd" @click="setCityReceive(0, '全国');">
            <view class="a">全国</view>
          </view>
        </view>

        <view v-for="(item, index) in citys" :key="index">
          <view class="first" :id="index">{{index}}</view>
          <view class="bd">
            <view class="a" v-for="(item_city, index_city) in item" :key="index_city" @click="setCityReceive(item_city.id, item_city.shortname);">
              {{item_city.shortname}}
            </view>
          </view>
        </view>
      </scroll-view>

      <view class="letter_list">
        <view class="items">
          <view class="item" @click="scrollTo(index)" v-for="(item, index) in citys" :key="index">{{index}}</view>
        </view>
      </view>
    </view>
	</u-popup>
</template>

<script>
import { request } from "@/utils/http.js"

export default {
	name: "City",

	data() {
		return {
			citys: [],
      toView: '',
		};
	},

  props: {
    city_show: {
      type: Boolean

    },
    use_all: {
      type: Boolean
    },
    use_country: {
      type: Boolean
    }
  },

	mounted: function () {
	  this.getCitys();
	},

	methods: {
		getCitys: function() {
			request.post('/common/getCitys').then(res => {
				this.loading = true;
				this.citys = res.data;
			})
		},

    setCityShow() {
      this.city_show1 = true;
      this.$emit('setCityShow');
    },

		setCityHide: function() {
			this.city_show1 = false;
			this.$emit('setCityHide');
		},

		setCityReceive: function(city_id, city_name) {
			this.setCityHide();
			this.$emit('setCityReceive', city_id, city_name);
		},

    scrollTo(id) {
      setTimeout(() => {
        this.toView = id;
      }, 100);
    },

    onScroll: function(e) {
      if (this.toView != '') {
        this.toView = '';
      }
    }
	}
}
</script>

<style>
.city {
  position: fixed;
  left: 0;
	bottom: 0;
  width: 100%;
	height: 990rpx;
  overflow: hidden;
  z-index: 999;
}
.city_top {
	width: 100%;
	text-align: center;
	height: 90rpx;
	line-height: 90rpx;
	border-bottom: 1px solid #f5f5f5;
  background-color: #fff;
  border-top-left-radius: 8px;
  border-top-right-radius: 8px;
  overflow: hidden;
}
.city_top .box {
	position: relative;
}
.city_top .box .title {
	letter-spacing: 2px;
}
.city_top .box .close {
	position: absolute;
  top: 0;
	right: 40rpx;
	height: 90rpx;
	line-height: 90rpx !important;
	color: #999;
}
.city_list {
	height: 900rpx;
  width: 100%;
}
.city_list .first {
  padding: 20rpx 40rpx;
  font-weight: 600;
  background-color: #f1f3f8;
  letter-spacing: 2px;
}
.city_list .bd {
  overflow: hidden;
  background-color: #fff;
  padding: 0 40rpx;
  letter-spacing: 2px;
}
.city_list .bd .a {
  display: block;
  height: 80rpx;
  line-height: 80rpx;
  border-bottom: 1px solid #f9f9f9;
}
.city_list .bd .a:last-child {
  border-bottom: none;
}

.letter_list {
  position: absolute;
  right: 20rpx;
  top: 120rpx;
  width: 30rpx;
  color: #333;
  text-align: center;
  font-size: 10px;
}
</style>

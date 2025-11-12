<template>
	<view class="page">
		<CustomTopIndex top_title="商品分类"></CustomTopIndex>
		<view class="category" v-if="!loading">
      <scroll-view class="sidebar scroll-view" scroll-y="true">
        <view class="items">
          <view
            class="item"
            :class="category_id == item.id ? 'on' : ''"
            v-for="(item, index) in categorys"
            :key="index"
            @click="setCategoryId(item.id)"
          >
            <span class="name">{{item.name}}</span>
          </view>
        </view>
      </scroll-view>
      <scroll-view class="main scroll-view" scroll-y="true">
        <view class="box" v-for="(item, index) in categorys" :key="index" v-if="category_id == item.id">
          <view class="aduty-btn aduty-btn-default" @click="jumpPage('/pages/product/list?category_id=' + item.id)">进入{{category.name}}</view>
          <view class="items">
            <view class="item" v-for="(item100, index100) in item.items" :key="index100">
              <view class="stitle">{{item100.name}}</view>
              <view class="options">
                <span
                  class="option"
                  v-for="(item101, index101) in item100.items"
                  :key="index101"
									@click="jumpPage('/pages/product/list?category_id=' + item101.id)"
                >
                  {{item101.name}}
                </span>
              </view>
            </view>
          </view>
        </view>
      </scroll-view>

		</view>
	</view>
</template>

<script>
import { request } from "@/utils/http.js"
import CustomTopIndex from "@/components/CustomTopIndex.vue"

export default {
  components: { CustomTopIndex },

  data() {
    return {
      loading: true,
      categorys: [],
      category_id: '',
			category: {}
    }
  },

	onLoad() {
		uni.showLoading();
    request.post('/product/getCategorys', { get_attributes: 1 }).then(res => {
      this.loading = false;
			uni.hideLoading();
      if (res.data.length > 0) {
        this.category_id = res.data[0].id;
				this.category = res.data[0];
        this.categorys = res.data;
      }
    })
	},

	methods: {
    setCategoryId: function(id) {
      this.category_id = id;
			this.categorys.forEach((item, index) => {
				if (item.id == id) {
					this.category = item;
				}
			})
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
	background-color: #fff;
}
</style>

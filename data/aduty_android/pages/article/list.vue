<template>
  <view>
    <CustomTop :top_title="top_title"></CustomTop>
    <view class="container">
      <view class="article_list" v-if="!loading">
        <view class="items" v-if="data_list.length > 0">
          <view
            class="item"
            v-for="(item, index) in data_list"
            :key="index"
            @click="jumpPage('/pages/article/show?id=' + item.id);"
          >
            <image :src="item.cover" class="cover" mode="aspectFill" v-if="item.cover != ''" />
            <view class="info">
              <view class="title">{{item.title}}</view>
              <view class="description">{{item.description ? item.description : '暂无简介'}}</view>
            </view>
          </view>
          <u-loadmore :status="loadmore_status" />
        </view>
        <u-empty
          v-if="data_list.length == 0"
          mode="data"
          icon="http://cdn.uviewui.com/uview/empty/data.png"
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
			top_title: '文档中心',
      loading: true,
      data_list: [],
      loadmore_status: 'loadmore',
      loadmore_finished: false,
      params: {
        page_size: 15,
        page: 1,
        category_id: ''
      },
    }
  },

  onLoad(options) {
		if (options.category_id != undefined && options.category_id != '') {
		  this.params.category_id = options.category_id;

			request.post('/article/getCategory', {id: options.category_id}).then(res => {
				if (res.data != null) {
					this.top_title = res.data.name;
				}
			})
		}
    uni.showLoading();
    this.getArticlesPaginate();
  },

  onReachBottom() {
    this.getMore();
  },

  methods: {
    getArticlesPaginate: function() {
      let params = this.params;
      request.post('/article/getArticlesPaginate', params).then(res => {
        uni.stopPullDownRefresh();
        uni.hideLoading();
        this.loading = false;
        if (res.data.total == 0) {
          this.data_list = [];
          return false;
        }

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
        this.params.page = parseInt(res.data.current_page) + parseInt(1);
        this.loadmore_status = 'loadmore';
        this.loadmore_finished = false;
      })
    },

    getMore: function() {
      if (!this.loadmore_finished) {
        this.loadmore_status = 'loading';
        this.getArticlesPaginate();
      }
    },

    getInit: function() {
      uni.showLoading()
      this.params.page = 1;
      this.getArticlesPaginate();
    },

    onSet_categoryId: function(category_id) {
      this.params.category_id = category_id;
      this.getInit();
    },

    switchTab: function(url) {
      uni.switchTab({ url: url })
    },

    jumpPage: function(url) {
      uni.navigateTo({ url: url })
    }
  }
}
</script>

<style>
@import url("article.css");
page {
  padding-bottom: 30rpx;
  background-color: #fff;
}
</style>

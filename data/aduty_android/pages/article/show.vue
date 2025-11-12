<template>
	<view>
		<CustomTop top_title="详情"></CustomTop>
    <view class="container">
      <view class="article_show" v-if="!loading">
        <view class="top">
          <view class="title">{{article.title}}</view>
        </view>
        <view class="content">
          <u-parse :content="article.content" :selectable="true" @preview="preview" />
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
      id: '',
			type: '',
      article: {},
    }
  },

  onLoad(options) {
    uni.showLoading();
    if (options.id != undefined) {
			this.id = options.id;
		}
    if (options.type != undefined) {
    	this.type = options.type;
    }
    this.getArticle();
  },

  methods: {
    getArticle: function() {
      let params = {
				id: this.id,
				type: this.type
			};
      request.post('/article/getArticle', params).then(res => {
        uni.hideLoading();
        this.loading = false;
        // 处理富文本
        let content = res.data.content;
        content = content.replace(/<img/gi, '<img style="max-width:95%; height:auto; border: 1px solid #f5f5f5; padding: 5px; border-radius: 3px;"');
        content = content.replace(/<video/gi, '<video style="width:100%; height:auto; text-align: center;"');
        res.data.content = content;
        this.article = res.data;
      })
    },
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

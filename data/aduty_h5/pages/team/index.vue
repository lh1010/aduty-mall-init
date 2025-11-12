<template>
  <view class="page">
    <CustomTop top_title="团队"></CustomTop>
    <view class="abox1">
      <view class="vm1">邀请有礼</view>
      <view class="vm2">邀请用户 获取收益</view>
      <view class="vm2">多邀多得 上不封顶</view>
			<view class="vm3">下级用户消费100元，您将收益{{ 100 * config.team.rate }}元</view>
    </view>

		<view v-if="!loading && logined">
			<view class="abox2">
			  <view class="bd">
			    <view class="msg">生成海报图邀请</view>
			    <view class="msg">丰厚的邀请奖励等您来领，赶快行动吧</view>
			    <view class="btn" @click="createPosterImage">立即邀请</view>
			  </view>
			</view>
			<view class="abox3">
			  <view class="bd">
			    <view class="stitle">邀请记录</view>
			    <view class="infobox">
			      <view class="">被邀请人在平台消费您将获得对应奖励</view>
			      <view>已成功邀请 {{data_list.length}} 人，已获得 {{allInviteprice}} 元收益</view>
			    </view>
			    <view class="luck-table">
			      <view class="luck-table-tr">
			        <view class="luck-table-th">被邀请人</view>
			        <view class="luck-table-th">时间</view>
			      </view>
			      <view class="luck-table-tr" v-for="(item, index) in data_list" :key="index" v-if="!data_list_loading && data_list.length > 0">
			        <view class="luck-table-td">{{item.nickname}}</view>
			        <view class="luck-table-td">{{item.created_at}}</view>
			      </view>
			      <view class="luck-table-tr" :style="{ marginTop: '40rpx' }" v-if="!data_list_loading && data_list.length == 0">
			        <view class="luck-table-td text-center">暂无邀请用户~</view>
			      </view>
			    </view>
			  </view>
			</view>
		</view>

		<view v-if="!loading && !logined">
			<view class="loginbox">
				<view class="btn" @click="jumpPage('/pages/account/login_password')">立即登录</view>
			</view>
		</view>

		<view class="page_blank" :style="{ height: '140rpx' }"></view>
    <u-popup
			round="3"
      mode="center"
      :show="popupShow_poster"
      @close="onPopupClose_poster"
			custom-style="background-color: transparent;"
    >
      <view class="invite_img" :style="{ backgroundColor: '#ffffff' }">
        <image class="img" :src="poster_img" mode="widthFix" />
        <view class="des">长按保存图片</view>
        <!-- <view class="btn" @click="saveImage">保存图片</view> -->
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
      loading: true,
			logined: false,
			user: {},
      data_list_loading: true,
      data_list: [],
      poster_img: '',
      poster_img_origin: '',
      popupShow_poster: false,
      config: {
				team: {},
			},
      allInviteprice: '',
    }
  },

  onLoad: function() {
    uni.showLoading()
    request.post('/common/getConfig').then(res => {
      uni.hideLoading();
      this.config = res.data;
    })
  },

	onShow: function() {
		request.post('/account/getLoginUser').then(res => {
			uni.hideLoading();
			this.loading = false;
			if (res.data.id) {
				this.logined = true;
				this.user = res.data;
				this.getAllInviteWallet();
				this.getTeamUsers();
			} else {
				this.logined = false;
			}
		})
	},

  methods: {
    getAllInviteWallet: function() {
      request.post('/account/getAllInviteWallet').then(res => {
        this.allInviteprice = res.data;
      })
    },

    getTeamUsers: function() {
      request.post('/account/getTeamUsers').then(res => {
        this.data_list_loading = false;
        this.data_list = res.data;
      })
    },

    createPosterImage: function() {
      uni.showLoading({ title: '生成中...' });
      request.post('/account/createPosterImage', {}, {responseType: 'arraybuffer'}).then(res => {
        uni.hideLoading()
        this.poster_img_origin = res;
        this.poster_img = 'data:image/png;base64,' + uni.arrayBufferToBase64(res);
        this.popupShow_poster = true;
      })
    },

    onPopupClose_poster() {
      this.popupShow_poster = false;
    },

    // app内保存图片
    saveImage: function() {
      uni.showLoading();
      let that = this;
      let base64 = that.poster_img;
      const bitmap = new plus.nativeObj.Bitmap("base64");
      bitmap.loadBase64Data(base64, function() {
        uni.hideLoading();
        const url = "_doc/" + new Date().getTime() + ".png";
        bitmap.save(url, {
          overwrite: true,  // 是否覆盖
          // quality: 'quality'  // 图片清晰度
        }, (i) => {
          uni.saveImageToPhotosAlbum({
            filePath: url,
            success: function() {
              uni.showToast({ title: '图片保存成功', icon: 'none' });
              bitmap.clear();
            }
          });
        }, (e) => {
          uni.showToast({ title: '图片保存失败1', icon: 'none' });
          bitmap.clear();
        });
      }, (e) => {
        console.log(e);
        uni.showToast({ title: '图片保存失败2', icon: 'none' });
        bitmap.clear();
      });
    },

    // 小程序内保存图片
    saveImage: function() {
      let that = this;
      var imgSrc =  uni.arrayBufferToBase64(that.poster_img_origin);
      var save = uni.getFileSystemManager();
      var number = Math.random();
      var filePath = wx.env.USER_DATA_PATH + '/pic' + number + '.png';
      save.writeFile({
        filePath: filePath,
        data: imgSrc,
        encoding: 'base64',
        success: res => {
          uni.saveImageToPhotosAlbum({
            filePath: filePath,
            success(res) {
              uni.showToast({
               title: '保存成功',
               mask: true
              });
              that.popupShow_poster = false;
            },
            fail(res) {
              uni.showToast({
               title: '保存失败',
               icon: 'error',
               mask: true
              });
            }
          })
        },
        fail(res) {
          console.error(res)
        }
      })
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
@import url("team.css");
page {
	height: 100%;
}
.page {
	min-height: 100%;
  background: linear-gradient(#ff4c5e, #ff862e);
}
</style>

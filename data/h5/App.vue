<script>
	export default {
		onLaunch: function() {},
		onShow: function() {},
		onHide: function() {}
	}
</script>

<style>
@import url("@/static/iconfont/iconfont.css");
@import url("@/static/aduty.css");

page {
	background-color: #f6f7fb;
}
.container {
  width: 700rpx;
  margin: 0 auto;
}
.none {
	display: none !important;
}
/* 自定义头部 start */
.customtop_blank {
  height: 54px;
  padding-top: var(--status-bar-height);
}
.customtop {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  margin: auto;
  z-index: 99;
  height: 53px;
  line-height: 53px;
  border-bottom: 1px solid #f5f5f5;
  background: #fff;
  padding: 0 30rpx;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-top: var(--status-bar-height);
}
.customtop .item {
  flex: 1;
}
.customtop .left .iconfont {
  font-weight: bold;
	margin-left: 10px;
}
.customtop .left .iconfont:first-child {
  margin-left: 0;
}
.customtop .title {
  text-align: center;
  white-space: nowrap;
  overflow: hidden;
  text-overflow:ellipsis;
}
.customtop .right {
  text-align: right;
	font-size: 12px;
	color: #999;
}
/* 自定义头部 end */
</style>

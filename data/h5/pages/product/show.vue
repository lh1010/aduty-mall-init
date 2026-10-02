<template>
	<view class="page">
		<CustomTop topTitle="商品"></CustomTop>
    <view class="product_show" v-if="!loading && product.id">
      <view class="top_image">
        <template v-if="product.images.length > 0">
          <swiper class="swiper" circular :autoplay="true" :indicator-dots="true">
            <swiper-item v-for="(item, index) in product.images" :key="index">
              <view class="swiper-item">
                <image class="img" mode="aspectFill" :src="item.image" @click="previewImages(item.image)" />
              </view>
            </swiper-item>
          </swiper>
        </template>
        <template v-if="product.images.length == 0">
          <image class="img" :src="product.cover" mode="aspectFill"></image>
        </template>
      </view>
      <view class="proinfo">
        <view class="name">
          <view class="txt">{{product.name}}</view>
        </view>
        <view class="pricebox aduty-text-price">
          ¥{{currentSku.price}}
        </view>
      </view>
      <view class="specification pagebox" v-if="product.specification_type == '多规格'" @click="popupBuyShow">
        <view class="spebox">
          <view class="label">已选择</view>
          <view class="specification_items">
            <view class="specification_item" v-for="group in product.specifications" :key="group.specification_id">
              {{group.specification_name}}-{{getSelectedOptionName(group.specification_id)}}
            </view>
          </view>
          <i class="iconfont icon-youbian"></i>
        </view>
      </view>
      <view class="content pagebox">
        <view class="attributes" v-if="product.attributes.length > 0">
          <view class="items">
            <view class="item" v-for="(item, index) in product.attributes" :key="index">
              <view class="vm100">{{item.attribute_name}}</view>
              <view class="vm101">{{item.attribute_value}}</view>
            </view>
          </view>
        </view>
        <view class="bd">
          <mp-html :content="product.content" />
        </view>
      </view>
			<!-- foot start -->
      <view class="foot_action_blank"></view>
      <view class="foot_action" v-if="!loading">
      	<view class="container">
      		<view class="left">
      			<view class="item" @click="switchTab('/pages/index/index')">
      				<i class="iconfont icon-shouye"></i>
      				<view class="txt">首页</view>
      			</view>
      			<view class="item" @click="collect" v-if="currentSku?.collect_status != 1">
      				<i class="iconfont icon-shoucang"></i>
      				<view class="txt">收藏</view>
      			</view>
						<view class="item on" @click="collect" v-if="currentSku?.collect_status == 1">
							<i class="iconfont icon-shoucang-shoucang"></i>
      				<view class="txt">已收藏</view>
      			</view>
      			<view class="item" @click="switchTab('/pages/cart/index')">
      				<i class="iconfont icon-gouwuche1"></i>
      				<view class="txt">购物车</view>
      			</view>
      		</view>
      		<view class="right">
      			<view class="btns">
      				<view class="btn btn_cart" @click="popupBuyShow()">加入购物车</view>
      				<view class="btn btn_onekeybuy" @click="popupBuyShow()">立即购买</view>
      			</view>
      		</view>
      	</view>
      </view>
			<!-- foot end -->
			<!-- popup buy start -->
      <uni-popup ref="popupRef" type="bottom" background-color="transparent">
        <view class="popup_buy">
          <view class="btop">
            <view></view>
            <view></view>
            <i class="iconfont icon-quxiao" @click="popupBuyClose"></i>
          </view>
          <view class="box">
            <view class="ppinfo">
              <image :src="currentSku.cover" mode="aspectFill" class="cover"></image>
              <view class="infobox">
                <view class="name">{{product.name}}</view>
                <view class="pricebox aduty-text-price">
                  ¥{{currentSku.price}}
                </view>
                <view class="stockbox">库存 {{currentSku.stock}}</view>
                <view class="numbox">
                  <view class="dec" @click="setCount('dec')"><i class="iconfont icon-jian1"></i></view>
                  <input class="input" v-model="count" />
                  <view class="inc" @click="setCount('inc')"><i class="iconfont icon-jiahao1"></i></view>
                </view>
              </view>
            </view>
            <view class="sitems" v-if="product.specifications && product.specifications.length > 0">
              <view class="sitem" v-for="group in product.specifications" :key="group.specification_id">
                <view class="stitle">{{group.specification_name}}</view>
                <view class="options">
                  <view
                    class="option"
                    :class="{
                      'on': selected[group.specification_id] === opt.specification_option_id,
                      'invalid': opt.valid == 0
                    }"
                    v-for="opt in group.options"
                    :key="opt.specification_option_id"
                    @click="selectOption(group.specification_id, opt.specification_option_id)"
                  >
                    {{opt.specification_option}}
                  </view>
                </view>
              </view>
            </view>
          </view>
          <view class="bfoot">
            <view class="btns">
              <view class="btn btn_cart" @click="addCart()">加入购物车</view>
              <view class="btn btn_onekeybuy" @click="onekeybuy()">立即购买</view>
            </view>
          </view>
        </view>
      </uni-popup>
			<!-- popup buy end -->
    </view>
    <view class="aduty-empty" v-if="!loading && !product.id">
      <image class="aduty-empty-img" src="/static/images/empty.png" mode="aspectFit"></image>
      <text class="aduty-empty-text">商品已下架</text>
    </view>
	</view>
</template>

<script setup>
import { ref } from 'vue';
import { onLoad, onShow } from '@dcloudio/uni-app';
import CustomTop from "@/components/CustomTop.vue";
import http from "@/utils/http.js";
import util from "@/utils/util.js";

const loading = ref(true);
const logined = ref(false);
const id = ref('');
const sku = ref('');
const product = ref({});
const selected = ref({});
const currentSku = ref(null);
const count = ref(1);
const popupRef = ref(null);

onLoad((options) => {
  uni.setNavigationBarTitle({ title: '商品' });
  let params = { ...options };
  if (options.scene != undefined) {
    let scene = decodeURIComponent(options.scene);
    let searchParams = new URLSearchParams(scene);
    for (let [key, value] of searchParams.entries()) {
      params[key] = value;
    }
  }
  id.value = params.id || '';
  sku.value = params.sku || '';
  uni.showLoading();
  getShow();
});

onShow(() => {
  getLoginUser();
});

const getShow = () => {
  http.post('/product/getShow', { id: id.value, sku: sku.value }).then(res => {
    uni.hideLoading();
    if (res.code == 400 || !res.data || !res.data.id) {
      loading.value = false;
      return;
    }
    loading.value = false;
    product.value = res.data;
    currentSku.value = res.data.sku;
    initSpec();
  });
};

const getLoginUser = () => {
  http.post('/account/getLoginUser').then(res => {
    logined.value = res.data.id ? true : false;
  });
};

// 规格初始化
const initSpec = () => {
  const groups = product.value.specifications || [];
  const allSkus = product.value.skus || [];
  // 单规格或无规格时直接返回
  if (!groups.length || !allSkus.length) {
    updateUrlSku(currentSku.value?.sku);
    return;
  }
  initSelected();
  updateAll();
};

// 统一更新入口
const updateAll = () => {
  recalcValidity();
  currentSku.value = matchSku();
  sku.value = currentSku.value?.sku || '';
  updateUrlSku(sku.value);
};

// 初始化规格选中状态
const initSelected = () => {
  const groups = product.value.specifications || [];
  groups.forEach((group) => {
    const gid = group.specification_id;
    const preset = group.options.find(opt => opt.selected == 1);
    if (preset) {
      selected.value[gid] = preset.specification_option_id;
    } else if (group.options.length > 0) {
      const firstValid = group.options.find(opt => opt.valid == 1);
      selected.value[gid] = firstValid ? firstValid.specification_option_id : group.options[0].specification_option_id;
    }
  });
};

// 重新计算所有规格选项的有效性
const recalcValidity = () => {
  const groups = product.value.specifications || [];
  const allSkus = product.value.skus || [];
  if (!groups.length || !allSkus.length) return;

  groups.forEach((group) => {
    const gid = group.specification_id;
    group.options.forEach((opt) => {
      const oid = opt.specification_option_id;
      // 构建假设选中后的选项ID列表（当前组用假设值，其他组用已选值）
      const assumed = [];
      for (const key in selected.value) {
        if (Number(key) === gid) continue;
        assumed.push(selected.value[key]);
      }
      assumed.push(oid);

      // 检查是否存在某个 SKU 完全包含 assumed 中的所有选项ID
      const isValid = allSkus.some(skuItem =>
        assumed.every(id => skuItem.option_ids.includes(id))
      );

      opt.valid = isValid ? 1 : 0;
      opt.selected = (selected.value[gid] === oid) ? 1 : 0;
    });
  });
};

// 根据当前选中的规格组合匹配 SKU
const matchSku = () => {
  const allSkus = product.value.skus || [];
  if (!allSkus.length) return null;
  const selectedIds = Object.values(selected.value).slice().sort((a, b) => a - b);
  return allSkus.find(skuItem => {
    const ids = skuItem.option_ids.slice().sort((a, b) => a - b);
    return ids.length === selectedIds.length && ids.every((id, idx) => id === selectedIds[idx]);
  }) || null;
};

// 点击规格选项
const selectOption = (groupId, optionId) => {
  const group = product.value.specifications.find(g => g.specification_id === groupId);
  if (!group) return;
  const option = group.options.find(o => o.specification_option_id === optionId);
  if (!option || option.valid === 0) return; // 无效项不可点击
  if (selected.value[groupId] === optionId) return; // 已选中不重复处理
  selected.value[groupId] = optionId;
  updateAll();
};

// 获取已选规格选项名称
const getSelectedOptionName = (groupId) => {
  const group = product.value.specifications.find(g => g.specification_id === groupId);
  if (!group) return '';
  const opt = group.options.find(o => o.specification_option_id === selected.value[groupId]);
  return opt ? opt.specification_option : '';
};

// 更新浏览器地址栏中的 sku 参数
const updateUrlSku = (skuVal) => {
  if (!skuVal) return;
  util.updateUrl({ sku: skuVal });
};

const setCount = (ident) => {
  if (ident == 'inc') {
    count.value += 1;
  }
  if (ident == 'dec') {
    if (count.value <= 1) {
      count.value = 1;
      return false;
    }
    count.value -= 1;
  }
};

const popupBuyShow = () => {
  popupRef.value.open();
};

const popupBuyClose = () => {
  popupRef.value.close();
};

const onekeybuy = () => {
  if (!logined.value) {
    jumpPage('/pages/account/login');
    return false;
  }
  jumpPage('/pages/checkout/index?type=onekeybuy&sku=' + currentSku.value.sku + '&count=' + count.value);
};

const addCart = () => {
  uni.showLoading();
  let params = {
    sku: currentSku.value.sku,
    count: count.value
  };
  http.post('/product/addCart', params).then(res => {
    uni.hideLoading();
    if (res.code == 200) {
      uni.showToast({ title: res.message, icon: 'none' });
    } else if (res.code == 400) {
      uni.showToast({ title: res.message, icon: 'none' });
    }
  });
};

const collect = () => {
  if (!logined.value) {
    jumpPage('/pages/account/login');
    return;
  }
  // 取消收藏
  if (currentSku.value.collect_status == 1) {
    currentSku.value.collect_status = 0;
    http.post('/product/deleteCollect', { sku: currentSku.value.sku }).then((res) => {
      if (res.code != 200) {
        currentSku.value.collect_status = 1;
        uni.showToast({ title: res.message || '操作失败', icon: 'none' });
      }
    });
    return;
  }
  // 收藏
  currentSku.value.collect_status = 1;
  http.post('/product/collect', { sku: currentSku.value.sku }).then((res) => {
    if (res.code != 200) {
      currentSku.value.collect_status = 0;
      uni.showToast({ title: res.message || '操作失败', icon: 'none' });
    }
  });
};

const previewImage = (current) => {
  uni.previewImage({ current: current, urls: [current] });
};

const previewImages = (current) => {
  let urls = product.value.images.map((item) => item.image);
  uni.previewImage({ current: current, urls: urls });
};

const switchTab = (url) => {
  uni.switchTab({ url: url });
};

const jumpPage = (url) => {
  uni.navigateTo({ url: url });
};
</script>

<style>
@import url("product.css");
page {}
</style>

<template>
<Top />
<Head />
<Menu />
<div class="proshow">
  <div class="container">
    <template v-if="loading">
      <el-skeleton animated>
        <template #template>
          <div class="skeleton_nav el-skeleton__item"></div>
          <div class="skeleton_proshow">
            <div class="skeleton_left">
              <div class="skeleton_left_top el-skeleton__item"></div>
              <div class="skeleton_left_bottom el-skeleton__item"></div>
            </div>
            <div class="skeleton_right el-skeleton__item"></div>
          </div>
        </template>
      </el-skeleton>
    </template>
    <div class="nav" v-if="!loading">
      <span class="on">当前位置：</span>
      <router-link to="/">首页</router-link>
      <span>></span>
      <router-link :to="'/product/list/' + product.category_id">{{ product.category_name }}</router-link>
      <span>></span>
      <span class="on">详情</span>
    </div>
    <div class="left" v-if="!loading">
      <div class="left_top">
        <div class="lt_left">
          <div class="large-image"><img class="lazy" :src="cover" :alt="product.name"></div>
          <div class="small-images" v-if="product.images.length > 0">
            <img class="lazy img" :class=" item.id == coverId ? 'on' : '' " :src="item.image" v-for="item in product.images" :key="item.id" @mouseenter="changeCover(item)" />
          </div>
          <div class="actions">
            <a href="javascript:void(0);" class="a1 iconfont" @click="collect" v-if="currentSku?.collect_status != 1">收藏</a>
            <a href="javascript:void(0);" class="a1 on iconfont" @click="deleteCollect" v-else>已收藏</a>
            <router-link to="/article/show?type=contact" target="_blank" class="a2 iconfont">举报</router-link>
          </div>
        </div>
        <div class="lt_right">
          <div class="title">{{ product.name }}</div>
          <ul class="ul1 clearfix">
            <li class="li2 iconfont" title="商品分类">{{ product.full_category_name }}</li>
          </ul>
          <div class="price clearfix">
            <div class="sale_price text-price">
              <span class="span1">￥</span>
              <span class="span2">{{ currentSku.price }}</span>
            </div>
          </div>
          <div class="dl">
            <div class="dt">运费</div>
            <div class="dd">免运费</div>
          </div>
          <div class="dl">
            <div class="dt">库存</div>
            <div class="dd">{{ currentSku.stock }}</div>
          </div>
          <template v-if="product.specifications.length > 0">
            <div class="dl specs" v-for="item in product.specifications" :key="item.id">
              <div class="dt">{{ item.specification_name }}</div>
              <div class="dd">
                <div class="specs_items">
                  <div
                    class="specs_item"
                    :class="[
                      item_option.selected == 1 ? 'on' : '',
                      item_option.valid == 0 ? 'invalid' : ''
                    ]"
                    v-for="item_option in item.options"
                    :key="item_option.id"
                    @click="selectOption(item.specification_id, item_option.specification_option_id)"
                  >
                    {{ item_option.specification_option }}
                  </div>
                </div>
              </div>
            </div>
          </template>
          <div class="btns">
            <el-button type="primary" size="large" @click="oneKeyBuy">立即购买</el-button>
            <el-button type="primary" size="large" @click="addCart">加入购物车</el-button>
          </div>
        </div>
      </div>
      <div class="left_main">
        <div class="lm_nav" :class="isNavFixed ? 'fixed' : ''">
          <ul>
            <li class="on">商品详情</li>
          </ul>
          <a class="btn" href="javascript:void(0);" @click="oneKeyBuy" v-if="isNavFixed">立即购买</a>
        </div>
        <div class="content">
          <div class="probqm c1" v-if="product.attributes.length > 0">
             <ul class="probq">
              <template v-for="item in product.attributes" :key="item.id">
                <li class="l1">{{ item.attribute_name }}</li>
                <li class="l2">{{ item.attribute_value }}</li>
              </template>
            </ul>
          </div>
          <div class="c_main c2" v-html="product.content"></div>
        </div>
      </div>
    </div>
    <div class="right">
      <div class="r_section right_prodocts" v-if="newProducts.length > 0">
        <div class="top"><span class="title">推荐商品</span></div>
        <div class="items">
          <router-link :to="'/product/show/' + item.id" target="_blank" class="item" v-for="item in newProducts" :key="item.id">
            <div class="cover"><img class="lazy" :src="item.cover" :alt="item.name"></div>
            <div class="info">
              <div class="name">{{ item.name }}</div>
              <div class="price text-price">¥ {{ item.price }}</div>
            </div>
          </router-link>
        </div>
      </div>
    </div>
  </div>
</div>
<Foot />
</template>

<script setup>
defineOptions({ name: 'ProductShow' });
import { ref, onMounted, onBeforeUnmount, watch } from 'vue';
import http from '@/utils/http';
import Top from '@/components/Top.vue';
import Head from '@/components/Head.vue';
import Menu from '@/components/Menu.vue';
import Foot from '@/components/Foot.vue';
import { useRoute, useRouter } from 'vue-router';
import { ElMessage, ElLoading } from 'element-plus';
import { useCartStore } from '@/stores/cartStore';
import { useConfigStore } from '@/stores/configStore';
import { useUserStore } from '@/stores/userStore';

const configStore = useConfigStore();
const cartStore = useCartStore();
const userStore = useUserStore();
const route = useRoute();
const router = useRouter();
const id = route.params.id;
const loading = ref(true);
const product = ref({});
const selected = ref({}); // 用于记录每组选中的规格选项ID
const currentSku = ref(null); // 当前匹配到的SKU
const cover = ref(''); // 当前显示的封面
const coverId = ref(null);
const newProducts = ref([]); // 最新商品
const isNavFixed = ref(false); // 控制悬浮状态

watch(
  [() => product.value?.name, () => configStore.data?.app_name],
  ([productName, appName]) => {
    document.title = (productName || '') + (appName ? ' ' + appName : '');
  },
  { immediate: true }
);

onMounted(() => {
  getShow();
  getNewProducts();
  window.addEventListener('scroll', onScroll);
});

onBeforeUnmount(() => {
  window.removeEventListener('scroll', onScroll);
});

const onScroll = () => {
  if (loading.value) return;
  const topEl = document.querySelector('.header_top');
  const headEl = document.querySelector('.head');
  const menuEl = document.querySelector('.menu');
  let height = topEl.offsetHeight + headEl.offsetHeight + menuEl.offsetHeight;
  height += 520;
  if (window.scrollY > height) {
    isNavFixed.value = true;
  } else {
    isNavFixed.value = false;
  }
}

const getShow = () => {
  const params = { id: id };
  if (route.query.sku) {
    params.sku = route.query.sku;
  }
  http.post('/product/getShow', params).then((res) => {
    loading.value = false;
    product.value = res.data;
    currentSku.value = res.data.sku;
    initSpec();
  });
}

// 规格初始化
const initSpec = () => {
  const groups = product.value.specifications || [];
  const allSkus = product.value.skus || [];
  // 单规格
  if (!groups.length || !allSkus.length) {
    updateCover();
    updateUrlSku(currentSku.value?.sku);
    return;
  }
  initSelected();
  updateAll();
};

// 统一更新入口
const updateAll = () => {
  recalcValidity(); // 计算所有选项的有效性
  currentSku.value = matchSku(); // 匹配 SKU
  updateCover(); // 更新封面图
  updateUrlSku(currentSku.value?.sku); // 更新浏览器地址栏URL
};

// 初始化规格选中状态
const initSelected = () => {
  const groups = product.value.specifications || [];
  groups.forEach((group) => {
    const gid = group.specification_id;
    // 查找后端标记为默认选中的选项
    const preset = group.options.find(opt => opt.selected == 1);
    if (preset) {
      selected.value[gid] = preset.specification_option_id;
    } else if (group.options.length > 0) {
      // 没有预设项时，尝试选第一个 valid=1 的选项
      const firstValid = group.options.find(opt => opt.valid == 1);
      selected.value[gid] = firstValid ? firstValid.specification_option_id : group.options[0].specification_option_id; // 最终兜底
    }
  });
};

// 重新计算所有规格选项的有效性
const recalcValidity = () => {
  const groups = product.value.specifications || [];
  const allSkus = product.value.skus || [];
  // 无规格或无可用的 SKU 时直接返回
  if (!groups.length || !allSkus.length) return;

  groups.forEach((group) => {
    const gid = group.specification_id;
    group.options.forEach((opt) => {
      const oid = opt.specification_option_id;
      // 构建假设选中后的选项ID列表
      const assumed = [];
      for (const key in selected.value) {
        if (Number(key) === gid) continue;
        assumed.push(selected.value[key]);
      }
      assumed.push(oid);

      // 检查是否存在某个 SKU 完全包含 assumed 中的所有选项ID
      const isValid = allSkus.some(sku =>
        assumed.every(id => sku.option_ids.includes(id))
      );

      // 更新选项属性（Vue 能检测到深层变更）
      opt.valid = isValid ? 1 : 0;
      opt.selected = (selected.value[gid] === oid) ? 1 : 0;
    });
  });
};

// 根据当前选中的规格组合匹配 SKU
const matchSku = () => {
  const allSkus = product.value.skus || [];
  if (!allSkus.length) return null;
  // 获取当前选中的 ID 列表并排序，便于比较
  const selectedIds = Object.values(selected.value).slice().sort((a, b) => a - b);
  return allSkus.find(sku => {
    const ids = sku.option_ids.slice().sort((a, b) => a - b);
    return ids.length === selectedIds.length && ids.every((id, idx) => id === selectedIds[idx]);
  }) || null;
};

// 点击规格选项
const selectOption = (groupId, optionId) => {
  // 查找对应的规格组和选项对象
  const group = product.value.specifications.find(g => g.specification_id === groupId);
  if (!group) return;
  const option = group.options.find(o => o.specification_option_id === optionId);
  if (!option || option.valid === 0) return; // 无效项不可点击
  // 如果点击的是已选中的选项，无需处理
  if (selected.value[groupId] === optionId) return;
  // 更新选中状态
  selected.value[groupId] = optionId;
  // 触发重新计算和匹配
  updateAll();
};

// 更新浏览器地址栏中的 sku 参数
const updateUrlSku = (sku) => {
  if (!sku) return;
  const baseUrl = window.location.pathname;
  const newUrl = baseUrl + '?sku=' + encodeURIComponent(sku);
  window.history.pushState({ sku }, '', newUrl);
};

// 当currentSku变化时自动更新cover
const updateCover = () => {
  if (currentSku.value) {
    cover.value = currentSku.value.cover;
  } else if (product.value.sku) {
    cover.value = product.value.sku.cover;
  }
};

const changeCover = (item) => {
  cover.value = item.image;
  coverId.value = item.id;
}

const oneKeyBuy = () => {
  if (!userStore.isLogin) {
    userStore.showLoginDialog = true;
    return;
  }
  let sku = currentSku.value.sku;
  router.push({ path: '/checkout', query: { type: 'onekeybuy', sku: sku } });
  console.log(sku);
}

const addCart = () => {
  if (!userStore.isLogin) {
    userStore.showLoginDialog = true;
    return;
  }
  doAddCart();
};

const doAddCart = () => {
  let loading = ElLoading.service({ lock: true, background: 'rgba(0, 0, 0, 0.7)' });
  let sku = currentSku.value.sku;
  http.post('/product/addCart', { sku: sku, quantity: 1 }).then((res) => {
    if (res.code === 200) {
      cartStore.getCount();
      ElMessage.success('添加购物成功');
    } else {
      ElMessage.error(res.message || '操作失败');
    }
  }).finally(() => {
    loading.close();
  });
}

const collect = () => {
  if (!userStore.isLogin) {
    userStore.showLoginDialog = true;
    return;
  }
  currentSku.value.collect_status = 1;
  http.post('/product/collect', { sku: currentSku.value.sku }).then((res) => {
    if (res.code === 200) {
    } else {
      currentSku.value.collect_status = 0;
      ElMessage.error(res.message || '操作失败');
    }
  });
}

const deleteCollect = () => {
  currentSku.value.collect_status = 0;
  http.post('/product/deleteCollect', { sku: currentSku.value.sku }).then((res) => {
    if (res.code === 200) {
    } else {
      currentSku.value.collect_status = 1;
      ElMessage.error(res.message || '操作失败');
    }
  });
}

const getNewProducts = () => {
  http.post('/product/getNewProducts').then((res) => {
    newProducts.value = res.data;
  });
}
</script>

<style>
@import '@/assets/style/product.css';
</style>
<style scoped>
.skeleton_nav {
  height: 20px;
  margin-top: 20px;
}
.skeleton_proshow {
  margin-top: 20px;
  display: flex;
  justify-content: space-between;
}
.skeleton_proshow .skeleton_left {
  width: 940px;
}
.skeleton_proshow .skeleton_left_top {
  height: 300px;
}
.skeleton_proshow .skeleton_left_bottom {
  height: 800px;
  margin-top: 20px;
}
.skeleton_proshow .skeleton_right {
  width: 240px;
  height: 800px;
}
</style>

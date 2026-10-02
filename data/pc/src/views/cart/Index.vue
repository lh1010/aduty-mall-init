<template>
<Top />
<Head />
<Menu />
<div class="checkout">
  <div class="container">
    <div class="nav">
      <span class="on">当前位置：</span>
      <router-link to="/">首页</router-link>
      <span>></span>
      <span class="on">购物车</span>
    </div>
    <template v-if="loading">
      <div class="loading container">
        <img src="@/assets/images/loading.gif" alt="loading">
        <div class="txt">正在努力加载中，感谢您的等待</div>
      </div>
    </template>
    <template v-if="!loading">
      <div class="cart" v-if="products.length > 0">
        <table class="items items_head">
          <colgroup>
            <col class="t0">
            <col class="t1">
            <col class="t2">
            <col class="t3">
            <col class="t4">
            <col class="t5">
          </colgroup>
          <thead>
            <tr class="items_top">
              <th class="t0"><i class="check check_all iconfont" :class="{ on: checkAllSelected }" @click="toggleAll"></i></th>
              <th class="t1">商品</th>
              <th class="t2">单价</th>
              <th class="t3">数量</th>
              <th class="t4">小计</th>
              <th class="t5">操作</th>
            </tr>
          </thead>
        </table>
        <table class="items items_shop">
          <colgroup>
            <col class="t0">
            <col class="t1">
            <col class="t2">
            <col class="t3">
            <col class="t4">
            <col class="t5">
          </colgroup>
          <tbody>
            <tr class="item" v-for="product in products" :key="product.sku">
              <td class="t0">
                <i class="check check_product iconfont" :class="{ on: product.selected == 1 }" @click="setCartSelected(product.sku, product.selected == 1 ? 0 : 1)"></i>
              </td>
              <td class="t1 proinfo">
                <div class="cover">
                  <img class="lazy" :src="product.cover" alt="product.name" />
                </div>
                <div class="infobox">
                  <div class="name">
                    <router-link :to="`/product/show/${product.id}?sku=${product.sku}`" target="_blank">{{ product.name }}</router-link>
                  </div>
                  <div class="types" v-if="product.specifications && product.specifications.length > 0">
                    <div class="types_item" v-for="spec in product.specifications" :key="spec.id">
                      {{ spec.specification_name }}-{{ spec.specification_option }}
                    </div>
                  </div>
                </div>
              </td>
              <td class="t2 text-price">¥{{ product.price }}</td>
              <td class="t3">x{{ product.count }}</td>
              <td class="t4 text-price">¥{{ product.total_price }}</td>
              <td class="t5 actions"><a href="javascript:void(0);" @click="deleteCart(product.sku)">删除</a></td>
            </tr>
          </tbody>
        </table>
        <div class="cart_foot">
          <div class="cfmain">
            <div class="pricebox">
              <span class="s1">合计：</span>
              <span class="s2 text-price">¥</span>
              <span class="s3 text-price">{{  calculating ? '计算中' : totalData.total_price }}</span>
            </div>
            <div class="btn">
              <el-button type="primary" @click="goCheckout()">去结算</el-button>
            </div>
          </div>
        </div>
      </div>
      <div class="cart_empty" v-else>
        <img src="@/assets/images/cart.png" alt="购物车为空" />
        <div class="info">
          <p>购物车还是空空的呢，快去看看心仪的商品吧~</p>
          <router-link to="/">去购买 ></router-link>
        </div>
      </div>
    </template>
  </div>
</div>
<Foot />
</template>

<script setup>
defineOptions({ name: 'Cart' });
import { ref, computed, onMounted, watch } from 'vue';
import http from '@/utils/http';
import Top from '@/components/Top.vue';
import Head from '@/components/Head.vue';
import Menu from '@/components/Menu.vue';
import Foot from '@/components/Foot.vue';
import { useRouter } from 'vue-router';
import { ElMessage, ElLoading, ElMessageBox } from 'element-plus';
import { useConfigStore } from '@/stores/configStore';
import { useCartStore } from '@/stores/cartStore';

const cartStore = useCartStore();
const configStore = useConfigStore();
const router = useRouter();
const loading = ref(true);
const calculating = ref(false);
const products = ref([]);
const totalData = ref({});

watch(() => configStore.data.app_name, (newName) => { document.title = '购物车' + (newName ? ' ' + newName : ''); }, { immediate: true });

onMounted(() => {
  getCartData();
});

const getCartData = () => {
  return http.post('/order/getCartData').then(res => {
    products.value = res.data.products;
    totalData.value = res.data.totalData;
  }).finally(() => {
    loading.value = false;
  });
};

const setCartSelected = (sku, selected) => {
  calculating.value = true;
  products.value.forEach(product => {
    if (product.sku === sku) {
      product.selected = selected;
    }
  });
  http.post('/product/setCartSelected', { sku, selected }).then(res => {
    totalData.value.total_price = res.data.total_price;
    calculating.value = false;
  });
};

const toggleAll = () => {
  calculating.value = true;
  const newSelected = checkAllSelected.value ? 0 : 1;
  products.value.forEach(product => {
    product.selected = newSelected;
  });
  let skus = products.value.map(item => item.sku);
  http.post('/product/setCartSelectedBatch', { skus, selected: newSelected }).then(res => {
    totalData.value.total_price = res.data.total_price;
    calculating.value = false;
  });
};

const checkAllSelected = computed(() => {
  return products.value.length > 0 && products.value.every(item => item.selected == 1);
});

const deleteCart = (sku) => {
  ElMessageBox.confirm('确定要删除该商品吗？').then(() => {
    let loading = ElLoading.service({ lock: true, background: 'rgba(0, 0, 0, 0.7)' });
    calculating.value = true;
    http.post('/product/deleteCart', { sku }).then(res => {
      cartStore.getCount();
      ElMessage.success('删除成功');
      getCartData().then(() => {
        calculating.value = false;
      });
    }).finally(() => {
      loading.close();
    });
  }).catch(() => {});
};

const goCheckout = () => {
  let selectedProducts = products.value.filter(item => item.selected == 1);
  if (selectedProducts.length === 0) {
    ElMessage.error('请选择要结算的商品');
    return;
  }
  router.push({ path: '/checkout', query: { type: 'cart' } });
};
</script>

<style lang="less" scoped>
@import '@/assets/style/cart.css';
</style>

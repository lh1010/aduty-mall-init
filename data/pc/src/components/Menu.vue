<template>
<div class="menu">
  <div class="container">
    <div class="home_menu">
      <div class="dt"><a href="javascript:void(0);"><i class="iconfont">&#xe642;</i>商品分类</a></div>
      <div class="dd" :class="{ none: route.path !== '/' }">
        <div class="item" v-for="(item) in menuStore.menus" :key="item.id">
          <div class="item_home">
            <div class="title">
              <router-link class="link" :to="'/product/list/' + item.id" target="_blank">{{item.name}}<i>&gt;</i></router-link>
            </div>
          </div>
          <!-- 展开层 start -->
          <template v-if="item.items && item.items.length > 0">
            <div class="item_layer none">
              <div class="subitems">
                <dl v-for="(item1) in item.items" :key="item1.id">
                  <dt>
                    <router-link class="link" :to="'/product/list/' + item1.id" target="_blank">{{item1.name}}<i>&gt;</i></router-link>
                  </dt>
                  <dd>
                    <router-link class="link" :to="'/product/list/' + item2.id" v-for="(item2) in item1.items" :key="item2.id" target="_blank">{{item2.name}}</router-link>
                  </dd>
                </dl>
              </div>
            </div>
          </template>
          <!-- 展开层 end -->
          <!-- 预留右侧广告位 start -->
          <!-- ............ -->
          <!-- 预留右侧广告位 end -->
        </div>
      </div>
    </div>
    <ul class="custom_menu">
      <li><router-link class="link" to="/product/list" target="_blank">全部商品</router-link></li>
      <li><router-link class="link" to="/help/show/100000" target="_blank">帮助中心</router-link></li>
    </ul>
  </div>
</div>
</template>

<script setup>
defineOptions({ name: 'Menu' });
import { ref, onMounted } from 'vue';
import { useMenuStore } from '@/stores/menuStore';
import http from '@/utils/http';
import { useRoute } from 'vue-router';

const menuStore = useMenuStore();
const menuData = ref([]);
const route = useRoute();
console.log();

onMounted(() => {
  menuStore.getMenus();
  // getMenus();
});

const getMenus = () => {
  http.post('/common/getMenus').then((res) => {
    menuData.value = res.data;
  });
};
</script>

<style lang="less" scoped></style>

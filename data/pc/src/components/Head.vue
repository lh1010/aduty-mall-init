<template>
<div class="head">
  <div class="container">
    <div class="head_box">
      <router-link class="logo" to="/">
        <img :src="configStore.data.pc.app_logo" v-if="configStore.data.pc && configStore.data.pc.app_logo"/>
      </router-link>
      <div class="mainbd">
        <div class="search" id="topsearch">
          <div class="search_box">
            <input class="k" type="text" v-model="keyword" @keyup.enter="search" placeholder="请输入关键字">
            <button class="search_btn" type="button" @click="search"><i class="iconfont">&#xe8d6;</i>搜索</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</template>

<script setup>
defineOptions({ name: 'Head' });
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useRouter } from 'vue-router';
import { useConfigStore } from '@/stores/configStore';

const configStore = useConfigStore();
const route = useRoute();
const router = useRouter();
const keyword = ref('');

onMounted(() => {
  const urlK = route.query.k;
  if (urlK) {
    keyword.value = decodeURIComponent(urlK);
  }
});

const search = () => {
  const k = keyword.value.trim();
  if (k) {
    router.push('/product/list?k=' + encodeURIComponent(k));
  } else {
    router.push('/product/list');
  }
};
</script>

<style lang="less" scoped></style>

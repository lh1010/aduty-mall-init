<template>
<Top />
<Head />
<Menu />
<div class="article_show">
  <div class="container">
    <div class="box" v-loading="loading">
      <div class="title">{{ article.title }}</div>
      <div class="content" v-html="article.content"></div>
    </div>
  </div>
</div>
<Foot />
</template>

<script setup>
defineOptions({ name: 'HelpShow' });
import { ref, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import http from '@/utils/http';
import Top from '@/components/Top.vue';
import Head from '@/components/Head.vue';
import Menu from '@/components/Menu.vue';
import Foot from '@/components/Foot.vue';
import { useConfigStore } from '@/stores/configStore';

const configStore = useConfigStore();
const route = useRoute();
const id = route.params.id;
const type = route.query.type;
const article = ref({});
const loading = ref(true);

watch(() => configStore.data.app_name, (newName) => {
  document.title = (article.value.title ? article.value.title + ' ' : '') + '帮助中心' + (newName ? ' ' + newName : '');
}, { immediate: true });

onMounted(() => {
  getShow();
  console.log(id, type);
});

const getShow = () => {
  loading.value = true;
  http.post('/article/getShow', { id: id, type: type }).then(res => {
    article.value = res.data;
  }).finally(() => {
    loading.value = false;
  });
};
</script>

<style lang="less" scoped>
@import '@/assets/style/article.css';
.skeleton_left {
  float: left;
  width: 220px;
  height: 500px;
  border-radius: 6px;
}
</style>

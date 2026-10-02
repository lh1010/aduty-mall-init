<template>
<Top />
<Head />
<Menu />
<div class="help_show">
  <div class="container">
    <template v-if="categoryLoading">
      <el-skeleton animated>
        <template #template>
          <div class="skeleton_left el-skeleton__item"></div>
        </template>
      </el-skeleton>
    </template>
    <div class="left" v-if="!categoryLoading">
      <dl v-for="category in categorys" :key="category.id">
      <dt @click="toggleCategory(category.id)">
        <span>{{ category.name }}</span>
        <i class="iconfont" :class="expandedIds.includes(category.id) ? 'up' : 'down'"></i>
      </dt>
      <dd v-show="expandedIds.includes(category.id)">
        <a
          v-for="article in category.articles"
          :key="article.id"
          class="item"
          :class="{ on: article.id == articleId }"
          href="javascript:void(0);"
          @click="selectArticle(article.id)"
        >{{ article.title }}</a>
      </dd>
      </dl>
    </div>
    <div class="right" v-loading="loading">
      <div class="box">
        <div class="title">{{ article.title }}</div>
        <div class="content" v-html="article.content"></div>
      </div>
    </div>
  </div>
</div>
<Foot />
</template>

<script setup>
defineOptions({ name: 'HelpShow' });
import { ref, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import http from '@/utils/http';
import Top from '@/components/Top.vue';
import Head from '@/components/Head.vue';
import Menu from '@/components/Menu.vue';
import Foot from '@/components/Foot.vue';
import { useConfigStore } from '@/stores/configStore';

const configStore = useConfigStore();
const route = useRoute();
const router = useRouter();
const articleId = ref(route.params.id);
const article = ref({});
const loading = ref(true);
const categoryLoading = ref(true);
const categorys = ref([]);
const expandedIds = ref([]);

watch(() => configStore.data.app_name, (newName) => {
  document.title = (article.value.title ? article.value.title + ' ' : '') + '帮助中心' + (newName ? ' ' + newName : '');
}, { immediate: true });

watch(() => route.params.id, (newId) => {
  if (newId && newId != articleId.value) {
    articleId.value = newId;
    getShow();
  }
});

onMounted(() => {
  getCategorys();
  getShow();
});

const getCategorys = () => {
  categoryLoading.value = true;
  http.post('/article/getHelpCategorys').then(res => {
    categorys.value = res.data;
    const currentCategory = categorys.value.find(c => c.articles.some(a => a.id == articleId.value));
    if (currentCategory) {
      expandedIds.value = [currentCategory.id];
    } else if (categorys.value.length > 0) {
      expandedIds.value = [categorys.value[0].id];
    }
  }).finally(() => {
    categoryLoading.value = false;
  });
};

const getShow = () => {
  loading.value = true;
  window.scrollTo({ top: 0 });
  http.post('/article/getShow', { id: articleId.value }).then(res => {
    article.value = res.data;
  }).finally(() => {
    loading.value = false;
  });
};

const selectArticle = (id) => {
  articleId.value = id;
  router.replace('/help/show/' + id);
  getShow();
};

const toggleCategory = (id) => {
  const index = expandedIds.value.indexOf(id);
  if (index > -1) {
    expandedIds.value.splice(index, 1);
  } else {
    expandedIds.value.push(id);
  }
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

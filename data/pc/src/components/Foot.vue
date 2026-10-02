<template>
<div class="foot">
  <div class="container">
    <div class="foot_menu">
      <dl v-for="help in helps" :key="help.id">
        <dt>{{ help.name }}</dt>
        <dd v-for="article in help.articles" :key="article.id">
          <router-link :to="'/help/show/' + article.id" target="_blank">{{ article.title }}</router-link>
        </dd>
      </dl>
      <div class="foot_contact">
        <div class="item wxapp_qrcode">
          <img :src="configStore.data.wxmp.qrcode" alt="微信公众号" v-if="configStore && configStore.data.wxmp.qrcode">
          <p class="txt">微信公众号</p>
        </div>
      </div>
    </div>
    <div class="foot_link">
      <span>友情链接</span>
      <ul class="clearfix">
        <li><a href="https://www.linghao100.com" target="_blank">领浩科技</a></li>
        <li><a href="http://home.adutymall.lh909.com" target="_blank">开源商城</a></li>
        <li><a href="https://www.linghao100.com/s/swb" target="_blank">商务邦系统</a></li>
        <li><a href="https://www.linghao100.com/s/tg" target="_blank">红人通告系统</a></li>
        <li><a href="https://www.linghao100.com/s/ttb" target="_blank">APP拉新系统</a></li>
        <li><a href="https://www.linghao100.com/s/qun" target="_blank">社群人脉系统</a></li>
        <li><a href="https://www.aliyun.com" target="_blank">阿里云</a></li>
      </ul>
    </div>
    <p class="copyright">
      © {{ new Date().getFullYear() }} {{ configStore.data.pc.app_name }} <a href="https://beian.miit.gov.cn" target="_blank">{{ configStore.data.beian }}</a>
    </p>
  </div>
</div>
</template>

<script setup>
defineOptions({ name: 'Foot' });
import { ref, onMounted } from 'vue';
import http from '@/utils/http';
import { useConfigStore } from '@/stores/configStore';

const configStore = useConfigStore();
const helps = ref({});

onMounted(() => {
  getHelps();
});

const getHelps = () => {
  http.post('/common/getHelps').then(res => {
    helps.value = res.data;
  });
};
</script>

<style lang="less" scoped></style>

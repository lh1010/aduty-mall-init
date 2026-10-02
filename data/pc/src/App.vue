<template>
  <div id="app">
    <router-view />
  </div>
</template>

<script setup>
defineOptions({
  name: 'App'
});
import { onMounted } from 'vue';
import { useConfigStore } from '@/stores/configStore';

const configStore = useConfigStore();

onMounted(() => {
  configStore.getConfig();
});
</script>

<style scoped></style>

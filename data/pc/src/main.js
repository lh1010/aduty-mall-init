import '@/assets/style/base.css'
import '@/assets/style/style.css'

import { createApp } from 'vue'
import App from './App.vue'
import router from './router'

import ElementPlus from 'element-plus'
import 'element-plus/dist/index.css'
import '@/assets/style/element-override.css' // 覆盖样式
import zhCn from 'element-plus/dist/locale/zh-cn.mjs'
import * as ElementPlusIconsVue from '@element-plus/icons-vue'

// pinia
import { createPinia } from 'pinia'
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate'
// 创建并配置 pinia
const pinia = createPinia()
pinia.use(piniaPluginPersistedstate)  // 启用持久化

// 创建 Vue 实例
const app = createApp(App)
// 注册路由
app.use(router)
// 注册图标
for (const [key, component] of Object.entries(ElementPlusIconsVue)) {
  app.component(key, component)
}
// 注册 Element Plus
app.use(ElementPlus, { locale: zhCn })
// 使用配置好的 pinia 实例
app.use(pinia)
// 挂载到 DOM
app.mount('#app')

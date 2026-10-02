// 公共配置
import { defineStore } from 'pinia';
import http from '@/utils/http';

export const useConfigStore = defineStore('config', {
  // 存放数据
  state: () => ({
    data: {
      pc: {},
      wxmp: {},
      image: {},
      withdrawal: {},
    }, // 配置数据
    expireTime: 0, // 过期时间
  }),

  getters: {
    isValid: (state) => {
      return Object.keys(state.data).length > 0 && Date.now() < state.expireTime;
    },
  },

  // 存放方法
  actions: {
    // 获取菜单数据
    getConfig() {
      if (this.isValid) return;
      http.post('/common/getConfig').then((res) => {
        this.data = res.data;
        this.expireTime = Date.now() + 30 * 60 * 1000; // 标签内过期时间 30分钟
      });
    },
  },

  // 持久化存储
  // persist: {
  //   key: 'menu-store',
  // },
})

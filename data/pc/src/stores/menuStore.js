// 菜单
import { defineStore } from 'pinia';
import http from '@/utils/http';

export const useMenuStore = defineStore('menu', {
  // 存放数据
  state: () => ({
    menus: [], // 菜单数据
    expireTime: 0, // 过期时间
  }),

  getters: {
    isValid: (state) => {
      return state.menus.length > 0 && Date.now() < state.expireTime;
    },
  },

  // 存放方法
  actions: {
    // 获取菜单数据
    getMenus() {
      if (this.isValid) return;
      http.post('/common/getMenus').then((res) => {
        this.menus = res.data;
        this.expireTime = Date.now() + 30 * 60 * 1000; // 过期时间 30分钟
      });
    },
  },

  // 持久化存储
  // persist: {
  //   key: 'menu-store',
  // },
})

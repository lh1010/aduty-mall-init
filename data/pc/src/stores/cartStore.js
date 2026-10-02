// 公共配置
import { defineStore } from 'pinia';
import http from '@/utils/http';

export const useCartStore = defineStore('cart', {
  // 存放数据
  state: () => ({
    count: 0, // 购物车数量
  }),

  // 存放方法
  actions: {
    getCount() {
      http.post('/product/getCartCount').then((res) => {
        this.count = res.data;
        this.expireTime = Date.now() + 30 * 60 * 1000; // 过期时间 30分钟
      });
    },

    clear() {
      this.count = 0;
      this.expireTime = 0;
    }
  },

  // 持久化存储
  // persist: {
  //   key: 'menu-store',
  // },
})

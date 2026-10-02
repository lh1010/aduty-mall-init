// 用户信息
import { defineStore } from 'pinia'
import { useCartStore } from '@/stores/cartStore'

export const useUserStore = defineStore('user', {
  // 存放数据
  state: () => ({
    token: '',
    userInfo: {},
    showLoginDialog: false // 全局登录弹窗状态
  }),

  // getters
  getters: {
    // 判断是否登录
    isLogin: (state) => !!state.token,
  },

  actions: {
    // 登录成功保存信息
    setUserData(token, userInfo) {
      this.token = token;
      this.userInfo = userInfo;
    },

    // 登出
    logout() {
      this.token = '';
      this.userInfo = {};
      // 清除购物车数量
      const cartStore = useCartStore();
      cartStore.clear();
    },
  },

  // 持久化存储
  persist: {
    key: 'user-store',
    storage: localStorage, // 存储方式
    pick: ['token', 'userInfo']  // 持久化字段
  }
})

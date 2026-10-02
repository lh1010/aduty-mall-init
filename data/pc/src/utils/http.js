import axios from 'axios';
import router from '/src/router';
import config from "./config.js";
import { useUserStore } from '@/stores/userStore';

// 创建axios实例
const http = axios.create({
  baseURL: config.url + '/api',
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
  },
});

// 请求拦截器
http.interceptors.request.use(
  requestConfig => {
    const userStore = useUserStore();
    // header 请求头
    requestConfig.headers['token'] = userStore.token;
    requestConfig.headers['client'] = config.client;
    requestConfig.headers['version'] = config.version;
    // body 请求体
    // requestConfig.data = requestConfig.data || {};
    // requestConfig.data['client'] = config.client;
    // requestConfig.data['version'] = config.version;
    return requestConfig;
  },
  error => {
    // 处理请求错误
    return Promise.reject(error);
  }
);

// 响应拦截器
http.interceptors.response.use(
  response => {
    if (response.data.code == 401) {
      const userStore = useUserStore();
      userStore.logout();
      router.replace('/login');
      return;
    }
    return response.data;
  },
  error => {
    // 响应错误
    if (error.response) {
      switch (error.response.status) {
        case 401:
          // 未授权，重定向到登录页面或显示错误消息
          break;
        case 403:
          // 禁止访问，显示错误消息
          break;
        case 404:
          // 资源未找到，显示错误消息
          break;
        // 其他错误状态码处理...
      }
    }
    return Promise.reject(error);
  }
);

export default http;

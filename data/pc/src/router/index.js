import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'Home',
      component: () => import('@/views/Home.vue')
    },
    {
      path: '/product/list/:id?',
      name: 'ProductList',
      component: () => import('@/views/product/List.vue')
    },
    {
      path: '/product/show/:id?',
      name: 'ProductShow',
      component: () => import('@/views/product/Show.vue')
    },
    {
      path: '/login',
      name: 'Login',
      component: () => import('@/views/account/Login.vue')
    },
    {
      path: '/register',
      name: 'Register',
      component: () => import('@/views/account/Register.vue')
    },
    {
      path: '/article/show/:id?',
      name: 'ArticleShow',
      component: () => import('@/views/article/Show.vue')
    },
    {
      path: '/help/show/:id?',
      name: 'HelpShow',
      component: () => import('@/views/help/Show.vue')
    },
    {
      path: '/cart',
      name: 'Cart',
      component: () => import('@/views/cart/Index.vue')
    },
    {
      path: '/checkout',
      name: 'Checkout',
      component: () => import('@/views/checkout/Index.vue')
    },
    {
      path: '/checkout/pay',
      name: 'CheckoutPay',
      component: () => import('@/views/checkout/Pay.vue')
    },
    {
      path: '/account/user_info',
      name: 'AccountUserInfo',
      component: () => import('@/views/account/UserInfo.vue')
    },
    {
      path: '/account/user_password',
      name: 'AccountUserPassword',
      component: () => import('@/views/account/UserPassword.vue')
    },
    {
      path: '/account/user_contact',
      name: 'AccountUserContact',
      component: () => import('@/views/account/UserContact.vue')
    },
    {
      path: '/account/order_list',
      name: 'AccountOrderList',
      component: () => import('@/views/account/OrderList.vue')
    },
    {
      path: '/account/order_show',
      name: 'AccountOrderShow',
      component: () => import('@/views/account/OrderShow.vue')
    },
    {
      path: '/account/collect_product',
      name: 'AccountCollectProduct',
      component: () => import('@/views/account/CollectProduct.vue')
    },
    {
      path: '/account/realname_auth',
      name: 'AccountRealnameAuth',
      component: () => import('@/views/account/RealnameAuth.vue')
    },
    {
      path: '/account/company_auth',
      name: 'AccountCompanyAuth',
      component: () => import('@/views/account/CompanyAuth.vue')
    },
    {
      path: '/account/wallet',
      name: 'AccountWallet',
      component: () => import('@/views/account/Wallet.vue')
    },
    {
      path: '/account/wallet_logs',
      name: 'AccountWalletLogs',
      component: () => import('@/views/account/WalletLogs.vue')
    },
    {
      path: '/account/wallet_pay',
      name: 'AccountWalletPay',
      component: () => import('@/views/account/WalletPay.vue')
    },
    {
      path: '/account/wallet_withdraw',
      name: 'AccountWalletWithdraw',
      component: () => import('@/views/account/WalletWithdraw.vue')
    },
    {
      path: '/account/withdrawal_logs',
      name: 'AccountWithdrawalLogs',
      component: () => import('@/views/account/WithdrawalLogs.vue')
    },
    {
      path: '/account/address_list',
      name: 'AccountAddressList',
      component: () => import('@/views/account/AddressList.vue')
    },
  ],
})

export default router

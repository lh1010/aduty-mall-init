<template>
<Top />
<Head />
<Menu />
<div class="checkout okb">
  <div class="container">
    <div class="nav">
      <span class="on">当前位置：</span>
      <router-link to="/">首页</router-link>
      <span>></span>
      <span class="on">确认订单</span>
    </div>
    <div class="cpros" v-loading="loading">
      <div class="addressbox">
        <div class="stitle">地址选择</div>
        <div class="address_items">
          <div class="address_item" :class="{ on: item.id == addressId }" v-for="item in addresses" :key="item.id" @click=" addressId = item.id ">
            <div class="address-box-info" :title=" item.name + ' ' + item.province_name + ' ' + item.city_name + ' ' + item.district_name + ' ' + item.detailed_address + ' ' + item.phone ">
              <div class="name">{{ item.name }}</div>
              <div class="detaile">{{ item.province_name }} {{ item.city_name }} {{ item.district_name }}</div>
              <div class="detaile">{{ item.detailed_address }}</div>
              <div class="number-phone">{{ item.phone }}</div>
            </div>
          </div>
        </div>
        <div class="add-btn" @click=" dialogVisible = true "><i class="iconfont">&#xe727;</i> 使用新地址</div>
      </div>
      <div class="probox">
        <div class="stitle">商品清单</div>
        <table class="items">
          <thead>
            <tr class="items_top">
              <th class="t1">商品</th>
              <th class="t2">单价</th>
              <th class="t3">数量</th>
              <th class="t4">小计</th>
            </tr>
          </thead>
          <tbody>
            <tr class="item" v-for="item in checkoutData.products" :key="item.sku">
              <td class="t1 proinfo">
                <div class="cover"><img class="lazy" :src="item.cover" alt="item.sku"></div>
                <div class="infobox">
                  <div class="name">
                    <router-link :to="'/product/show/' + item.id + '?sku=' + item.sku" target="_blank">{{ item.name }}</router-link>
                  </div>
                  <div class="types" v-if="item.specifications && item.specifications.length > 0">
                    <div class="types_item" v-for="(item_specn, index_specn) in item.specifications" :key="index_specn">
                      {{ item_specn.specification_name }}-{{ item_specn.specification_option }}
                    </div>
                  </div>
                </div>
              </td>
              <td class="t2 text-price">¥{{ item.price }}</td>
              <td class="t3">x{{ item.count }}</td>
              <td class="t4 text-price">¥{{ item.total_price }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="cpros_foot">
        <div class="cfmain">
          <div class="pricebox">
            <span class="s1">合计：</span>
            <span class="s2 text-price">¥</span>
            <span class="s3 text-price">{{ checkoutData.totalData.total_price }}</span>
          </div>
          <div class="btn">
            <el-button type="primary" size="large" @click="createOrder">提交订单</el-button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<Foot />
<el-dialog v-model="dialogVisible" title="添加地址" width="600">
  <el-form :model="form" ref="formRef" :rules="rules" label-width="auto">
    <el-form-item label="收货人" prop="name">
      <el-input v-model="form.name" placeholder="请输入收货人姓名" />
    </el-form-item>
    <el-form-item label="手机号" prop="phone">
      <el-input v-model="form.phone" placeholder="请输入收货人手机号" />
    </el-form-item>
    <el-form-item label="省市区" prop="region">
      <el-cascader v-model="form.region" :options="regionOptions" placeholder="请选择省市区" clearable />
    </el-form-item>
    <el-form-item label="详细地址" prop="detailed_address">
      <el-input v-model="form.detailed_address" placeholder="请输入详细地址" />
    </el-form-item>
    <el-form-item label="是否默认">
      <el-switch v-model="form.default" active-text="是" inactive-text="否" />
    </el-form-item>
  </el-form>
  <template #footer>
    <el-button @click=" dialogVisible = false ">取消</el-button>
    <el-button type="primary" @click="submitAddress">确认添加</el-button>
  </template>
</el-dialog>
</template>

<script setup>
defineOptions({ name: 'CheckoutOnekeybuy' });
import { ref, reactive, onMounted, watch } from 'vue';
import http from '@/utils/http';
import Top from '@/components/Top.vue';
import Head from '@/components/Head.vue';
import Menu from '@/components/Menu.vue';
import Foot from '@/components/Foot.vue';
import { useRoute, useRouter } from 'vue-router';
import { ElMessage, ElLoading } from 'element-plus';
import { useConfigStore } from '@/stores/configStore';

const configStore = useConfigStore();
const route = useRoute();
const router = useRouter();
const sku = route.query.sku;
const loading = ref(true);
const checkoutData = ref({products: [], address: {}, totalData: {}});
const addresses = ref([]);
const addressId = ref(null);
// 添加地址
const dialogVisible = ref(false);
const formRef = ref(null);
const form = reactive({
  name: '',
  phone: '',
  region: [],
  detailed_address: '',
  default: false,
});
const regionOptions = ref([]); // 省市区数据
const rules = {
  name: [
    { required: true, message: '请输入收货人姓名', trigger: 'blur' },
    { min: 2, max: 20, message: '姓名长度在 2-20 个字符', trigger: 'blur' }
  ],
  phone: [
    { required: true, message: '请输入手机号', trigger: 'blur' },
    { pattern: /^1[3-9]\d{9}$/, message: '请输入正确的手机号', trigger: 'blur' }
  ],
  region: [
    {
      required: true,
      message: '请选择省市区',
      trigger: 'change',
      validator: (rule, value, callback) => {
        if (!value || value.length < 3) {
          callback(new Error('请选择完整的省市区'))
        } else {
          callback()
        }
      }
    }
  ],
  detailed_address: [
    { required: true, message: '请输入详细地址', trigger: 'blur' }
  ]
};

watch(() => configStore.data.app_name, (newName) => { document.title = '确认订单' + (newName ? ' ' + newName : ''); }, { immediate: true });

onMounted(() => {
  getCheckoutData();
  getAddresses();
  getRegionOptions();
});

const getCheckoutData = () => {
  http.post('/order/getCheckoutData', { type: 'onekeybuy', sku: sku }).then(res => {
    loading.value = false;
    checkoutData.value = res.data;
    if (res.data.address) {
      addressId.value = res.data.address.id;
    }
  });
};

const getAddresses = () => {
  http.post('/address/getList').then(res => {
    addresses.value = res.data || [];
  });
};

// 获取省市区数据
const getRegionOptions = () => {
  http.post('/common/getRegionOptions').then(res => {
    regionOptions.value = res.data;
  });
};

const submitAddress = () => {
  if (!formRef.value) return;
  formRef.value.validate((valid) => {
    if (!valid) return;
    let loading = ElLoading.service({ lock: true, background: 'rgba(0, 0, 0, 0.7)' });
    let params = form;
    http.post('/address/store', params).then(res => {
      if (res.code == 200) {
        ElMessage.success('地址添加成功');
        dialogVisible.value = false;
        formRef.value.resetFields();
        getAddresses();
      } else {
        ElMessage.error(res.message || '地址添加失败');
      }
    }).finally(() => {
      loading.close();
    });
  })
};

const createOrder = () => {
  if (!addressId.value) {
    ElMessage.warning('请选择收货地址');
    return;
  }
  let loading = ElLoading.service({ lock: true, background: 'rgba(0, 0, 0, 0.7)' });
  http.post('/order/createOrder', { type: 'onekeybuy', sku: sku, address_id: addressId.value }).then(res => {
    if (res.code == 200) {
      ElMessage.success('订单创建成功');
      router.push('/checkout/pay?order_ids=' + res.data.order_ids);
    } else if (res.code == 400) {
      ElMessage.error(res.message || '订单创建失败');
    } else {
      ElMessage.error(res.message || '订单创建失败');
    }
  }).finally(() => {
    loading.close();
  });
};
</script>

<style lang="less" scoped>
@import '@/assets/style/checkout.css';
</style>

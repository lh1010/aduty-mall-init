<template>
<Top />
<TopNav />
<div class="account clearfix realname_auth">
  <div class="container">
    <LeftMenu />
    <!-- main box start -->
    <div class="account_main">
      <div class="am_unify_box address_list">
        <div class="top">
          <span class="title">收货地址</span>
          <span class="action">
            <a href="javascript:void(0);" class="text-link" @click="openEdit()">添加地址</a>
          </span>
        </div>
        <template v-if="dataListLoading">
          <div class="loading">
            <img src="@/assets/images/loading.gif" alt="loading">
            <div class="txt">正在努力加载中，感谢您的等待</div>
          </div>
        </template>
        <template v-if="!dataListLoading">
          <template v-if="dataList.length > 0">
            <el-table :data="dataList" border style="width: 100%" v-loading="dataItemsLoading">
              <el-table-column prop="name" label="收货人" width="120" />
              <el-table-column prop="phone" label="手机号" width="150" />
              <el-table-column label="收货地址">
                <template #default="scope">
                  {{ scope.row.province_name }} {{ scope.row.city_name }} {{ scope.row.district_name }} {{ scope.row.detailed_address }}
                </template>
              </el-table-column>
              <el-table-column prop="default" label="默认地址" width="100">
                <template #default="scope">
                  <span v-if="scope.row.default == 1" class="color">是</span>
                  <span v-else>否</span>
                </template>
              </el-table-column>
              <el-table-column label="操作" width="150">
                <template #default="scope">
                  <a href="javascript:void(0);" @click="openEdit(scope.row)">修改</a>
                  <a href="javascript:void(0);" @click="deleteAddress(scope.row.id)" style="margin-left: 10px;">删除</a>
                </template>
              </el-table-column>
            </el-table>
          </template>
          <div class="noresult" v-if="dataList.length == 0">
            <img src="@/assets/images/noresult.png" alt="noresult">
            <p>暂无收货地址</p>
          </div>
        </template>
      </div>
    </div>
    <!-- main box end -->
  </div>
</div>
<Foot />
<el-dialog v-model="dialogVisible" :title="isEdit ? '修改地址' : '添加地址'" width="600">
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
    <el-button @click="dialogVisible = false">取消</el-button>
    <el-button type="primary" @click="submitAddress">{{ isEdit ? '确认修改' : '确认添加' }}</el-button>
  </template>
</el-dialog>
</template>

<script setup>
defineOptions({ name: 'AccountAddressList' });
import Top from '@/components/Top.vue';
import TopNav from '@/components/account/TopNav.vue';
import LeftMenu from '@/components/account/LeftMenu.vue';
import Foot from '@/components/Foot.vue';
import { ref, reactive, onMounted } from 'vue';
import http from '@/utils/http';
import { ElMessage, ElLoading, ElMessageBox } from 'element-plus';

const dataListLoading = ref(true);
const dataList = ref([]);
const dataItemsLoading = ref(true);

// 地址弹窗
const dialogVisible = ref(false);
const isEdit = ref(false);
const editId = ref(null);
const formRef = ref(null);
const form = reactive({
  name: '',
  phone: '',
  region: [],
  detailed_address: '',
  default: false,
});
const regionOptions = ref([]);
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

onMounted(() => {
  getList();
  getRegionOptions();
});

const getInit = () => {
  dataListLoading.value = true;
  dataList.value = [];
  getList();
};

const getList = () => {
  http.post('/address/getList').then(res => {
    dataListLoading.value = false;
    dataItemsLoading.value = false;
    dataList.value = res.data || [];
  });
};

const getRegionOptions = () => {
  http.post('/common/getRegionOptions').then(res => {
    regionOptions.value = res.data;
  });
};

const openEdit = (row) => {
  if (row) {
    isEdit.value = true;
    editId.value = row.id;
    form.name = row.name;
    form.phone = row.phone;
    form.region = [row.province_id, row.city_id, row.district_id];
    form.detailed_address = row.detailed_address;
    form.default = row.default == 1;
  } else {
    isEdit.value = false;
    editId.value = null;
    form.name = '';
    form.phone = '';
    form.region = [];
    form.detailed_address = '';
    form.default = false;
  }
  dialogVisible.value = true;
};

const submitAddress = () => {
  if (!formRef.value) return;
  formRef.value.validate((valid) => {
    if (!valid) return;
    let loading = ElLoading.service({ lock: true, background: 'rgba(0, 0, 0, 0.7)' });
    let apiParams = {
      name: form.name,
      phone: form.phone,
      province_id: form.region[0],
      city_id: form.region[1],
      district_id: form.region[2],
      detailed_address: form.detailed_address,
      default: form.default ? 1 : 0,
    };
    let url = isEdit.value ? '/address/update' : '/address/store';
    if (isEdit.value) apiParams.id = editId.value;
    http.post(url, apiParams).then(res => {
      if (res.code == 200) {
        ElMessage.success(isEdit.value ? '地址修改成功' : '地址添加成功');
        dialogVisible.value = false;
        formRef.value.resetFields();
        getList();
      } else {
        ElMessage.error(res.message || (isEdit.value ? '地址修改失败' : '地址添加失败'));
      }
    }).finally(() => {
      loading.close();
    });
  });
};

const deleteAddress = (id) => {
  ElMessageBox.confirm('确定要删除该地址吗？').then(() => {
    let loading = ElLoading.service({ lock: true, background: 'rgba(0, 0, 0, 0.7)' });
    http.post('/address/delete', { id }).then(res => {
      if (res.code == 200) {
        let index = dataList.value.findIndex(item => item.id === id);
        if (index > -1) {
          dataList.value.splice(index, 1);
        }
        ElMessage.success('删除成功');
      } else {
        ElMessage.error(res.message || '删除失败');
      }
    }).finally(() => {
      loading.close();
    });
  }).catch(() => {});
};
</script>

<style scoped>
</style>

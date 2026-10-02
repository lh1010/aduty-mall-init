import config from './config';
const baseUrl = config.url;
const client = config.client;
const version = config.version;

const getDefaultHeader = () => ({
  'client': client,
  'version': version,
  'token': uni.getStorageSync('token') || '',
});

function request(options) {
  const {path, method = 'get', data = {}, header = {}} = options;
  const fullUrl = baseUrl + path;
  return new Promise((resolve, reject) => {
    uni.request({
      url: fullUrl,
      method,
      data,
      header: { ...getDefaultHeader(), ...header },
      success: (res) => {
        if (res.statusCode !== 200) {
          uni.showToast({ title: '请求失败 ' + res.statusCode, icon: 'none' });
          return reject(res);
        }
				const resData = res.data;
				if (resData.code == 200) {
					resolve(resData);
				} else if (resData.code == 401) {
					uni.showToast({ title: '登录已失效，请重新登录', icon: 'none' });
					reject(resData);
				} else {
					resolve(resData);
				}
      },
      fail: (err) => {
        uni.showToast({ title: '网络错误', icon: 'none' });
        reject(err);
      },
    });
  });
}

function upload(path, filePath, formData = {}, header = {}) {
  const fullUrl = baseUrl + path;
  return new Promise((resolve, reject) => {
    uni.uploadFile({
      url: fullUrl,
      filePath: filePath,
      name: 'file',
      formData: formData,
      header: { ...getDefaultHeader(), ...header },
      success: (res) => {
        if (res.statusCode !== 200) {
          uni.showToast({ title: '请求失败 ' + res.statusCode, icon: 'none' });
          return reject(res);
        }
				const resData = typeof res.data === 'string' ? JSON.parse(res.data) : res.data;
				if (resData.code == 200) {
					resolve(resData);
				} else if (resData.code == 401) {
          uni.showToast({ title: '登录已失效，请重新登录', icon: 'none' });
          reject(resData);
        } else {
					resolve(resData);
        }
      },
      fail: (err) => {
        uni.showToast({ title: '网络错误', icon: 'none' });
        reject(err);
      },
    });
  });
}

const http = {
  get(path, data = {}, options = {}) {
    return request({ path, method: 'GET', data, ...options });
  },

  post(path, data = {}, options = {}) {
    return request({ path, method: 'POST', data, ...options });
  },

  put(path, data = {}, options = {}) {
    return request({ path, method: 'PUT', data, ...options });
  },

  del(path, data = {}, options = {}) {
    return request({ path, method: 'DELETE', data, ...options });
  },

  upload: upload,
};

export default http;

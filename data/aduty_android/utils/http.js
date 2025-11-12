import config from "./config.js";
let api_url = config.api_url;

function request(url, method, params, requestParams) {
	var header = {
		"content-type": "application/x-www-form-urlencoded",
	};
  var responseType = requestParams.responseType != undefined ? requestParams.responseType : 'txt';
	var url = api_url + url;
	var params = params ? params : {};
	params['user_token'] = uni.getStorageSync('user_token');
	params['request_client'] = config.app_client;

	var promise = new Promise((resolve, reject) => {
		uni.request({
			url: url,
			method: method,
			data: params,
			header: header,
      responseType: responseType,
			success: function(res) {
				if (res.data.code == 401) {
					// 唤起登录
					uni.showToast({ title: '登录已失效，请重新登录', icon: 'none' });
					return false;
				} else if (res.data.code == 500) {
					uni.showToast({ title: '服务异常', icon: 'none' });
					return false;
				} else {
					resolve(res.data);
				}
			},
			fail: function(e) {
				uni.showToast({ title: '网络错误', icon: 'none' });
				return false;
			},
		});
	});

	return promise;
}

function upload(filePath, formData) {
	var url = api_url + '/upload';
	var formData = formData ? formData : {};
	formData['user_token'] = uni.getStorageSync('user_token');

	var promise = new Promise((resolve, reject) => {
		uni.uploadFile({
			url: url,
			filePath: filePath,
			name: 'file',
			formData: formData,
			success: function(res) {
				var res = JSON.parse(res.data);
				if (res.code == 401) {
					uni.showToast({ title: '登录已失效，请重新登录', icon: 'none' });
					return false;
				} else if (res.code == 500) {
					uni.showToast({ title: '服务异常', icon: 'none' });
					return false;
				} else {
					resolve(res);
				}
			},
			fail: function(e) {
				uni.showToast({ title: '网络错误', icon: 'none' });
				return false;
			},
		});
	});

	return promise;
}

['options', 'get', 'post', 'put', 'head', 'delete', 'trace', 'connect'].forEach((method) => {
  request[method] = (url, data, opt) => request(url, method, data, opt || {})
});

module.exports = {
	request: request,
  upload: upload
};

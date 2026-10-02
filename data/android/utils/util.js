/**
 * 判断当前是否为 hash 路由模式
 */
function isHashMode() {
  return typeof window !== 'undefined' && window.location.hash.startsWith('#/');
}

/**
 * 将参数同步到浏览器地址栏 不刷新页面
 * @param params
 * @param excludeKeys 需要排除的字段数组，这些字段不会添加到 URL 中
 */
function updateUrl(params = null, excludeKeys = []) {
  if (typeof window === 'undefined') return;

  if (isHashMode()) {
    // 兼容 hash 模式
    let hash = window.location.hash;
    let hashQuestionIndex = hash.indexOf('?');
    let hashPath = hashQuestionIndex > -1 ? hash.substring(0, hashQuestionIndex) : hash;
    let hashSearch = hashQuestionIndex > -1 ? hash.substring(hashQuestionIndex + 1) : '';
    let searchParams = new URLSearchParams(hashSearch);
    for (let key in params) {
      if (excludeKeys.includes(key)) {
        continue;
      }
      if (params[key] != '' && params[key] != undefined && params[key] != null) {
        searchParams.set(key, params[key]);
      } else {
        searchParams.delete(key);
      }
    }
    let newHash = searchParams.toString() ? `${hashPath}?${searchParams.toString()}` : hashPath;
    let url = new URL(window.location.href);
    url.hash = newHash;
    history.replaceState({}, '', url.toString());
  } else {
    // 兼容 hash 模式
    let url = new URL(window.location.href);
    let searchParams = new URLSearchParams(url.search);
    for (let key in params) {
      if (excludeKeys.includes(key)) {
        continue;
      }
      if (params[key] != '' && params[key] != undefined && params[key] != null) {
        searchParams.set(key, params[key]);
      } else {
        searchParams.delete(key);
      }
    }
    url.search = searchParams.toString();
    history.replaceState({}, '', url.toString());
  }
}

// 获取当前页面 URL 中的指定参数值
function getUrlParam(name) {
  if (typeof window === 'undefined') return '';
  if (isHashMode()) {
    // 兼容 hash 模式
    let hash = window.location.hash;
    let hashQuestionIndex = hash.indexOf('?');
    if (hashQuestionIndex === -1) return '';
    let hashSearch = hash.substring(hashQuestionIndex + 1);
    let searchParams = new URLSearchParams(hashSearch);
    return searchParams.get(name) || '';
  }
  let searchParams = new URLSearchParams(window.location.search);
  return searchParams.get(name) || '';
}

// 判断是否为微信浏览器
function isWx() {
	return false;
  let ua = navigator.userAgent.toLowerCase();
  if (ua.match(/MicroMessenger/i) == "micromessenger") {
    return true;
  } else {
    return false;
  }
}

export default {
  updateUrl,
  getUrlParam,
	isWx,
};

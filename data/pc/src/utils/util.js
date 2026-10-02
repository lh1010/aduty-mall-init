/**
 * 将参数同步到浏览器地址栏 不刷新页面
 * @param params
 * @param excludeKeys 需要排除的字段数组，这些字段不会添加到 URL 中
 */
function updateUrl(params = null, excludeKeys = []) {
  if (typeof window === 'undefined') return;
  let url = new URL(window.location.href);
  let searchParams = new URLSearchParams(url.search);
  for (let key in params) {
    // 如果在排除列表中，跳过该字段
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

// 获取当前页面 URL 中的指定参数值
function getUrlParam(name) {
  if (typeof window === 'undefined') return '';
  let searchParams = new URLSearchParams(window.location.search);
  return searchParams.get(name) || '';
}

export default {
  updateUrl,
  getUrlParam,
};

/**
 * 时间戳转日期格式
 * format 1=Y-m-d 2=Y-m-d H:i:s
 */
function timeToDate(timestamp, format = 1) {
  var date = timestamp ? new Date(timestamp) : new Date();
  var year = date.getFullYear();
  var month = date.getMonth() + 1;
  var day = date.getDate();
  var hour = date.getHours();
  var minute = date.getMinutes();
  var second = date.getSeconds();
  if (month < 10) month = '0' + month;
  if (day < 10) day = '0' + day;
  if (hour < 10) hour = '0' + hour;
  if (minute < 10) minute = '0' + minute;
  if (second < 10) second = '0' + second;
  var res = '';
  if (format == 1) {
    res = year + '-' + month + '-' + day;
  }
  if (format == 2) {
    res = year + '-' + month + '-' + day + ' ' + hour + ':' + minute + ':' + second;
  }
  return res;
}

function urlToObj(url) {
  let obj = {}
  let str = url.slice(url.indexOf('?') + 1)
  let arr = str.split('&')
  for (let j = arr.length, i = 0; i < j; i++) {
    let arr_temp = arr[i].split('=')
    obj[arr_temp[0]] = arr_temp[1]
  }
  return obj;
}

// 验证对象是否为空
function isEmptyObject(object) {
  return JSON.stringify(object) == '{}' ? true : false;
}

// 判断是否为微信浏览器
function is_wx() {
	return false;
  let ua = navigator.userAgent.toLowerCase();
  if (ua.match(/MicroMessenger/i) == "micromessenger") {
    return true;
  } else {
    return false;
  }
}

// 判断是否为电脑端
function is_pc() {
  var userAgentInfo = navigator.userAgent;
  var Agents = ["Android", "iPhone", "SymbianOS", "Windows Phone", "iPad", "iPod"];
  var flag = true;
  for (var v = 0; v < Agents.length; v++) {
    if (userAgentInfo.indexOf(Agents[v]) > 0) {
      flag = false;
      break;
    }
  }
  return flag;
}

module.exports = {
  isEmptyObject,
  timeToDate,
  urlToObj,
  is_wx,
  is_pc
}

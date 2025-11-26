$(document).ready(function(){
  getCartCount();
});

function getCartCount() {
  $.ajax({
    url: '/api/product/getCartCount',
    type: 'post',
    data: {
      user_token: $('#user_token').val()
    },
    success: function(res) {
      $('#head_top_cart span').html(res.data);
    }
  })
}

$("img.lazy").lazyload();

function logout() {
  var load = layer.load();
	$.ajax({
		url: '/api/account/logout',
    type: 'post',
    data: {
      user_token: $('#user_token').val()
    },
		success: function(res) {
      layer.close(load);
			layer.msg('已安全退出...', {time: 1500}, function() { 
				window.location.reload(); 
			});
		}
	})
}

function goLogin() {
	layer.msg('请先登录...', {time: 1500}, function() {
		window.location.href = '/login_password';
	})
}

function layerOpen(url = '', title = '信息', width = '90%', height = '90%') {
  layer.open({
    type: 2,
    title: title,
    area: [width, height],
    maxmin: false,
    content: url,
  });
}

function showImage(img_url) {
  var img = new Image();
  img.src = img_url;
  if (img.complete) {
    var img_width = img.width;
    var img_height = img.height;
  } else {
    img.onload = function() {
      var img_width = img.width;
      var img_height = img.height;
    }
  }
  var img_scale = img_width / img_height;
  var width = img_width; height = img_height;

  var max_width = document.documentElement.clientWidth * 0.8;
  var max_height = document.documentElement.clientHeight * 0.8;

  if (img_width > max_width) {
    width = max_width;
    height = width / img_scale;
  }
  if (height > max_height) {
    height = max_height
    width = height * img_scale;
  }

  var str = '';
  str += '<div class="image_popup" style="width: ' + width + 'px; height: ' + height + 'px; line-height: ' + height + 'px; text-align: center; overflow: hidden;">';
  str += '<img src="' + img_url + '" />';
  str += '</div>';

  layer.open({
    type: 1,
    title: false,
    area:['auto'],
    closeBtn: 0,
    shadeClose: true,
    skin: 'image_popup_addition_class',
    content: str
  });
}

function getUrlParam(param) {
  var sPageURL = window.location.search.substring(1),
    sURLVariables = sPageURL.split('&'),
    sParamName,
    i;
  for (i = 0; i < sURLVariables.length; i++) {
    sParamName = sURLVariables[i].split('=');
    if (sParamName[0] === param) {
      return sParamName[1] === undefined ? true : decodeURIComponent(sParamName[1]);
    }
  }
};

function removeUrlParam(params = []) {
  var url = window.location.href.split('?')[0] + '?';
  
  var sPageURL = decodeURIComponent(window.location.search.substring(1));
  var sURLVariables = sPageURL != '' ? sPageURL.split('&') : [];
  var sParameterName;
  var i;

  for (i = 0; i < sURLVariables.length; i++) {
    sParameterName = sURLVariables[i].split('=');
    if (!in_array(sParameterName[0], params)) {
      url = url + sParameterName[0] + '=' + sParameterName[1] + '&'
    }
  }

  return url.substring(0, url.length - 1);
}

function setUrlParam(param, value) {
  var url = getUrlParam(param) != undefined ? removeUrlParam(param) : window.location.href;
  var reg = /\?/;
  reg.test(url) ? url += '&' + param + '=' + value : url += '?' + param + '=' + value;
  return url;
}

function in_array(value, array) {
  for (var i = 0; i < array.length; i++) {
    if (value === array[i]) {
      return true;
    }
  }
  return false;
}

function setPostSearchUrl(key, value) {
  var url = removeUrlParam(['page', key]);
  if (value != '') {
    var reg = /\?/;
    reg.test(url) ? url += '&' + key + '=' + value : url += '?' + key + '=' + value;
  }
  window.location.href = url;
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

function oneKeyBuy(sku) {
  window.location.href = '/checkout/onekeybuy?sku=' + sku;
}

function addCart(sku) {
  var load = layer.load();
  $.ajax({
    url: '/api/product/addCart',
    type: 'post',
    data: {
      user_token: $('#user_token').val(),
      sku: sku,
    },
    success: function(data) {
      layer.close(load);
      if (data.code == 401) {
        goLogin();
        return false;
      } else if (data.code == 200) {
        getCartCount();
        layer.msg('成功添加购物车');
        return false;
      } else if (data.code == 400) {
        layer.msg(data.message);
        return false;
      }
    }
  })
}

function collectProduct(sku) {
  var load = layer.load();
  $.ajax({
    url: '/api/product/collect',
    type: 'post',
    data: {
      user_token: $('#user_token').val(),
      sku: sku,
    },
    success: function(res) {
      layer.close(load);
      if (res.code == 200) {
        location.reload();
      } else if (res.code == 400) {
        layer.msg(res.message);
      } else if (res.code == 401) {
        goLogin();
      } else {
        layer.msg('操作失败');
      }
    }
  })
}

function deleteCollectProduct(sku) {
  var layer_confirm =  layer.confirm('确认取消？', function() {
    layer.close(layer_confirm);
    var load = layer.load();
    $.ajax({
      url: '/api/product/collect',
      type: 'post',
      data: {
        user_token: $('#user_token').val(),
        sku: sku,
      },
      success: function(res) {
        layer.close(load);
        if (res.code == 200) {
          location.reload();
        } else if (res.code == 400) {
          layer.msg(res.message);
        } else if (res.code == 401) {
          goLogin();
        } else {
          layer.msg('操作失败');
        }
      }
    })
  })
}

function collectShop(id) {
  var load = layer.load();
  $.ajax({
    url: '/api/shop/collect',
    type: 'post',
    data: {
      user_token: $('#user_token').val(),
      id: id,
    },
    success: function(res) {
      layer.close(load);
      if (res.code == 200) {
        location.reload();
      } else if (res.code == 400) {
        layer.msg(res.message);
      } else if (res.code == 401) {
        goLogin();
      } else {
        layer.msg('操作失败');
      }
    }
  })
}

function deleteCollectShop(id) {
  var layer_confirm =  layer.confirm('确认取消？', function() {
    layer.close(layer_confirm);
    var load = layer.load();
    $.ajax({
      url: '/api/shop/deleteCollect',
      type: 'post',
      data: {
        user_token: $('#user_token').val(),
        id: id,
      },
      success: function(res) {
        layer.close(load);
        if (res.code == 200) {
          location.reload();
        } else if (res.code == 400) {
          layer.msg(res.message);
        } else if (res.code == 401) {
          goLogin();
        } else {
          layer.msg('操作失败');
        }
      }
    })
  })
}

function createOrder(type) {
  var data = {
    user_token: $('#user_token').val(),
    type: type,
  };
  if (type == 'onekeybuy') {
    data.sku = $('input[name="sku"]').val();
  }
  var address_id = '';
  address_id = $('.address_items .address_item.on').data('id');
  data.address_id = address_id;
  var load = layer.load();
  $.ajax({
    url: '/api/order/createOrder',
    type: 'post',
    data,
    success: function(res) {
      layer.close(load);
      if (res.code == 401) {
        goLogin();
        return false;
      } else if (res.code == 200) {
        window.location.href = '/checkout/pay?order_ids=' + res.data.order_ids;
      } else if (res.code == 400) {
        layer.msg(res.message);
        return false;
      } else {
        layer.msg('操作失败');
        return false;
      }
    }
  })
}

function getContact_user(user_id) {
  var load = layer.load();
  $.ajax({
    url: '/api/user/getContact',
    type: 'post',
    data: {
      user_token: $('#user_token').val(),
      user_id: user_id,
    },
    success: function(res) {
      layer.close(load);
      if (res.code == 200) {
        var str = '';
        str += '<p>微信：' + ( res.data.weixin ? res.data.weixin : '未填写' ) + '</p>';
        str += '<p>手机：' + ( res.data.phone ? res.data.phone : '未填写' ) + '</p>';
        str += '<p>Q Q：' + ( res.data.qq ? res.data.qq : '未填写' ) + '</p>';
        str += '<p>电话：' + ( res.data.telphone ? res.data.telphone : '未填写' ) + '</p>';
        layer.open({
          title: '用户联系方式',
          closeBtn: 0,
          content: str, 
          btn: ['我知道了'],
          yes: function(index, layero){
            layer.close(index);
          }
        });
      } else if (res.code == 400) {
        layer.msg(res.message);
      } else if (res.code == 401) {
        goLogin();
      } else {
        layer.msg('操作失败');
      }
    }
  })
}

function getContact_shop(shop_id) {
  var load = layer.load();
  $.ajax({
    url: '/api/shop/getContact',
    type: 'post',
    data: {
      user_token: $('#user_token').val(),
      shop_id: shop_id,
    },
    success: function(res) {
      layer.close(load);
      if (res.code == 200) {
        var str = '';
        str += '<p>微信：' + ( res.data.weixin ? res.data.weixin : '未填写' ) + '</p>';
        str += '<p>手机：' + ( res.data.phone ? res.data.phone : '未填写' ) + '</p>';
        str += '<p>Q Q：' + ( res.data.qq ? res.data.qq : '未填写' ) + '</p>';
        str += '<p>电话：' + ( res.data.telphone ? res.data.telphone : '未填写' ) + '</p>';
        layer.open({
          title: '店铺联系方式',
          closeBtn: 0,
          content: str, 
          btn: ['我知道了'],
          yes: function(index, layero){
            layer.close(index);
          }
        });
      } else if (res.code == 400) {
        layer.msg(res.message);
      } else if (res.code == 401) {
        goLogin();
      } else {
        layer.msg('操作失败');
      }
    }
  })
}

$('#topsearch .search_menu li').click(function() {
  $(this).addClass('on').siblings().removeClass('on');
})

$('#topsearch .search_btn').click(function() {
  var type = $('#topsearch .search_menu li.on').data('type');
  var k = $('#topsearch .k').val();
  var url = '';
  if (type == 1) {
    url = '/product/list?stype=1&k=' + k;
  }
  if (type == 2) {
    url = '/shop/list?stype=2&k=' + k;
  }
  if (type == 3) {
    url = '/task/list?stype=3&k=' + k;
  }
  window.location.href = url;
})
function sendCode() {
	if ($('#phone').val() == '') {
		layer.msg('手机号不能为空');
		return false;
	}
	var load = layer.load();
	$.ajax({
		url: '/api/account/sendCode',
		type: 'post',
		data: {
			phone: $('#phone').val(),
		},
		success: function(res) {
			layer.close(load);
			if (res.code == 200) {
				afreshSend();
				layer.msg('已发送，请注意接收');
			} else if (res.code == 400) {
				layer.msg(res.message);
			} else {
				layer.msg('操作失败');
			}
		}
	})
}

var wait = 60;
function afreshSend(type = 'phone') {
	var functionStr = 'sendEmailCode();';
	if (type == 'phone') {
		functionStr = 'sendCode();';
	}

	if (wait == 0) {
		$("#send_code a").html('获取验证码');
		$("#send_code a").attr('onclick', functionStr);
		wait = 60;
	} else {
		$("#send_code a").removeAttr("onclick");
		$("#send_code a").html('重新发送 ' + wait);
		wait--;
		setTimeout(function() {afreshSend()}, 1000);
	}
}

// 取消订单
function cancelOrder(order_id) {
  var confirm_str = '确认取消订单？';
  layer.confirm(confirm_str, function() {
    layer.closeAll();
    var load = layer.load();
    $.ajax({
      url: '/api/order/cancelOrder',
      type: 'post',
      data: {
        user_token: $('#user_token').val(),
        order_id: order_id,
      },
      success: function(res) {
        layer.close(load);
        if (res.code == 200) {
          layer.msg('操作成功', {time: 1500}, function() {
            window.location.reload();
          });
        } else if (res.code == 400) {
          layer.alert(res.message);
        } else if (res.code == 401) {
          goLogin();
        } else {
          layer.alert('操作失败');
        }
      }
    })
  });
}

// 确认收货
function receiveOrder(order_id) {
  var confirm_str = '确认收货？';
  layer.confirm(confirm_str, function() {
    layer.closeAll();
    var load = layer.load();
    $.ajax({
      url: '/api/order/receiveOrder',
      type: 'post',
      data: {
        user_token: $('#user_token').val(),
        order_id: order_id
      },
      success: function(res) {
        layer.close(load);
        if (res.code == 200) {
          layer.msg('操作成功', {time: 1500}, function() {
            window.location.reload();
          });
        } else if (res.code == 400) {
          layer.alert(res.message);
        } else if (res.code == 401) {
          goLogin();
        } else {
          layer.alert('操作失败');
        }
      }
    })
  });
}

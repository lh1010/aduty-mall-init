// 取消订单
function set_order_status_m10(order_id) {
  var confirm_str = '确认取消订单？';
  layer.confirm(confirm_str, function() {
    layer.closeAll();
    var load = layer.load();
    $.ajax({
      url: '/api/shop/set_order_status',
      type: 'post',
      data: {
        user_token: $('#user_token').val(),
        order_id: order_id,
        status: -10
      },
      success: function(res) {
        layer.close(load);
        if (res.code == 200) {
          layer.msg('操作成功', {time: 1500}, function() { 
            window.location.reload(); 
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
  });
}

// 确认发货
function set_order_status_30(order_id) {
  var confirm_str = '确认发货？';
  layer.confirm(confirm_str, function() {
    layer.closeAll();
    var load = layer.load();
    $.ajax({
      url: '/api/shop/set_order_status',
      type: 'post',
      data: {
        user_token: $('#user_token').val(),
        order_id: order_id,
        status: 30
      },
      success: function(res) {
        layer.close(load);
        if (res.code == 200) {
          layer.msg('操作成功', {time: 1500}, function() { 
            window.location.reload(); 
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
  });
}
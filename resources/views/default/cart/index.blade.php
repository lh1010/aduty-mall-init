@extends(Config('common.view.tpl_folder') . '.shared._layout')
@section('title', '购物车 ' . Config('common.pc.app_name'))
@section('keywords', '购物车 ' . Config('common.pc.app_name'))
@section('description', '购物车 ' . Config('common.pc.app_name'))
@section('pagecss')
<link rel="stylesheet" type="text/css" href="/static/default/style/cart.css?v={{Config('common.pc.version')}}" />
@endsection
@section('content')
<div class="checkout">
  <div class="container">
    <div class="nav">
      <span class="on">当前位置：</span><a href="/">首页</a><span>></span><span class="on">购物车</span>
    </div>
    @if(!empty($products))
    <div class="cart">
      <table class="items">
        <thead>
          <tr class="items_top">
            <th class="t0"><i class="check check_all iconfont @if($totalData['all_selected'] == 1) on @endif" data-ident='all'></i></th>
            <th class="t1">产品信息</th>
            <th class="t2">单价</th>
            <th class="t3">数量</th>
            <th class="t4">小计</th>
            <th class="t5">操作</th>
          </tr>
        </thead>
      </table>
      <table class="items items_shop">
        <thead>
          <tr class="items_top"></tr>
        </thead>
        <tbody>
          @foreach($products as $key => $value)
          <tr class="item">
            <td class="t0"><i class="check check_product iconfont @if($value['selected'] == 1) on @endif" data-ident='product' data-id='{{$value["id"]}}' data-sku='{{$value["sku"]}}'></i></td>
            <td class="t1 proinfo">
              <div class="cover"><img class="lazy" data-original="{{$value['cover']}}" src="{{Config('common.image.lazy_default')}}"></div>
              <div class="infobox">
                <div class="name"><a href="/product/show/{{$value['sku']}}" target="_blank">{{$value['name']}}</a></div>
                @if(!empty($value['specifications']))
                <div class="types">
                  @foreach($value['specifications'] as $key_specn => $value_specn)
                  <div class="types_item">{{$value_specn['specification_name']}}-{{$value_specn['specification_option']}}</div>
                  @endforeach
                </div>
                @endif
              </div>
            </td>
            <td class="t2 text-price">¥{{$value['price']}}</td>
            <td class="t3">x{{$value['count']}}</td>
            <td class="t4 text-price">¥{{$value['total_price']}}</td>
            <td class="t5 actions"><a href="javascript:void(0);" onclick="deleteCart('{{$value["sku"]}}');">删除</a></td>
          </div>
          @endforeach
        </tr>
      </table>
      <div class="cart_foot">
        <div class="cfmain">
          <div class="pricebox">
            <span class="s1">合计：</span>
            <span class="s2 text-price">¥</span>
            <span class="s3 text-price">{{$totalData['total_price']}}</span>
          </div>
          <div class="btn"><a type="button" class="layui-btn layui-btn-danger" href="javascript:goCheckout();">去结算</a></div>
        </div>
      </div>
    </div>
    @else
    <div class="cart_empty">
      <img src="/static/default/images/cart.png">
      <div class="info">
        <p>购物车还是空空的呢，快去看看心仪的商品吧~</p>
        <a href="/">去购买 ></a>
      </div>
    </div>
    @endif
  </div>
</div>
@endsection
@section('pagejs')
<script type="text/javascript">
function goCheckout() {
  if (getCartProductIds() == '') {
    layer.msg('请选择商品');
    return false;
  }
  window.location.href = '/checkout/cart';
}

$(document).ready(function() {
  $('.cart .check').click(function() {
    var ident = $(this).data('ident');
    var selected = $(this).hasClass('on');
    if (selected == true) {
      if (ident == 'product') {
        $(this).removeClass('on');
      }
      if (ident == 'shop') {
        $(this).parents('.items').find('.check').removeClass('on');
      }
      if (ident == 'all') {
        $(this).parents('.cart').find('.check').removeClass('on');
      }
    } else {
      if (ident == 'product') {
        $(this).addClass('on');
      }
      if (ident == 'shop') {
        $(this).parents('.items').find('.check').addClass('on');
      }
      if (ident == 'all') {
        $(this).parents('.cart').find('.check').addClass('on');
      }
    }
    checkSelected();
    setCartSelected();
  })
})

function checkSelected() {
  var i1 = 1;
  $('.cart .items_shop').each(function() {
    var i2 = 1;
    $(this).find('.check_product').each(function() {
      if ($(this).hasClass('on') == false) {
        i2 = 0;
        i1 = 0;
      }
    })
    if (i2 == 0) {
      $(this).find('.check_shop').removeClass('on');
    } else {
      $(this).find('.check_shop').addClass('on');
    }
  })

  if (i1 == 0) {
    $('.check_all').removeClass('on');
  } else {
    $('.check_all').addClass('on');
  }
}

function getCartProductIds() {
  var product_ids = '';
  $('.cart .check_product').each(function() {
    if ($(this).hasClass('on') == true) {
      if ($(this).data('id') != undefined) {
        product_ids += $(this).data('id') + ',';
      }
    }
  })
  if (product_ids != '') product_ids = product_ids.substr(0, product_ids.length - 1);
  return product_ids;
}

function getSkus() {
  var skus = '';
  $('.cart .check_product').each(function() {
    if ($(this).hasClass('on') == true) {
      if ($(this).data('sku') != undefined) {
        skus += $(this).data('sku') + ',';
      }
    }
  })
  if (skus != '') skus = skus.substr(0, skus.length - 1);
  return skus;
}

function setCartSelected(type, product_id = '') {
  var skus = getSkus();
  var data = {
    user_token: $('#user_token').val(),
    skus: skus,
  };
  var load = layer.load();
  $.ajax({
    url: '/api/product/setCartSelected',
    type: 'post',
    data,
    success: function(data) {
      layer.close(load);
      if (data.code == 200) {
        window.location.reload();
        return false;
      } else if (data.code == 400) {
        layer.msg(data.message);
        return false;
      } else if (data.code == 401) {
        goLogin();
        return false;
      }
    }
  })
}

function deleteCart(sku) {
  layer.confirm('确认删除？', function() {
    layer.closeAll();
    var load = layer.load();
    $.ajax({
      url: '/api/product/deleteCart',
      type: 'post',
      data: {
        skus: sku,
        user_token: $('#user_token').val(),
      },
      success: function(res) {
        layer.close(load);
        if (res.code == 200) {
          window.location.reload();
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
</script>
@endsection

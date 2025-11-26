@extends(Config('common.view.tpl_folder') . '.shared._layout')
@section('title', '确认订单 ' . Config('common.pc.app_name'))
@section('keywords', '确认订单 ' . Config('common.pc.app_name'))
@section('description', '确认订单 ' . Config('common.pc.app_name'))
@section('pagecss')
<link rel="stylesheet" type="text/css" href="/static/default/style/checkout.css?v={{Config('common.pc.version')}}" />
@endsection
@section('content')
<input type="hidden" name="sku" value="{{Request()->sku}}">
<div class="checkout okb">
  <div class="container">
    <div class="nav">
      <span class="on">当前位置：</span><a href="/">首页</a><span>></span><span class="on">确认订单</span>
    </div>
    <div class="cpros">
      <div class="addressbox">
        <div class="stitle">地址选择</div>
        <div class="address_items">
          @foreach($checkoutData['addresses'] as $key => $value)
          <div class="address_item {{ $value['default'] == 1 ? 'on' :'' }}" data-id="{{$value['id']}}">
            <div class="address-box-info" title="{{$value['name']}} {{$value['province_name']}} {{$value['city_name']}} {{$value['district_name']}}">
              <div class="name">{{$value['name']}}</div>
              <div class="detaile">{{$value['province_name']}} {{$value['city_name']}} {{$value['district_name']}}</div>
              <div class="detaile">{{$value['detailed_address']}}</div>
              <div class="number-phone">{{$value['phone']}}</div>
            </div>
          </div>
          @endforeach
        </div>
        <div class="add-btn" onclick="layerOpen('/address/popupCreate', '创建地址', '530px', '565px')"><i class="iconfont">&#xe727;</i> 使用新地址</div>
      </div>
      <div class="probox">
        <div class="stitle">商品清单</div>
        <table class="items">
          <thead>
            <tr class="items_top">
              <th class="t1">商品</th>
              <th class="t2">单价</th>
              <th class="t3">数量</th>
              <th class="t4">小计</th>
            </tr>
          </thead>
          <tbody>
            @foreach($checkoutData['products'] as $key => $value)
            <tr class="item">
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
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="cpros_foot">
        <div class="cfmain">
          <div class="pricebox">
            <span class="s1">合计：</span>
            <span class="s2 text-price">¥</span>
            <span class="s3 text-price">{{$checkoutData['totalData']['total_price']}}</span>
          </div>
          <div class="btn"><button type="button" class="layui-btn layui-btn-danger" onclick="createOrder('onekeybuy');">提交订单</button></div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
@section('pagejs')
<script type="text/javascript">
$(document).ready(function() {
  $('.address_items .address_item').click(function() {
    $(this).addClass('on').siblings().removeClass('on');;
  })
})
</script>
@endsection
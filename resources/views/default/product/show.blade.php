@extends(Config('common.view.tpl_folder') . '.shared._layout')
@section('title', $product->name . ' 商品详情 ' . $product->category_name . ' ' . Config('common.pc.app_name'))
@section('keywords', $product->name . ' 商品详情 ' . $product->category_name . ' ' . Config('common.pc.app_name'))
@section('description', $product->name . ' 商品详情 ' . $product->category_name . ' ' . Config('common.pc.app_name'))
@section('pagecss')
<link rel="stylesheet" type="text/css" href="/static/default/style/product.css?v={{Config('common.pc.version')}}" />
@endsection
@section('content')
<div class="proshow">
  <div class="container">
    <div class="nav">
      <span class="on">当前位置：</span><a href="/">首页</a><span>></span><a href="/product/list?cid={{$product->category_id}}">{{$product->category_name}}</a><span>></span><span class="on">详情</span>
    </div>
    <div class="left">
      <div class="left_top">
        <div class="lt_left">
          <div class="large-image"><img class="lazy" data-original="{{$product->sku->cover}}" src="{{Config('common.image.lazy')}}"></div>
          @if(!empty($product->images))
          <div class="small-images">
            @foreach($product->images as $value)
            <img class="lazy img" data-original="{{$value->image}}" src="{{Config('common.image.lazy')}}" />
            @endforeach
          </div>
          @endif
          <div class="actions">
            @if($product->collect_status != 1)
            <a href="javascript:void(0);" class="a1 iconfont" data-sku="{{$product->sku->sku}}" onclick="collectProduct('{{$product->sku->sku}}');">收藏</a>
            @else
            <a href="javascript:void(0);" class="a1 on iconfont" onclick="deleteCollectProduct('{{$product->sku->sku}}');">已收藏</a>
            @endif
            <a href="/article/show?type=contact" target="_blank" class="a2 iconfont">举报</a>
          </div>
        </div>
        <div class="lt_right">
          <div class="title">{{$product->name}}</div>
          <ul class="ul1 clearfix">
            <li class="li2 iconfont" title="商品分类">{{$product->full_category_name}}</li>
          </ul>
          <div class="price clearfix">
            <div class="sale_price text-price">
              <span class="span1">￥</span>
              <span class="span2">{{$product->sku->price}}</span>
            </div>
          </div>
          <div class="dl">
            <div class="dt">运费</div>
            <div class="dd">免运费</div>
          </div>
          @if(!empty($product->specifications))
          @foreach($product->specifications as $key => $value)
          <div class="dl specs">
            <div class="dt">{{$value['specification_name']}}</div>
            <div class="dd">
              <div class="specs_items">
                @foreach($value['options'] as $key_option => $value_option)
                <div class="specs_item {{ isset($value_option['selected']) && $value_option['selected'] == 1 ? 'on' : '' }} {{ $value_option['valid'] == 0 ? 'invalid' : '' }}" data-sku="{{$value_option['sku']}}" data-valid="{{$value_option['valid']}}">{{$value_option['specification_option']}}</div>
                @endforeach
              </div>
            </div>
          </div>
          @endforeach
          @endif
          <div class="btns">
            <button class="layui-btn layui-btn-luck" onclick="oneKeyBuy('{{$product->sku->sku}}');">立即购买</button>
            <button class="layui-btn layui-btn-warm" onclick="addCart('{{$product->sku->sku}}');">加入购物车</button>
          </div>
        </div>
      </div>
      <div class="left_main">
        <div class="lm_nav">
          <ul>
            <li class="on" data-key="c1">商品规格</li>
            <li data-key="c2">商品详情</li>
          </ul>
          <a class="btn" href="javascript:void(0);" onclick="oneKeyBuy({{$product->id}});">立即购买</a>
        </div>
        <div class="content">
          @if(!empty($product->attributes))
          <div class="probqm c1">
             <ul class="probq">
              @foreach($product->attributes as $value)
              <li class="l1">{{$value->attribute_name}}</li>
              <li class="l2">{{$value->attribute_value}}</li>
              @endforeach
            </ul>
          </div>
          @endif
          <div class="c_main c2">{!! $product->content !!}</div>
        </div>
      </div>
    </div>

    <div class="right">
      <div class="r_section right_prodocts">
        <div class="top"><span class="title">最新商品</span></div>
        <div class="items">
          @foreach($r1 as $value)
          <a href="/product/show/{{$value->id}}" class="item">
            <div class="cover"><img class="lazy" data-original="{{$value->cover}}" src="{{Config('common.image.lazy_default')}}"></div>
            <div class="info">
              <div class="name">{{$value->name}}</div>
              <div class="price text-price">¥ {{$value->price}}</div>
            </div>
          </a>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
@section('pagejs')
<script type="text/javascript" src="/static/default/script/product.js"></script>
<script type="text/javascript">
$(document).ready(function() {
  var targetOffset = $('.menu').offset().top;
  $('html, body').scrollTop(targetOffset);
  navLocation();
})

$(".lm_nav li").click(function() {
  $(this).addClass('on').siblings().removeClass('on');
  var top = $('.' + $(this).attr('data-key')).offset().top;
  if ($('.lm_nav').hasClass('lm_nav1')) {
    top = top - 55;
  } else {
    top = top - 110;
  }

  $("html, body").animate({scrollTop: top + 'px'}, 300);
})

var popupIndex_jubao;
function showJubaoPopup() {
  popupIndex_jubao = layer.open({
    type: 1,
    title: false,
    area:['auto'],
    closeBtn: 0,
    shadeClose: true,
    skin: 'jubao_popup_addition_class',
    content: $('.jubao_popup')
  });
}

function closeJubaoPopup() {
  layer.close(popupIndex_jubao);
}

layui.use('form', function() {
  var form = layui.form;
  form.on('submit(formSub)', function(data) {
    var load = layer.load();
    var data = data.field;
    data.user_token = $('#user_token').val();
    $.ajax({
      url: '/api/account/jubao',
      type: 'post',
      data: data,
      success: function(res) {
        layer.close(load);
        if (res.code == 200) {
          layer.msg('举报成功，等待后台核实');
          layer.close(popupIndex_jubao);

        } else if (res.code == 400) {
          layer.msg(res.message);
        } else if (res.code == 401) {
          goLogin();
        } else {
          layer.msg('操作失败');
        }
      }
    })
    return false;
  });
});
</script>
@endsection

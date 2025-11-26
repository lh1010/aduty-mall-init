@extends(Config('common.view.tpl_folder') . '.shared._layout')
@section('title', Config('common.pc.index_title'))
@section('keywords', Config('common.pc.index_keywords'))
@section('description', Config('common.pc.index_description'))
@section('pagecss')
<link rel="stylesheet" type="text/css" href="/static/default/style/product.css?v={{Config('common.pc.version')}}" />
<link rel="stylesheet" href="/static/default/plugins/Swiper/swiper8/swiper-bundle.css">
@endsection
@section('content')
@if(!empty($adver_banner))
<div class="banner">
  <div class="adver_a swiper">
    <div class="swiper-wrapper">
      @foreach($adver_banner as $value)
      <a class="swiper-slide item" href="{{$value->url}}" target="{{$value->open_mode == 1 ? '_self' : '_blank'}}"><img src="{{$value->image}}"></a>
      @endforeach
    </div>
    <div class="swiper-pagination"></div>
  </div>
</div>
@endif
@if(!empty($product_data))
@foreach($product_data as $value)
<div class="section prolist">
  <div class="container">
    <div class="top"><span class="title">{{$value->name}}</span><a class="more" href="/product/list/{{$value->id}}" target="_blank">查看更多</a></div>
    <div class="items clearfix">
      @if(!empty($value->products))
      @foreach($value->products as $value_product)
      <div class="item">
        <div class="item_box">
          <a class="cover" href="/product/show/{{$value_product->sku}}" target="_blank"><img class="lazy" data-original="{{$value_product->cover}}" src="{{Config('common.image.lazy')}}"></a>
          <div class="con">
            <div class="price text-price"><em>¥</em>{{$value_product->price}}</div>
            <a class="title" href="/product/show/{{$value_product->sku}}" target="_blank">{{$value_product->name}}</a>
          </div>
        </div>
      </div>
      @endforeach
      @else
      <div class="s_noresult">
        <p>该分类下暂无商品~</p>
      </div>
      @endif
    </div>
  </div>
</div>
@endforeach
@endif

@endsection
@section('pagejs')
<script type="text/javascript" src="/static/default/plugins/Swiper/swiper8/swiper-bundle.js"> </script>
<script type="text/javascript">
var mySwiper = new Swiper ('.swiper', {
  loop: true,
  pagination: {
    el: '.swiper-pagination',
  },
})
</script>
@endsection

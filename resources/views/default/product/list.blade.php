@extends(Config('common.view.tpl_folder') . '.shared._layout')
@section('title', ($category ? $category->seo_title : '全部商品') . ' ' . Config('common.pc.app_name'))
@section('keywords', ($category ? $category->seo_title : '全部商品') . ' ' . Config('common.pc.app_name'))
@section('description', ($category ? $category->seo_title : '全部商品') . ' ' . Config('common.pc.app_name'))
@section('pagecss')
<link rel="stylesheet" type="text/css" href="/static/default/style/product.css?v={{Config('common.pc.version')}}" />
@endsection
@section('content')
<div class="prolist">
  <div class="container">
    <div class="nav">{!! $nav !!}</div>
    <div class="condition">
      <div class="c_group">
        <div class="c_group_title">排序方式：</div>
        <div class="c_group_items">
          <a class="span_item @if(Request()->order == '') on @endif" href="javascript:void(0);" onclick="setPostSearchUrl('order', '');">默认</a><a class="span_item @if(Request()->order == 1) on @endif" href="javascript:void(0);" onclick="setPostSearchUrl('order', 1);">最新</a>
        </div>
      </div>
    </div>
    @if($products->total() > 0)
    <div class="items clearfix">
      @foreach($products as $value)
      <div class="item">
        <div class="item_box">
          <a class="cover" href="/product/show/{{$value->sku}}" target="_blank"><img class="lazy" data-original="{{$value->cover}}" src="{{Config('common.image.lazy')}}"></a>
          <div class="con">
            <div class="price text-price"><em>¥</em>{{$value->price}}</div>
            <a class="title" href="/product/show/{{$value->sku}}" target="_blank">{{$value->name}}</a>
          </div>
        </div>
      </div>
      @endforeach
    </div>
    <div class="page">{{ $products->appends(Request()->all())->render() }}</div>
    @else
    <div class="noresult">
      <img src="{{Config('common.image.noresult')}}">
      <p>暂无内容</p>
    </div>
    @endif
  </div>
</div>
@endsection

@extends(Config('common.view.tpl_folder') . '.shared._layout')
@section('title', $category->name . ' ' . Config('common.pc.app_name'))
@section('keywords', $category->name . ' ' . Config('common.pc.app_name'))
@section('description', $category->name . ' ' . Config('common.pc.app_name'))
@section('pagecss')
<link rel="stylesheet" type="text/css" href="/static/default/style/article.css?v={{Config('common.pc.version')}}" />
@endsection
@section('content')
<div class="article_list clearfix">
  <div class="container">
    <div class="nav">
      <a href="/">首页</a><span>></span><span class="on">{{$category->name}}</span>
    </div>

    <div class="article_list_left">
      @if($articles->total() > 0)
      <div class="items">
        @foreach($articles as $value)
        <div class="item">
          <div class="item_box">
            <div class="top">
              <div class="title"><a href="/article/show/{{$value->id}}" target="_blank">{{$value->title}}</a></div>
            </div>
            <ul class="type">
              <li><span>{{ Str::limit($value->created_at, 10, '') }}</span></li>
            </ul>
          </div>
        </div>
        @endforeach
      </div>
      <div class="page">{{ $articles->appends(Request()->all())->render() }}</div>
      @else
      <div class="noresult">
        <img src="{{Config('common.image.noresult')}}">
        <p>暂无内容</p>
      </div>
      @endif
    </div>

    <div class="common_right">
      <div class="cr_section adver">
        <img class="adver_left_icon" src="/static/default/images/icon_wxapp.png" />
        <div class="adver_content">
          <div class="adver_content_title">微信扫码使用小程序</div>
          <div>微信搜索商务邦小程序</div>
        </div>
        <a href="javascript:void(0);"><img class="adver_right_qrcode" src="https://lh1010.oss-cn-beijing.aliyuncs.com/swb/swb_wxapp.jpg"></a>
      </div>
      <div class="cr_section adver1">
        <a href=""><img src="/static/default/images/right_adver1.png"></a>
      </div>
    </div>
  </div>
</div>
@endsection
@section('pagejs')

@endsection

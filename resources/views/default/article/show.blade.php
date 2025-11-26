@extends(Config('common.view.tpl_folder') . '.shared._layout')
@section('title', ($article->seo_title ? $article->seo_title : $article->title) . ' ' . Config('common.pc.app_name'))
@section('keywords', ($article->seo_keywords ? $article->seo_keywords : $article->title) . ',' . Config('common.pc.app_name'))
@section('description', ($article->seo_description ? $article->seo_description : $article->title) . ',' . Config('common.pc.app_name'))
@section('pagecss')
<link rel="stylesheet" type="text/css" href="/static/default/style/article.css?v={{Config('common.pc.version')}}" />
@endsection
@section('content')
<div class="article_show clearfix">
  <div class="container">
    <div class="nav">
      <a href="/">首页</a>
      <span>></span>
      <a href="/article/list/{{$article->category_id}}">{{$article->category_name}}</a>
      <span>></span>
      <span class="on">详情</span>
    </div>

    <div class="article_show_left">
      <div class="main">
        <div class="top_page clearfix">
          <div class="title">{{$article->title}}</div>
          <ul class="type">
            <li><span>{{ Str::limit($article->created_at, 10, '') }}</span></li>
          </ul>
        </div>
        <div class="content">
          <div class="bd">{!! $article->content !!}</div>
        </div>
      </div>
    </div>

    <div class="common_right">
      <div class="cr_section adver">
        <img class="adver_left_icon" src="/static/web/images/icon_wxapp.png" />
        <div class="adver_content">
          <div class="adver_content_title">微信扫码使用小程序</div>
          <div>微信搜索商务邦小程序</div>
        </div>
        <a href="javascript:void(0);"><img class="adver_right_qrcode" src="https://lh1010.oss-cn-beijing.aliyuncs.com/swb/swb_wxapp.jpg"></a>
      </div>
      <div class="cr_section adver1">
        <a href=""><img src="/static/web/images/right_adver1.png"></a>
      </div>
    </div>
  </div>
</div>
@endsection
@section('pagejs')

@endsection

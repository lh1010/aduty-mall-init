@extends(Config('common.view.tpl_folder') . '.shared._layout')
@section('title', $article->title . ' 帮助中心 ' . Config('common.pc.app_name'))
@section('keywords', $article->title . ' 帮助中心 ' . Config('common.pc.app_name'))
@section('description', $article->title . ' 帮助中心 ' . Config('common.pc.app_name'))
@section('pagecss')
<link rel="stylesheet" type="text/css" href="/static/default/style/article.css?v={{Config('common.pc.version')}}" />
@endsection
@section('content')
<div class="help_show2">
  <div class="container">
    <div class="left">
      @foreach($categorys as $value)
      <dl>
        <dt>
          <span>{{$value->name}}</span>
          @if($article->category_id != $value->id)
          <i class="iconfont down"></i>
          @else
          <i class="iconfont up"></i>
          @endif
        </dt>
        <dd @if($article->category_id != $value->id) class="none" @endif>
          @foreach($value->articles as $value_article)
          <a class="item @if($value_article->id == Request()->id) on @endif" href="/help/show/{{$value_article->id}}">{{$value_article->title}}</a>
          @endforeach
        </dd>
      </dl>
      @endforeach
    </div>
    <div class="right">
      <div class="box">
        <div class="title">{{$article->title}}</div>
        <div class="content">{!! $article->content !!}</div>
      </div>
    </div>
  </div>
</div>
@endsection
@section('pagejs')
<script type="text/javascript">
$(document).ready(function(){
  $('.left dt').click(function() {
    $(this).next('dd').toggle(0);
    if ($(this).find('i').hasClass('up')) {
      $(this).find('i').removeClass('up').addClass('down');
    } else {
      $(this).find('i').removeClass('down').addClass('up');
    }
  })
});
</script>
@endsection

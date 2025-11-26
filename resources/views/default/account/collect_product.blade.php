@extends(Config('common.view.tpl_folder') . '.account._layout_account')
@section('title', '收藏商品 ' . Config('common.pc.app_name'))
@section('pagecss')
<link rel="stylesheet" type="text/css" href="/static/default/style/account.css?v={{Config('common.pc.version')}}" />
@endsection
@section('content')
<div class="account clearfix collect_product_page">
  <div class="container">
    @include(Config('common.view.tpl_folder') . '.account._left_menu')
    <div class="account_main">
      <div class="am_unify_box">
        <div class="top">
          <span class="title">收藏商品</span>
        </div>
        @if($products->total() > 0)
        <div class="items clearfix">
          @foreach($products as $value)
        	<div class="item">
            <div class="item_box">
              <div class="cover">
                <img class="lazy" data-original="{{$value->cover}}" src="{{Config('common.image.lazy_default')}}" />
                <div class="actions">
                  <a class="a1" href="javascript:void(0);" onclick="addCart('{{$value->sku}}');">加入购物车</a>
                  <a class="a2" href="javascript:void(0);" onclick="deleteCollectProduct('{{$value->sku}}');">移除</a>
                </div>
              </div>
              <div class="con">
                <a class="title" href="/product/show/{{$value->sku}}" target="_blank">{{$value->name}}</a>
                @if(!empty($value->specifications))
                <div class="types">
                  @foreach($value->specifications as $key_specn => $value_specn)
                  <div class="types_item">{{$value_specn->specification_name}}-{{$value_specn->specification_option}}</div>
                  @endforeach
                </div>
                @endif
                <div class="price text-price"><em>¥</em>{{$value->price}}</div>
              </div>
            </div>
          </div>
          @endforeach
        </div>
        <div class="page mt-20">{{ $products->appends(Request()->all())->render() }}</div>
        @else
        <div class="noresult">
          <img src="{{Config('common.image.noresult')}}">
          <p>暂无收藏~</p>
        </div>
        @endif

      </div>
    </div>
  </div>
</div>
@endsection
@section('pagejs')
<script type="text/javascript">
layui.use('form', function() {
  var form = layui.form;
  form.on('submit(formSub)', function(data) {
    var load = layer.load();
    var data = data.field;
    data.user_token = $('#user_token').val();
    $.ajax({
      url: '/api/user/update',
      type: 'post',
      data: data,
      success: function(res) {
        layer.close(load);
        if (res.code == 200) {
          layer.msg('更新成功', {time: 1500}, function() {
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
    return false;
  });
});
</script>
@endsection

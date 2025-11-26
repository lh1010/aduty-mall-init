@extends(Config('common.view.tpl_folder') . '.shared._layout')
@section('title', $product->name . ' ' . $product->category_name . ' ' . Config('common.pc.app_name'))
@section('keywords', $product->name . ' ' . $product->category_name . ' ' . Config('common.pc.app_name'))
@section('description', $product->name . ' ' . $product->category_name . ' ' . Config('common.pc.app_name'))
@section('pagecss')
<link rel="stylesheet" type="text/css" href="/static/default/style/product.css?v={{Config('common.pc.version')}}" />
@endsection
@section('content')
<div class="preview_msg">商品预览页</div>
<div class="proshow">
  <div class="container">
    <div class="nav">
      <span class="on">当前位置：</span><a href="/">首页</a><span>></span><a href="/product/list?cid={{$product->category_id}}">{{$product->category_name}}</a><span>></span><span class="on">详情</span>
    </div>
    <div class="left">
      <div class="left_top">
        <div class="lt_left">
          <div class="cover"><img class="lazy" data-original="{{$product->cover}}" src="{{Config('common.image.lazy_default')}}"></div>
          <div class="actions">
            <a href="javascript:void(0);" class="a1 iconfont" onclick="layer.alert('商品预览');">举报</a>
            @if($is_collect != 1)
            <a href="javascript:void(0);" class="a2 iconfont" onclick="layer.alert('商品预览');">收藏</a>
            @else
            <a href="javascript:void(0);" class="a2 on iconfont" onclick="layer.alert('商品预览');">已收藏</a>
            @endif
          </div>
        </div>
        <div class="lt_right">
          <div class="title">{{$product->name}}</div>
          <ul class="ul1 clearfix">
            <li class="li1 iconfont" title="刷新时间">{{$product->refresh_date}}</li>
            <li class="li2 iconfont" title="商品分类">{{$product->category_name}}</li>
          </ul>
          <div class="price clearfix">
            <div class="sale_price text-price">
              <span class="span1">￥</span>
              <span class="span2">{{$product->price}}</span>
            </div>
          </div>
          <div class="dl dl1">
            <div class="dt">演示地址：</div>
            <div class="dd">
              @if($product->demo_address)
              <a href="{{$product->demo_address}}" target="_blank">查看演示</a>
              @else
              暂无演示
              @endif
            </div>
          </div>
          <div class="dl">
            <div class="dt">安装服务：</div>
            <div class="dd">免费安装</div>
          </div>
          <div class="dl">
            <div class="dt">最后刷新：</div>
            <div class="dd">{{$product->refresh_date}}</div>
          </div>
          <div class="btns">
            <button class="layui-btn layui-btn-normal" onclick="layer.alert('商品预览');">立即购买</button>
            <button class="layui-btn layui-btn-warm" onclick="layer.alert('商品预览');">加入购物车</button>
          </div>
        </div>
      </div>
      <div class="left_main">
        <div class="lm_nav">
          <ul>
            <li class="on" data-key="c1">商品详情</li>
            <li data-key="c2">交易规则</li>
          </ul>
          <a class="btn" href="javascript:void(0);" onclick="layer.alert('商品预览');">立即购买</a>
        </div>
        <div class="content c1">
          @if(!empty($product->attributes))
          <div class="probqm">
             <ul class="probq">
              @foreach($product->attributes as $value)
              <li class="l1" title="@foreach($value['values'] as $value_value) {{$value_value}} @endforeach">{{$value['name']}}</li>
              <li class="l2" title="@foreach($value['values'] as $value_value) {{$value_value}} @endforeach">
                @foreach($value['values'] as $value_value) {{$value_value}} @endforeach
              </li>
              @endforeach
            </ul>
          </div>
          @endif
          <div class="c_main">{!! $product->content !!}</div>
        </div>
        <div class="content cp c2">
          <div class="top_title"><i class="iconfont">&#xe89a;</i>交易规则</div>
          <div class="c_main">
            @if($category->rule)
            {!! $category->rule !!}
            @else
            <div class="noresult">
              <img src="{{Config('common.image.noresult')}}">
              <p>平台暂未设置交易规则 ~</p>
            </div>
            @endif
          </div>
        </div>
      </div>
    </div>

    <div class="right">
      <div class="r_section right_user">
        <div class="avatar"><img class="lazy" data-original="{{$shop->logo}}" src="{{Config('common.image.lazy_default')}}"></div>
        <div class="name">{{$shop->name}}</div>
        <ul class="type">
          <li><span class="first">店铺类型：</span><span>{{$shop->type_str}}</span></li>
          <li><span class="first">开店时间：</span><span>{{$shop->created_at}}</span></li>
          <li class="none"><span class="first">所在地区：</span><span>郑州</span></li>
        </ul>
        <ul class="type type2">
          <li class="title"><i class="iconfont">&#xe6a3;</i>联系店铺客服</li>
          <li><span class="first">微信：</span><span>{{ $shop->contact->weixin ? $shop->contact->weixin : '未填写' }}</span></li>
          <li><span class="first">手机：</span><span>{{ $shop->contact->phone ? $shop->contact->phone : '未填写' }}</span></li>
          <li><span class="first">Q Q：</span><span>{{ $shop->contact->qq ? $shop->contact->qq : '未填写' }}</span></li>
          <li><span class="first">电话：</span><span>{{ $shop->contact->telphone ? $shop->contact->telphone : '未填写' }}</span></li>
        </ul>
        <div class="actions">
          <a href="/shop/index/{{$shop->id}}" target="_blank" class="a1">进入店铺</a>
        </div>
      </div>
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

<div class="jubao_popup">
  <div class="close iconfont" onclick="closeJubaoPopup();"></div>
  <div class="main">
    <form class="layui-form" autocomplete="off">
      <input type="hidden" name="model" value="product" />
      <input type="hidden" name="ident_id" value="{{$product->id}}" />
      <div class="layui-form-item">
        <label class="layui-form-label">举报对象</label>
        <div class="layui-input-block">
          <div class="layui-form-mid layui-word-aux"><a class="a1" href="/product/show/{{$product->id}}" target="_blank">{{$product->name}}</a></div>
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">举报类型</label>
        <div class="layui-input-block">
          <select name="type" lay-verify="required">
            <option value=""></option>
            @foreach(Config('common.jubao_type') as $value)
            <option value="{{$value['name']}}">{{$value['content']}}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">举报原因</label>
        <div class="layui-input-block">
          <textarea name="content" placeholder="请输入具体的举报原因" class="layui-textarea"></textarea>
        </div>
      </div>
      <div class="layui-form-item">
        <div class="layui-input-block">
          <button class="layui-btn" lay-submit lay-filter="formSub">提交信息</button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection
@section('pagejs')
<script type="text/javascript" src="/static/default/script/product.js"></script>
<script type="text/javascript">
$(document).ready(function() {
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

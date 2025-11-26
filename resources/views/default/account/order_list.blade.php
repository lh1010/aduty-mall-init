@extends(Config('common.view.tpl_folder') . '.account._layout_account')
@section('title', '我的订单 ' . Config('common.pc.app_name'))
@section('content')
<div class="account clearfix">
  <div class="container">
    @include(Config('common.view.tpl_folder') . '.account._left_menu')
    <div class="account_main opros">
      <div class="am_unify_box">
        <div class="top">
          <span class="title">我的订单</span>
        </div>
        @if($orders->total() > 0)
        @foreach($orders as $value)
        <div class="items_ss">
          <div class="items_top">
            <ul>
              <li>订单号：{{$value->id}}</li>
              <li>交易时间：{{$value->created_at}}</li>
              <li class="more"><a href="/account/order_show?id={{$value->id}}">订单详情</a></li>
            </ul>
          </div>
          <div class="items">
            @foreach($value->snaps as $value_snap)
            <div class="item">
              <div class="cover"><img class="lazy" data-original="{{$value_snap->cover}}" src="{{Config('common.image.lazy_default')}}"></div>
              <div class="infobox">
                <div class="name"><a href="/product/show/{{$value_snap->sku}}" target="_blank">{{$value_snap->name}}</a></div>
                @if(!empty($value_snap->specifications))
                <div class="types">
                  @foreach($value_snap->specifications as $key_specn => $value_specn)
                  <div class="types_item">
                    {{$value_specn->specification_name}}-{{$value_specn->specification_option}}
                  </div>
                  @endforeach
                </div>
                @endif
              </div>
              <div class="price text-price">¥{{$value_snap->total_price}}</div>
            </div>
            @endforeach
          </div>
          <div class="items_foot">
            <div class="ofmain">
              <div class="pricebox">
                <span class="s1">合计：</span>
                <span class="s2 text-price">¥</span>
                <span class="s3 text-price">{{$value->total_price}}</span>
              </div>
              <div class="status">订单状态：{{$value->status_str}}</div>
              @if($value->status == 0)
              <div class="btns"><a type="button" class="layui-btn layui-btn-xs layui-btn-danger" href="/checkout/pay?order_ids={{$value->id}}">立即付款</a></div>
              <div class="actions"><a href="javascript:void(0);" onclick="cancelOrder({{$value->id}});">取消订单</a></div>
              @endif
              @if($value->status == 20)
              <div class="btns"><button type="button" class="layui-btn layui-btn-xs layui-btn-danger" onclick="receiveOrder({{$value->id}});">确认收货</button></div>
              @endif
            </div>
          </div>
        </div>
        @endforeach
        <div class="page mt-20">{{ $orders->appends(Request()->all())->render() }}</div>
        @else
        <div class="noresult">
          <img src="{{Config('common.image.noresult')}}">
          <p>暂无记录</p>
        </div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection
@section('pagejs')
<script type="text/javascript" src="/static/default/script/account.js?v={{Config('common.pc.version')}}"></script>
@endsection

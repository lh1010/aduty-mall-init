@extends(Config('common.view.tpl_folder') . '.account._layout_account')
@section('title', '订单详情 ' . Config('common.pc.app_name'))
@section('content')
<div class="account clearfix">
  <div class="container">
    @include(Config('common.view.tpl_folder') . '.account._left_menu')
    <div class="account_main">
      <div class="am_unify_box my_order_show">
        <div class="top">
          <span class="title"><a href="/account/order_list">我的订单</a> > 详情</span>
        </div>

        <div class="order_flow">
          <li class="@if($order->status >= 10) on @endif">
            <span class="s1">买方付款</span>
            <span class="s2"></span>
            <span class="s3">1</span>
          </li>
          <li class="@if($order->status >= 20) on @endif">
            <span class="s1">商家确认发货</span>
            <span class="s2"></span>
            <span class="s3">2</span>
          </li>
          <li class="@if($order->status >= 30) on @endif">
            <span class="s1">买家确认收货</span>
            <span class="s2"></span>
            <span class="s3">3</span>
          </li>
          <li class="@if($order->status >= 30) on @endif">
            <span class="s1">交易完成</span>
            <span class="s2"></span>
            <span class="s3">4</span>
          </li>
        </div>

        <div class="mos_content">
          <table class="layui-table items">
            <colgroup>
              <col width="120">
              <col width="">
              <col>
            </colgroup>
            <thead>
              <tr>
                <th class="ttop" colspan="2">订单信息</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="t1">订单编号</td>
                <td class="t2">{{$order->id}}</td>
              </tr>
              <tr>
                <td class="t1">订单金额</td>
                <td class="t2 text-price">¥{{$order->total_price}}</td>
              </tr>
              <tr>
                <td class="t1">订单状态</td>
                <td class="t2">{{$order->status_str}}</td>
              </tr>
              <tr>
                <td class="t1">创建时间</td>
                <td class="t2">{{$order->created_at}}</td>
              </tr>
              <tr class="actions">
                <td class="t1">当前操作</td>
                <td class="t2">
                  @if($order->status == 0)
                  <a type="button" class="layui-btn layui-btn-xs layui-btn-danger" href="/checkout/pay?order_ids={{$order->id}}">立即付款</a>
                  <a class="a ml-6" href="javascript:void(0);" onclick="cancelOrder({{$order->id}});">取消订单</a>
                  @elseif($order->status == 20)
                  <button type="button" class="layui-btn layui-btn-xs layui-btn-danger" onclick="receiveOrder({{$order->id}});">确认收货</button>
                  @else
                  无
                  @endif
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="opros">
          <div class="items_ss">
            <div class="items_top">
              <ul>
                <li>订单中的商品</li>
              </ul>
            </div>
            <div class="items">
              @foreach($order->snaps as $value_snap)
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
          </div>
        </div>

        <div class="order_schedule">
          <div class="order_schedule_top">
            订单流程进度
          </div>
          <div class="order_schedule_content">
            @foreach($order->logs as $value)
            <div class="item">{{$value->created_at}} {{$value->content}}</div>
            @endforeach
            @if(!in_array($order->status, [-10, 30]))
            <div class="item">......</div>
            @endif
          </div>
        </div>

      </div>
    </div>
  </div>
</div>
@endsection
@section('pagejs')
<script type="text/javascript" src="/static/default/script/account.js?v={{Config('common.pc.version')}}"></script>
@endsection

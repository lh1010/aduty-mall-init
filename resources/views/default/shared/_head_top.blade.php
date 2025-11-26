<div class="header_top">
  <div class="container">
    <p class="left_title">免费开源可商用的PHP商城系统源码 ~</p>
    <ul class="right_nav">
      @if($user = getLoginUser())
      <li class="item user_box">
        <a href="/account/user_info">{{$user->nickname}}<i class="iconfont">&#xe799;</i></a>
        <div class="fold_items">
          <a href="/account/user_info" class="fold_item">我的信息</a>
          <a href="/account/order_list" class="fold_item">我的订单</a>
          <a href="javascript:void();" onclick="logout();" class="fold_item">安全退出</a>
        </div>
      </li>
      @else
      <li class="item login_box">
        <a href="/login_password">请登录</a><a href="/register">免费注册</a>
      </li>
      @endif
      <li class="item" id="head_top_cart">
        <a href="/cart">购物车 <span class="text-price"></span> 件</a>
      </li>
      <li class="item">
        <a href="/help/show/100000">用户协议</a>
      </li>
      <li class="item">
        <a href="/help/show/100001">隐私协议</a>
      </li>
      <li class="item">
        <a href="/download" target="_blank">移动端</a>
      </li>
    </ul>
  </div>
</div>
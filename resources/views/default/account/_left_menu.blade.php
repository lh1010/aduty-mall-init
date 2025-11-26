<div class="account_menu">
  <dl>
    <dt class="title"><a href="javascript:void(0);">个人信息</a></dt>
    <dd class="{{ in_array(Request()->route()->getActionMethod(), ['user_info']) ? 'on' : '' }}"><a href="/account/user_info">我的信息</a></dd>
    <dd class="{{ in_array(Request()->route()->getActionMethod(), ['user_password']) ? 'on' : '' }}"><a href="/account/user_password">修改密码</a></dd>
    <dd class="{{ in_array(Request()->route()->getActionMethod(), ['user_contact']) ? 'on' : '' }}"><a href="/account/user_contact">联系方式</a></dd>
  </dl>
  <dl>
    <dt class="title"><a href="javascript:void(0);">交易中心</a></dt>
    <dd class="{{ in_array(Request()->route()->getActionMethod(), ['order_list', 'order_show']) ? 'on' : '' }}"><a href="/account/order_list">我的订单</a></dd>
  </dl>
  <dl>
    <dt class="title"><a href="javascript:void(0);">我的资产</a></dt>
    <dd class="{{ in_array(Request()->route()->getActionMethod(), ['wallet', 'wallet_pay', 'wallet_withdraw']) ? 'on' : '' }}"><a href="/account/wallet">我的钱包</a></dd>
  </dl>
  <dl>
    <dt class="title"><a href="javascript:void(0);">身份认证</a></dt>
    <dd class="{{ in_array(Request()->route()->getActionMethod(), ['realname_auth']) ? 'on' : '' }}"><a href="/account/realname_auth">实名认证</a></dd>
    <dd class="{{ in_array(Request()->route()->getActionMethod(), ['company_auth']) ? 'on' : '' }}"><a href="/account/company_auth">企业认证</a></dd>
  </dl>
  <dl>
    <dt class="title"><a href="javascript:void(0);">我的收藏</a></dt>
    <dd class="{{ in_array(Request()->route()->getActionMethod(), ['collect_product']) ? 'on' : '' }}"><a href="/account/collect_product">收藏商品</a></dd>
  </dl>
  <dl>
</div>
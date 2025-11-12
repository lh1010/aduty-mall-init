@extends('admin.shared._layout')
@section('pagecss')
<style>
html, body {
  height: 100%;
}
</style>
@endsection
@section('content')
<!-- sidebar start -->
<div id="sidebar">
  <div class="logo">
    <img src="/static/admin/images/logo.png" />
    <span>后台管理</span>
  </div>
  <div class="sidebar-menu">
    <div class="items">
      <div class="items-title">
        <i class="iconfont">&#xe9e9;</i><span>控制面板</span><i class="iconfont luck-icon-right"></i>
      </div>
      <div class="items-list">
        <a class="item" href="/admin/welcome" target="main">欢迎页</a>
      </div>
    </div>
    <div class="items">
      <div class="items-title">
        <i class="iconfont">&#xe62d;</i><span>商品管理</span><i class="iconfont luck-icon-right"></i>
      </div>
      <div class="items-list">
        <a class="item" href="/admin/product/list" target="main">商品列表</a>
        <a class="item" href="/admin/product/category_list" target="main">商品分类</a>
        <a class="item" href="/admin/product/specification_group_list" target="main">规格组合</a>
        <a class="item" href="/admin/product/specification_list" target="main">商品规格</a>
        <a class="item" href="/admin/product/attribute_group_list" target="main">属性组合</a>
        <a class="item" href="/admin/product/attribute_list" target="main">商品属性</a>
      </div>
    </div>
    <div class="items">
      <div class="items-title">
        <i class="iconfont">&#xe669;</i><span>订单管理</span><i class="iconfont luck-icon-right"></i>
      </div>
      <div class="items-list">
        <a class="item" href="/admin/order/list" target="main">订单列表</a>
      </div>
    </div>
    <div class="items">
      <div class="items-title">
        <i class="iconfont">&#xe698;</i><span>文章中心</span><i class="iconfont luck-icon-right"></i>
      </div>
      <div class="items-list">
        <a class="item" href="/admin/article/list" target="main">文章列表</a>
        <a class="item" href="/admin/article/category_list" target="main">文章分类</a>
      </div>
    </div>
    <div class="items none">
      <div class="items-title">
        <i class="iconfont">&#xe631;</i><span>专题中心</span><i class="iconfont luck-icon-right"></i>
      </div>
      <div class="items-list">
        <a class="item" href="/admin/subject/list" target="main">专题列表</a>
        <a class="item" href="/admin/subject/category_list" target="main">专题分类</a>
      </div>
    </div>
    <div class="items">
      <div class="items-title">
        <i class="iconfont">&#xe60d;</i><span>用户中心</span><i class="iconfont luck-icon-right"></i>
      </div>
      <div class="items-list">
        <a class="item" href="/admin/user/list" target="main">用户列表</a>
      </div>
    </div>
    <div class="items">
      <div class="items-title">
        <i class="iconfont">&#xe631;</i><span>财务管理</span><i class="iconfont luck-icon-right"></i>
      </div>
      <div class="items-list">
        <a class="item" href="/admin/finance/payment_log_list" target="main">支付记录</a>
        <a class="item" href="/admin/finance/withdrawal_log_list" target="main">提现记录</a>
      </div>
    </div>
    <div class="items">
      <div class="items-title">
        <i class="iconfont">&#xe62d;</i><span>平台能力</span><i class="iconfont luck-icon-right"></i>
      </div>
      <div class="items-list">
        <a class="item" href="/admin/adver/list" target="main">平台广告</a>
        <a class="item" href="/admin/cdkey/list" target="main">卡密管理</a>
        <a class="item" href="/admin/city/list" target="main">地区管理</a>
        <a class="item" href="/admin/cusfield/group_list" target="main">自定义字段</a>
      </div>
    </div>
    <div class="items">
      <div class="items-title">
        <i class="iconfont">&#xe625;</i><span>系统管理</span><i class="iconfont luck-icon-right"></i>
      </div>
      <div class="items-list">
        <a class="item" href="/admin/admin/list" target="main">管理员列表</a>
        <a class="item" href="/admin/auth/role_list" target="main">角色管理</a>
        <a class="item" href="/admin/auth/action_list" target="main">权限管理</a>
        <a class="item" href="/admin/set/system" target="main">系统设置</a>
      </div>
    </div>
    <div class="items">
      <div class="items-title">
        <i class="iconfont">&#xe638;</i><span>设置中心</span><i class="iconfont luck-icon-right"></i>
      </div>
      <div class="items-list">
        <a class="item" href="/admin/set/system" target="main">系统设置</a>
        <a class="item" href="/admin/set/sms" target="main">短信设置</a>
        <a class="item" href="/admin/set/payment_weixinpay" target="main">支付设置</a>
        <a class="item" href="/admin/set/client_pc" target="main">应用设置</a>
      </div>
    </div>
  </div>
  <div class="sidebar-foot">© {{date('Y')}} {{Config('common.app_name')}}</div>
</div>
<!-- sidebar end -->
<div id="main">
  <header>
    <span class="mini-button"><i class="iconfont"></i></span>
    <ul class="account">
      <li class="user_info">
        <i class="iconfont luck-icon-account"></i>{{Session::get('admin')['admin']['username']}}
      </li>
      <li class="logout" onclick="logout_admin();"><i class="iconfont luck-icon-logout"></i>退出</li>
    </ul>
  </header>
  <iframe src="/admin/welcome" frameborder="0" name="main"></iframe>
</div>
@endsection
@section('pagejs')
<script type="text/javascript">
function logout_admin()
{
  layer.confirm('确认退出？', function() {
    layer.closeAll();
    var load = layer.load();
    $.ajax({
      url: '/admin/logout',
      type: 'get',
      success: function(res) {
        layer.close(load);
        if (res.code == 200) {
          layer.msg('已安全退出...', {time: 1000}, function() {
            window.location.href = '/admin/login';
          });
        } else {
          layer.msg(res.message);
        }
      }
    })
  })
}
</script>
@endsection

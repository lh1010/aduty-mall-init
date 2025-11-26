@extends(Config('common.view.tpl_folder') . '.account._layout_account')
@section('title', '我的钱包 ' . Config('common.pc.app_name'))
@section('content')
<div class="account clearfix">
  <div class="container">
    @include(Config('common.view.tpl_folder') . '.account._left_menu')
    <div class="account_main">
      <div class="am_unify_box">
        <div class="top">
          <span class="title">我的钱包</span>
        </div>
        <div class="wallet_box">
          <div class="txt"><span>钱包余额</span><span class="price">{{$user->wallet}}</span></div>
          <div class="btns">
            <a class="layui-btn layui-btn-luck" href="/account/wallet_withdraw">提现</a>
            <a class="layui-btn layui-btn-payment" href="/account/wallet_pay">充值</a>
          </div>
        </div>
      </div>

      <div class="am_unify_box log_list mt-10">
        <div class="top">
          <span class="title">钱包明细</span>
          <a href="/account/wallet_log"><i class="iconfont more">&#xe63b;</i></a>
        </div>
        @if(!empty($user_wallet_logs))
        <div class="items">
          @foreach($user_wallet_logs as $value)
        	<div class="item">
            <div class="info">
              <div class="txt">{{$value->description}}</div>
              <div class="ident">{{$value->ident == 'inc' ? '+' : '-'}}{{$value->price}}</div>
            </div>
            <div class="date">{{ Str::limit($value->created_at, 16, '') }}</div>
          </div>
          @endforeach
        </div>
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

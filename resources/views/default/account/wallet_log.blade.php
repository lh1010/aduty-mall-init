@extends(Config('common.view.tpl_folder') . '.shared._layout')
@section('title', '我的钱包 ' . Config('common.pc.app_name'))
@section('pagecss')
<link rel="stylesheet" type="text/css" href="/static/default/style/account.css?v={{Config('common.pc.version')}}" />
@endsection
@section('content')
<div class="account clearfix">
  <div class="container">
    @include(Config('common.view.tpl_folder') . '.account._left_menu')
    <div class="account_main">
      <div class="am_unify_box log_list">
        <div class="top">
          <span class="title"><a href="/account/wallet">我的钱包</a> > 钱包明细</span>
        </div>
        @if($user_wallet_logs->total() > 0)
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
        <div class="page">{{ $user_wallet_logs->appends(Request()->all())->render() }}</div>
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

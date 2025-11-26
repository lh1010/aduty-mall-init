@extends(Config('common.view.tpl_folder') . '.account._layout_account')
@section('title', '钱包余额 ' . Config('common.pc.app_name'))
@section('content')
<div class="account clearfix">
  <div class="container">
    @include(Config('common.view.tpl_folder') . '.account._left_menu')
    <div class="account_main">
      <div class="am_unify_box">
        <div class="top">
          <span class="title"><a href="/account/wallet">我的钱包</a> > 钱包提现</span>
        </div>
        <form class="layui-form" autocomplete="off">
          <div class="layui-form-item">
            <label class="layui-form-label">钱包余额</label>
            <div class="layui-input-block">
              <div class="layui-form-mid layui-word-aux">¥ {{$user->wallet}}</div>
            </div>
          </div>
          <div class="layui-form-item">
            <label class="layui-form-label">提现金额</label>
            <div class="layui-input-block">
              <input type="text" name="price" lay-verify="required" class="layui-input" placeholder="￥" >
            </div>
          </div>
          <div class="layui-form-item">
            <label class="layui-form-label">支付宝账号</label>
            <div class="layui-input-block">
              <input type="text" lay-verify="required" name="alipay_account" class="layui-input" placeholder="请输入支付宝账号" >
            </div>
          </div>
          <div class="layui-form-item">
            <label class="layui-form-label">账号名字</label>
            <div class="layui-input-block">
              <input type="text" name="alipay_name" lay-verify="required" class="layui-input" placeholder="请输入支付宝账号名字" >
            </div>
          </div>
          <div class="layui-form-item">
            <div class="layui-input-block">
              <button class="layui-btn layui-btn-luck" lay-submit lay-filter="formSub">提交信息</button>
            </div>
          </div>
        </form>
        <div class="wallet_withdraw_info">
          <div class="stitle">提现注意事项</div> 
          <div class="item">提现手续费：{{Config('common.finance.withdrawal.commission_rate')}}</div>
          @if(Config('common.finance.withdrawal.min') > 0)
          <div class="item">最小提现金额：{{Config('common.finance.withdrawal.min')}}</div>
          @endif
          @if(Config('common.finance.withdrawal.max') > 0)
          <div class="item">最大提现金额：{{Config('common.finance.withdrawal.max')}}</div>
          @endif
          @if(Config('common.finance.withdrawal.today_count') > 0)
          <div class="item">每天可提现次数：{{Config('common.finance.withdrawal.today_count')}}</div>
          @endif
        </div>
      </div>
      <div class="am_unify_box withdraw_log mt-10">
        <div class="top">
          <span class="title">提现记录</span>
        </div>
        @if($withdrawal_logs->total() > 0)
        <table class="layui-table">
          <colgroup>
            <col width="300">
            <col width="120">
            <col>
          </colgroup>
          <thead>
            <tr>
              <th>申请时间</th>
              <th>金额</th>
              <th>手续费</th>
              <th>审核状态</th>
            </tr>
          </thead>
          <tbody>
            @foreach($withdrawal_logs as $value)
            <tr>
              <td>{{$value->created_at}}</td>
              <td>{{$value->price}}</td>
              <td>{{$value->commission_price}}</td>
              <td>
                @if($value->status == 0)审核中@endif
                @if($value->status == 1)审核成功@endif
                @if($value->status == 2)
                审核失败<i class="iconfont msg" onclick="layer.alert('{{$value->message ? $value->message : '无留言'}}');">&#xe671;</i>
                @endif
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
        <div class="page mt-20">{{ $withdrawal_logs->appends(Request()->all())->render() }}</div>
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
      url: '/api/account/wallet_withdraw',
      type: 'post',
      data: data,
      success: function(res) {
        layer.close(load);
        if (res.code == 200) {
          layer.msg('申请成功，等待平台审核~', {time: 1500}, function() {
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

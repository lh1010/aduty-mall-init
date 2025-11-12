@extends('admin.shared._layout')
@section('content')
<div class="main">
  <div class="bg-white p-4">
    <div class="breadcrumb-stitle">提现记录</div>
  </div>
  <div class="m-4">
    <div class="pagebox">
      <form class="row g-2">
        <div class="col-auto">
          <div class="input-group">
            <select class="form-select" name="kident">
              <option value="用户昵称" @if(Request()->kident == '用户昵称') selected @endif>用户昵称</option>
              <option value="用户ID" @if(Request()->kident == '用户ID') selected @endif>用户ID</option>
            </select>
            <input type="text" class="form-control" placeholder="请输入搜索内容" name="k" value="{{Request()->k}}">
          </div>
        </div>
        <div class="col-auto">
          <select class="form-select" name="status">
            <option value="">状态</option>
            @foreach(Config('common.withdrawal.status') as $key => $value)
            <option value="{{$key}}" @if(Request()->status == "$key") selected @endif>{{$value}}</option>
            @endforeach
          </select>
        </div>
        <div class="col-auto">
          <button type="submit" class="btn btn-primary">搜索</button>
        </div>
      </form>
    </div>
  </div>
  <div class="m-4">
    <div class="pagebox">
      <div class="overflow-hidden">
        <div class="float-start row g-2">
          <div class="col-auto" style="font-size: 12px; color: #999;">
            共有 {{$withdrawal_logs->total()}} 条数据
          </div>
        </div>
        <div class="float-end row g-2">
          <div class="col-auto">
            <a class="btn btn-primary" href="javascript:window.location.reload();"><i class="iconfont luck-icon-refresh"></i></a>
          </div>
        </div>
      </div>
      @if($withdrawal_logs->total() > 0)
      <table class="table table-bordered table-hover text-center mt-3" style="font-size: 12px;">
        <thead>
          <tr>
          	<th>编号</th>
            <th>提现金额</th>
            <th>手续费比例</th>
            <th>手续费金额</th>
            <th>最终金额</th>
            <th>用户ID</th>
            <th>用户昵称</th>
            <th>支付宝账号</th>
            <th>支付宝姓名</th>
            <th>状态</th>
            <th>申请时间</th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody>
          @foreach($withdrawal_logs as $value)
          <tr>
          	<td>{{$value->id}}</td>
            <td>{{$value->price}}</td>
            <td>{{ $value->commission_rate * 100 }}%</td>
            <td>{{$value->commission_price}}</td>
            <td>{{$value->final_price}}</td>
            <td>{{$value->user_id}}</td>
            <td>{{$value->user_nickname}}</td>
            <td>{{$value->alipay_account}}</td>
            <td>{{$value->alipay_name}}</td>
            <td>{{Config('common.withdrawal.status')[$value->status]}}</td>
            <td>{{$value->created_at}}</td>
            <td>
              <button class="btn btn-primary btn-sm" onclick="layerOpen('/admin/finance/withdrawal_set?id={{$value->id}}', '操作提现', '800px', '560px');">操作</button>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
      <div class="page my-3">{{ $withdrawal_logs->appends(Request()->all())->render() }}</div>
      @else
      <div class="noresult">
        <img src="/static/admin/images/noresult.png">
        <p>暂无内容</p>
      </div>
      @endif
    </div>
  </div>
</div>
@endsection

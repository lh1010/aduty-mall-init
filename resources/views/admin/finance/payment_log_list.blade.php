@extends('admin.shared._layout')
@section('content')
<div class="main">
  <div class="bg-white p-4">
    <div class="breadcrumb-stitle">支付记录</div>
  </div>
  <div class="m-4">
    <div class="pagebox">
      <form class="row g-2">
        <div class="col-auto">
          <div class="input-group">
            <select class="form-select" name="kident">
              <option value="">搜索条件</option>
              <option value="用户昵称" @if(Request()->kident == '用户昵称') selected @endif>用户昵称</option>
              <option value="用户ID" @if(Request()->kident == '用户ID') selected @endif>用户ID</option>
            </select>
            <input type="text" class="form-control" placeholder="请输入搜索内容" name="k" value="{{Request()->k}}">
          </div>
        </div>
        <div class="col-auto">
          <select class="form-select" name="payment_way">
            <option value="">支付方式</option>
            @foreach(Config('common.payment_way_array') as $key => $value)
            <option value="{{$key}}" @if(Request()->payment_way == $key) selected @endif>{{$value}} - {{$key}}</option>
            @endforeach
          </select>
        </div>
        <div class="col-auto">
          <select class="form-select" name="status">
            <option value="">支付状态</option>
            <option value="1" @if(Request()->status == '1') selected @endif>已支付</option>
            <option value="0" @if(Request()->status == '0') selected @endif>未支付</option>
          </select>
        </div>
        <div class="col-auto">
          <button type="submit" class="btn btn-primary">搜索</button>
        </div>
      </form>
    </div>
  </div>
  <div class="mx-4">
    <div class="pagebox">
      <div class="overflow-hidden">
        <div class="float-start row g-2">
          <div class="col-auto" style="font-size: 12px; color: #999;">
            共有 {{$payment_logs->total()}} 条数据
          </div>
        </div>
        <div class="float-end row g-2">
          <div class="col-auto">
            <a class="btn btn-primary" href="javascript:window.location.reload();"><i class="iconfont luck-icon-refresh"></i></a>
          </div>
        </div>
      </div>
      @if($payment_logs->total() > 0)
      <table class="table table-bordered table-hover text-center mt-3" style="font-size: 12px;">
        <thead>
          <tr>
          	<th>订单编号</th>
            <th>金额</th>
            <th>支付方式</th>
            <th>用户ID</th>
            <th>用户昵称</th>
            <th>三方平台编号</th>
            <th>描述</th>
            <th>状态</th>
            <th>创建时间</th>
          </tr>
        </thead>
        <tbody>
          @foreach($payment_logs as $value)
          <tr>
          	<td>{{$value->number}}</td>
            <td>{{$value->price}}</td>
            <td>{{$value->payment_way_show}}</td>
            <td>{{$value->user_id}}</td>
            <td>{{$value->user_nickname}}</td>
            <td>{{$value->trade_no}}</td>
            <td>{{$value->body}}</td>
            <td>
              @if($value->status == 1)
              <span class="badge bg-success">已支付</span>
              @else
              <span class="badge bg-secondary"> 未支付</span>
              @endif
            </td>
            <td>{{$value->created_at}}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
      <div class="page my-3">{{ $payment_logs->appends(Request()->all())->render() }}</div>
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
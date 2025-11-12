@extends('admin.shared._layout')
@section('pagecss')
<style>
html, body {
  min-width: 100%;
  max-width: 100%;
  background-color: #fff;
}
</style>
@endsection
@section('content')
<div class="main p-4">
  <form id="form" action="/admin/finance/withdrawal_set" method="post" autocomplete="off">
    @csrf
    <input type="hidden" name="id" value="{{$withdrawal_log->id}}" />
    <div class="mb-3 row">
      <label class="col-2 col-form-label text-end">用户ID：</label>
      <div class="col-auto">
        <input type="text" readonly class="form-control-plaintext" value="{{$user->id}}">
      </div>
    </div>
    <div class="mb-3 row">
      <label class="col-2 col-form-label text-end">用户昵称：</label>
      <div class="col-auto">
        <input type="text" readonly class="form-control-plaintext" value="{{$user->nickname}}">
      </div>
    </div>
    <div class="mb-3 row">
      <label class="col-2 col-form-label text-end">钱包金额：</label>
      <div class="col-auto">
        <input type="text" readonly class="form-control-plaintext" value="￥{{$user->wallet}}">
      </div>
    </div>
    <div class="mb-3 row">
      <label class="col-2 col-form-label text-end">提现金额：</label>
      <div class="col-auto">
        <input type="text" readonly class="form-control-plaintext" value="￥{{$withdrawal_log->price}}">
      </div>
    </div>
    <div class="mb-3 row">
      <label class="col-2 col-form-label text-end">手续费比例：</label>
      <div class="col-auto">
        <input type="text" readonly class="form-control-plaintext" value="{{ $withdrawal_log->commission_rate * 100 }}%">
      </div>
    </div>
    <div class="mb-3 row">
      <label class="col-2 col-form-label text-end">手续费金额：</label>
      <div class="col-auto">
        <input type="text" readonly class="form-control-plaintext" value="￥{{$withdrawal_log->commission_price}}">
      </div>
    </div>
    <div class="mb-3 row">
      <label class="col-2 col-form-label text-end">最终金额：</label>
      <div class="col-auto">
        <input type="text" readonly class="form-control-plaintext" value="￥{{$withdrawal_log->final_price}}">
      </div>
    </div>
    <div class="mb-3 row">
      <label class="col-2 col-form-label text-end">支付宝账号：</label>
      <div class="col-auto">
        <input type="text" readonly class="form-control-plaintext" value="{{$withdrawal_log->alipay_account}}">
      </div>
    </div>
    <div class="mb-3 row">
      <label class="col-2 col-form-label text-end">支付宝姓名：</label>
      <div class="col-auto">
        <input type="text" readonly class="form-control-plaintext" value="{{$withdrawal_log->alipay_name}}">
      </div>
    </div>
    <div class="row mb-3">
      <label class="col-2 col-form-label text-end"><i class="iconfont iconfont-question" data-bs-toggle="tooltip" title="" data-bs-original-title="操作成功后将不能被修改，请谨慎操作" aria-label="操作完成后将不能被修改，请谨慎操作"></i> 操作状态：</label>
      <div class="col-auto">
        <select class="form-select" name="status" @if($withdrawal_log->status != 0) disabled @endif>
          <option value="1" @if($withdrawal_log->status == 1) selected @endif>已审核转账</option>
          <option value="2" @if($withdrawal_log->status == 2) selected @endif>审核失败</option>
        </select>
      </div>
    </div>
    <div class="row mb-3">
      <label class="col-2 col-form-label text-end"><i class="iconfont iconfont-question" data-bs-toggle="tooltip" title="" data-bs-original-title="留底查看" aria-label="留底查看"></i> 转账凭证：</label>
      <div class="col-auto">
        @if(!empty($withdrawal_log->transfer_image))
        <div class="luckFU uploaded" style="width: 80px; height: 80px;" data-name="transfer_image" data-url="/admin/upload"><i class="luckFU_remove iconfont" href="javascript:void(0);" onclick="luckFU_delImage()"></i><img src="{{$withdrawal_log->transfer_image}}"))
        <div class="luckFU uploaded" style="width: 8"><input type="hidden" name="transfer_image" value="{{$withdrawal_log->transfer_image}}"></div>
        @else
        <div class="luckFU" style="width: 80px; height: 80px;" data-url="/admin/upload?type=withdrawal" data-name="transfer_image"></div>
        @endif
      </div>
    </div>
    <div class="row mb-3">
      <label class="col-2 col-form-label text-end"><i class="iconfont iconfont-question" data-bs-toggle="tooltip" title="" data-bs-original-title="审核失败留言" aria-label="审核失败留言"></i> 留言：</label>
      <div class="col-8">
        <textarea class="form-control" rows="3" name="message">{{$withdrawal_log->message}}</textarea>
      </div>
    </div>
    <div class="row mt-4">
      <label class="col-2"></label>
      <div class="col-auto">
        <button type="submit" class="btn btn-primary">确认提交</button>
      </div>
    </div>
  </form>
</div>
@endsection
@section('pagejs')
<script type="text/javascript" src="/static/admin/plugins/luck.file.upload.js"></script>
@include('admin.shared._jquery_validation')
<script type="text/javascript">
$("#form").validate({
  submitHandler: function() {
    var str = '请确认操作！<br/>';
    str += '操作成功后，状态将不能被修改！';
    layer.confirm(str, function() {
      layer.closeAll();
      var load = layer.load();
      $("#form").ajaxSubmit(function(res) {
        layer.close(load);
        if (res.code == 401) {
          goLogin(); return false;
        }
        if (res.code == 200) {
          window.parent.location.reload();
          var index = parent.layer.getFrameIndex(window.name);
          parent.layer.close(index);
        } else if (res.code == 400) {
          layer.msg(res.message); return false;
        } else {
          layer.msg('操作失败'); return false;
        }
      });
    });
  }
});
</script>
@endsection

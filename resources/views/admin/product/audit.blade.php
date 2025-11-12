@extends('admin.shared._layout')
@section('pagecss')
<style type="text/css">
html, body {
  min-width: 100%;
  max-width: 100%;
}
</style>
@endsection
@section('content')
<div class="main">
  <div class="pagebox m-3">
    <div><b>审核内容</b></div>
    <form class="mt-4" id="form" action="" method="post" autocomplete="off">
      @csrf
      <input type="hidden" name="id" value="{{$product->id}}">
      <div class="row mb-3">
        <label class="col-2 col-form-label text-end">状态：</label>
        <div class="col-8">
          <select class="form-select" name="status">
            @foreach(Config('common.mall.product_status') as $key => $value)
            <option value="{{$key}}" @if($product->status == $key) selected @endif>{{$value}}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-2 col-form-label text-end"><i class="iconfont iconfont-question" data-bs-toggle="tooltip" title="审核失败时，客户端显示留言"></i> 留言：</label>
        <div class="col-8">
          <input class="form-control" name="message">
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-2 col-form-label text-end"></label>
        <div class="col-8">
          <button type="submit" class="btn btn-primary">提交审核</button>
        </div>
      </div>
    </form>
  </div>
  <div class="pagebox m-3">
    <div class="overflow-hidden">
      <div class="float-start row g-2">
        <div class="col-auto"><b>操作记录</b></div>
      </div>
      <div class="float-end row g-2">
        <div class="col-auto">
          <a class="btn btn-primary" href="javascript:window.location.reload();"><i class="iconfont luck-icon-refresh"></i></a>
        </div>
      </div>
    </div>
    @if(!empty($logs))
    <table class="table table-bordered table-hover text-center mt-3">
      <thead>
        <tr>
          <th>状态</th>
          <th>留言</th>
          <th>时间</th>
        </tr>
      </thead>
      <tbody>
        @foreach($logs as $value)
        <tr>
          <td>{{Config('common.mall.product_status')[$value->status]}}</td>
          <td title="{{ $value->message ? $value->message : '无' }}">{{ $value->message ? Str::limit($value->message, 20) : '无' }}</td>
          <td>{{ Str::limit($value->created_at, 16, '') }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @else
    <div class="noresult">
      <img src="/static/admin/images/noresult.png">
      <p>暂无记录</p>
    </div>
    @endif
  </div>
</div>
@endsection
@section('pagejs')
@include('admin.shared._jquery_validation')
<script type="text/javascript">
 $("#form").validate({
  submitHandler: function() {
    layer.confirm('确认提交审核？', function() {
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
@extends('admin.shared._layout')
@section('pagecss')
<style type="text/css">
html, body {
  min-width: 100%;
  background-color: #fff;
}
</style>
@endsection
@section('content')
<div class="main p-4">
  <form class="" id="form" action="" method="post" autocomplete="off">
    @csrf
    <input type="hidden" name="id" value="{{$user->id}}">
    <div class="row mb-3">
      <label class="col-2 col-form-label text-end">用户昵称：</label>
      <div class="col-auto">
        <input type="text" readonly="" class="form-control-plaintext" value="{{$user->nickname}}">
      </div>
    </div>
    <div class="row mb-3">
      <label class="col-2 col-form-label text-end">金币余额：</label>
      <div class="col-auto">
        <input type="text" readonly="" class="form-control-plaintext" value="{{$user->gold}}">
      </div>
    </div>
    <div class="row mb-3">
      <label class="col-2 col-form-label text-end">操作：</label>
      <div class="col-auto">
        <select class="form-select" name="ident">
          <option value="inc">加</option>
          <option value="dec">减</option>
        </select>
      </div>
    </div>
    <div class="row mb-3">
      <label class="col-2 col-form-label text-end"><i class="iconfont iconfont-question" data-bs-toggle="tooltip" title="请输入金币"></i> 金币：</label>
      <div class="col-8">
        <input class="form-control" name="gold">
      </div>
    </div>
    <div class="row mb-3">
      <label class="col-2 col-form-label text-end"></label>
      <div class="col-8">
        <button type="submit" class="btn btn-primary">提交</button>
      </div>
    </div>
  </form>
  <div class="mt-4 px-3 pt-3" style="border-top: 1px solid #f5f5f5;">
    <div><b>流水记录</b></div>
    @if($logs->total() > 0)
    <table class="table text-center mt-3">
      <thead>
        <tr>
          <th>金币</th>
          <th>说明</th>
          <th>时间</th>
        </tr>
      </thead>
      <tbody>
        @foreach($logs as $value)
        <tr>
          <td>{{$value->ident == 'inc' ? '+' : '-'}}{{$value->gold}}</td>
          <td>{{$value->description}}</td>
          <td>{{$value->created_at}}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
    <div class="page mt-3">{{ $logs->appends(Request()->all())->render() }}</div>
    @else
    <div class="noresult">
      <img src="/static/admin/images/noresult.png">
      <p>暂无内容~</p>
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
    layer.confirm('确认提交？', function() {
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

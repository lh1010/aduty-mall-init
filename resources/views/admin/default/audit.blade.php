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
      <input type="hidden" name="id" value="{{$result->id}}">
      <div class="row mb-3">
        <label class="col-2 col-form-label text-end">ID：</label>
        <div class="col-auto">
          <div class="form-control-plaintext">{{$result->id}}</div>
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-2 col-form-label text-end">状态：</label>
        <div class="col-auto">
          <select class="form-select" name="status">
            @foreach(Config('common.tbname.status') as $key => $value)
            <option value="{{$key}}" @if($result->status == $key) selected @endif>{{$value}}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-2 col-form-label text-end"><i class="iconfont luck-icon-question" data-bs-toggle="tooltip" title="客户端留言"></i> 留言：</label>
        <div class="col-8">
          <textarea class="form-control" name="message"></textarea>
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-2 col-form-label text-end"></label>
        <div class="col-8">
          <button type="submit" class="btn btn-primary">提交信息</button>
        </div>
      </div>
    </form>
  </div>
  <div class="pagebox m-3">
    <div><b>操作记录</b></div>
    @if($logs->total() > 0)
    <table class="table mt-3" style="font-size: 12px;">
      <thead>
        <tr>
          <th>时间</th>
          <th>状态</th>
          <th>留言</th>
        </tr>
      </thead>
      <tbody>
        @foreach($logs as $value)
        <tr>
          <td>{{$value->created_at}}</td>
          <td>{{Config('common.tbname.status')[$value->status]}}</td>
          <td>{{$value->message}}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
    <div class="page mt-4">{{ $logs->appends(Request()->all())->render() }}</div>
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
<script type="text/javascript" src="/static/admin/plugins/luck.file.upload.js"></script>
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

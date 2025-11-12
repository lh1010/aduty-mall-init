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
    <div><b>修改状态</b></div>
    @if(!empty($log))
    <div class="alert alert-info mt-3" style="font-size: 12px">最新提交的信息</div>
    <form class="mt-4" id="form" action="" method="post" autocomplete="off">
      @csrf
      <input type="hidden" name="id" value="{{$log->user_id}}">
      <div class="row mb-3">
        <label class="col-2 col-form-label text-end">真实姓名：</label>
        <div class="col-auto">
          <div class="form-control-plaintext">{{$log->realname}}</div>
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-2 col-form-label text-end">身份证号：</label>
        <div class="col-auto">
          <div class="form-control-plaintext">{{$log->idcard}}</div>
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-2 col-form-label text-end">身份证正面：</label>
        <div class="col-auto">
          <div class="form-control-plaintext">
            <a href="{{$log->idcard_img1}}" target="_blank"><img src="{{$log->idcard_img1}}" style="width: 80px; height: 80px; border-radius: 5px; border: 1px solid #f5f5f5; padding: 3px;"></a>
          </div>
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-2 col-form-label text-end">身份证反面：</label>
        <div class="col-auto">
          <div class="form-control-plaintext">
            <a href="{{$log->idcard_img2}}" target="_blank"><img src="{{$log->idcard_img2}}" style="width: 80px; height: 80px; border-radius: 5px; border: 1px solid #f5f5f5; padding: 3px;"></a>
          </div>
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-2 col-form-label text-end">状态：</label>
        <div class="col-auto">
          <select class="form-select" name="status">
            @foreach(Config('common.user.realname_auth_status') as $key => $value)
            <option value="{{$key}}" @if($log->status == $key) selected @endif>{{$value}}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-2 col-form-label text-end"><i class="iconfont luck-icon-question" data-bs-toggle="tooltip" title="" data-bs-original-title="客户端留言"></i> 留言：</label>
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
    @else
    <div class="noresult">
      <img src="/static/admin/images/noresult.png">
      <p>暂无提交信息</p>
    </div>
    @endif
  </div>

  <div class="pagebox m-3">
    <div><b>提交记录</b></div>
    @if(!empty($logs))
    <table class="table mt-3" style="font-size: 12px;">
      <thead>
        <tr>
          <th>真实姓名</th>
          <th>身份证号</th>
          <th>身份证正面</th>
          <th>身份证反面</th>
          <th>状态</th>
          <th>留言</th>
          <th>提交时间</th>
        </tr>
      </thead>
      <tbody>
        @foreach($logs as $value)
        <tr>
          <td>{{$value->realname}}</td>
          <td>{{$value->idcard}}</td>
          <td>
            <a href="{{$value->idcard_img1}}" target="_blank"><img src="{{$value->idcard_img1}}" style="width: 65px; height: 65px; border-radius: 5px; border: 1px solid #f5f5f5; padding: 3px;"></a>
          </td>
          <td>
            <a href="{{$value->idcard_img2}}" target="_blank"><img src="{{$value->idcard_img2}}" style="width: 65px; height: 65px; border-radius: 5px; border: 1px solid #f5f5f5; padding: 3px;"></a>
          </td>
          <td>{{Config('common.user.realname_auth_status')[$value->status]}}</td>
          <td>{{ $value->message ? $value->message : '无'}}</td>
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
    layer.confirm('确认提交？', function() {
      layer.closeAll();
      var load = layer.load();
      $("#form").ajaxSubmit(function(res) {
        layer.close(load);
        if (res.code == 401) {
          goLogin(); return false;
        }
        if (res.code == 200) {
          layer.msg('操作成功', {time: 1500}, function() {
            window.parent.location.reload();
            var index = parent.layer.getFrameIndex(window.name);
            parent.layer.close(index);
          });
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

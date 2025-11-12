@extends('admin.shared._layout')
@section('pagecss')
<style type="text/css">
.categorys thead tr th {
  border-bottom: 1px solid #ddd !important;
}
.categorys .addchildboard {    
  margin-left: 5px;
  opacity: 0;
}
.categorys .addchildboard::before {
  content: '\e600';
  margin-right: 3px;
}
.categorys .name:hover .addchildboard {
  opacity: 1;
}
</style>
@endsection
@section('content')
<div class="main">
  <div class="bg-white p-4">
    <div class="breadcrumb-stitle">商品分类</div>
  </div>

  <div class="mx-4 mt-4">
    <div class="pagebox">
      <div class="overflow-hidden">
        <div class="float-start row g-2">
          <div class="col-auto" style="font-size: 12px; color: #999;">
            共有 {{count($categorys)}} 条数据
          </div>
        </div>
        <div class="float-end row g-2">
          <div class="col-auto">
            <a class="btn btn-primary" href="/admin/product/category_create"><i class="iconfont luck-icon-jia"></i> 新增</a>
          </div>
          <div class="col-auto">
            <a class="btn btn-primary" href="javascript:window.location.reload();"><i class="iconfont luck-icon-refresh"></i></a>
          </div>
        </div>
      </div>
      @if(!empty($categorys))
      <table class="table table-no-border text-center mt-3 categorys" style="font-size: 12px;">
        <thead>
          <tr>
            <th width="30"></th>
            <th width="80">ID</th>
            <th style="text-align: left;">名字</th>
            <th>简介</th>
            <th>排序</th>
            <th>状态</th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody>
          @foreach($categorys as $value)
          <tr class="group_{{$value->parent_id}}">
            <td class="{{$value->parent_id}}">@if($value->parent_id == 0)<a href="javascript:void(0);" onclick="toggle_group(this)">[-]</a>@endif</td>
            <td>{{$value->id}}</td>
            <td class="name" style="text-align: left;">
              <span class="txt">
                @php
                for($i = 1; $i < $value->level; $i++) {
                  echo '|—— ';
                }
                @endphp
                {{$value->name}}
              </span>
              <a class="addchildboard iconfont" href="/admin/product/category_create?parent_id={{$value->id}}">添加子分类</a>
            </td>
            <td title="{{$value->description}}">{{ Str::limit($value->description, 30) }}</td>
            <td>{{$value->sort}}</td>
            <td>
              @if($value->status == 1)
              <span class="badge bg-success">开启</span>
              @else
              <span class="badge bg-danger">关闭</span>
              @endif
            </td>
            <td>
              <a class="btn btn-primary btn-sm" href="/admin/product/category_edit?id={{$value->id}}">编辑</a>
              <button type="button" class="btn btn-danger btn-sm" onClick="deleteAction({{$value->id}})">删除</button>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
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
@section('pagejs')
<script type="text/javascript">
function toggle_group(thisNode) {
  if ($(thisNode).html() == '[-]') {
    $(thisNode).html('[+]');
  } else {
    $(thisNode).html('[-]');
  }
  $(thisNode).parents('.group_0').nextUntil('.group_0').toggleClass('none');
}

function deleteAction(id = '') {
  if (id == '') {
    var trs = $("tbody :checkbox:checked").parents("tr");
    if (trs.length <= 0) {
      layer.msg('请勾选需删除的数据', {icon: 5, time: 1500});
      return false;
    }
    var ids = '';
    for (var i = 0; i < trs.length; i++) {
      ids += trs[i].id + ',';
    }
    id = ids.substr(0, ids.length - 1);
  }
  layer.confirm('确认删除？', function(index) {
    layer.closeAll();
    var load = layer.load();
    $.ajax({
      type: 'GET',
      url: '/admin/product/category_delete?id=' + id,
      dataType: 'json',
      success: function(res) {
        layer.close(load);
        if (res.code == 200) {
          layer.msg('操作成功', {time: 1500}, function() {window.location.reload();});
        } else if (res.code == 400) {
          layer.msg(res.message);
        } else {
          layer.msg('操作失败');
        }
      }
    });     
  });
}    
</script>
@endsection
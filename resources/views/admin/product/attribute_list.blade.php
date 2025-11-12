@extends('admin.shared._layout')
@section('pagecss')
<style>
.options {
  display: flex;
  flex-wrap: wrap;
  margin-bottom: -4px;
  margin-right: -4px;
}
.option {
  background-color: #5cb85c;
  padding: 2px 6px;
  margin-bottom: 4px;
  margin-right: 4px;
  cursor: pointer;
  font-size: 10px !important;
  color: #fff;
  border-radius: 4px;
}
</style>
@endsection
@section('content')
<div class="main">
  <div class="bg-white p-4">
    <div class="breadcrumb-stitle">商品属性</div>
    @if(!empty($group))
    <div class="mt-3" style="font-size: 12px;">属性组合：{{$group->name}}</div>
    @endif
  </div>
  <div class="m-4">
    <div class="pagebox">
      <form class="row g-2">
        <div class="col-auto">
          <select class="form-select" name="group_id">
            <option value="">属性组合</option>
            @foreach($groups as $value)
            <option value="{{$value->id}}" @if(Request()->group_id == $value->id) selected @endif>{{$value->name}}</option>
            @endforeach
          </select>
        </div>
        <div class="col-auto">
          <input type="text" class="form-control" placeholder="输入搜索内容" name="k" value="{{Request()->k}}">
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
            共有 {{$attributes->total()}} 条数据
          </div>
        </div>
        <div class="float-end row g-2">
          <div class="col-auto">
            <a class="btn btn-primary" href="/admin/product/attribute_create?group_id={{Request()->group_id}}"><i class="iconfont luck-icon-jia"></i> 新增</a>
          </div>
          <div class="col-auto">
            <a class="btn btn-primary" href="javascript:window.location.reload();"><i class="iconfont luck-icon-refresh"></i></a>
          </div>
        </div>
      </div>
      @if($attributes->total() > 0)
      <table class="table text-center mt-3">
        <thead>
          <tr>
            <th>排序</th>
            <th>ID</th>
            <th>属性组合</th>
            <th>属性名字</th>
            <th>类型</th>
            <th width="220px">选项值</th>
            <th>必需</th>
            <th>描述</th>
            <th>状态</th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody>
          @foreach($attributes as $value)
          <tr>
            <td>{{$value->sort}}</td>
            <td>{{$value->id}}</td>
            <td>{{$value->group_name}}</td>
            <td>{{$value->name}}</td>
            <td>{{$value->type}}</td>
            <td>
              @if($value->type == '选项')
              <div class="options" onclick="layerOpen('/admin/product/attribute_option_list?attribute_id={{$value->id}}', '选项值');">
                @foreach($value->options as $value_option)
                <span class="option">{{$value_option->option}}</span>
                @endforeach
              </div>
              @endif
            </td>
            <td>{{$value->required}}</td>
            <td>{{$value->description}}</td>
            <td>{{Config('common.mall.status')[$value->status]}}</td>
            <td>
              @if($value->type == '选项')
              <button type="button" class="btn btn-success btn-sm" onclick="layerOpen('/admin/product/attribute_option_list?attribute_id={{$value->id}}', '选项值');">选项值</button>
              @endif
              <a class="btn btn-primary btn-sm" href="/admin/product/attribute_edit?id={{$value->id}}{{ encodePrevPageParams(); }}">编辑</a>
              <button type="button" class="btn btn-danger btn-sm" onClick="deleteAction({{$value->id}})">删除</button>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
      <div class="page my-3">{{ $attributes->appends(Request()->all())->render() }}</div>
      @else
      <div class="noresult">
        <img src="/static/admin/images/noresult.png">
        <p>暂无内容~</p>
      </div>
      @endif
    </div>
  </div>
</div>
@endsection
@section('pagejs')
<script type="text/javascript">
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
  var layer_confirm = layer.confirm('确认删除？', function(index) {
    layer.close(layer_confirm);
    var layer_load = layer.load();
    $.ajax({
      type: 'GET',
      url: '/admin/product/attribute_delete?id=' + id,
      dataType: 'json',
      success: function(res) {
        layer.close(layer_load);
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
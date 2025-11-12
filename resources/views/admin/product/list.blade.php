@extends('admin.shared._layout')
@section('pagecss')
<style>
.cover {
  width: 70px;
  height: 70px;
  border-radius: 5px;
  border: 1px solid #eee;
  padding: 3px;
  vertical-align: top;
  object-fit: cover;
}
.skubox_layer {
  max-height: 400px;
  overflow-y: auto;
}
.skubox {
  margin-bottom: 2px;
}
.skubox .bd {
  border: 1px solid #eee;
  display: inline-block;
  padding: 2px 6px;
}
</style>
@endsection
@section('content')
<div class="main">
  <div class="bg-white p-4">
    <div class="breadcrumb-stitle">商品列表</div>
  </div>
  <div class="m-4">
    <div class="pagebox">
      <form class="row g-2">
        <div class="col-auto">
          <select class="form-select" name="category_id">
            <option value="">全部分类</option>
            @foreach($categorys as $value)
            <option value="{{$value->id}}" @if(Request()->category_id == $value->id) selected @endif>@php for($i = 1; $i < $value->level; $i++) {echo '&nbsp;&nbsp;';} @endphp {{$value->name}}</option>
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
            共有 {{$products->total()}} 条数据
          </div>
        </div>
        <div class="float-end row g-2">
          <div class="col-auto">
            <a class="btn btn-primary" href="/admin/product/create"><i class="iconfont luck-icon-jia"></i> 新增</a>
          </div>
          <div class="col-auto">
            <a class="btn btn-primary" href="javascript:window.location.reload();"><i class="iconfont luck-icon-refresh"></i></a>
          </div>
        </div>
      </div>
      @if($products->total() > 0)
      <table class="table text-center mt-3" style="font-size: 12px;">
        <thead>
          <tr>
            <th width="80px">ID</th>
            <th width="90px">封面图</th>
            <th width="220px">商品名</th>
            <th>分类</th>
            <th class="none">SKU</th>
            <th>价格</th>
            <th>库存</th>
            <th>创建时间</th>
            <th>状态</th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody>
          @foreach($products as $value)
          <tr>
            <td>{{$value->id}}</td>
            <td>@if(!empty($value->cover))<img src="{{$value->cover}}" class="cover">@endif</td>
            <td>
              <span class="text-danger">[{{$value->specification_type}}]</span> 
              {{$value->name}}
            </td>
            <td>{{$value->full_category_name}}</td>
            <td class="none">
              <div class="skubox_layer">
                @foreach($value->skus as $key_sku => $value_sku)
                <div class="skubox">
                  <div class="bd">
                    <a href="javascript:void(0);">{{$value_sku->sku}}</a>
                    @foreach($value_sku->specifications as $key_spe => $value_spe)
                    <span class="ms-1">{{$value_spe->specification_name}} - {{$value_spe->specification_option}}</span>
                    @endforeach
                  </div>
                </div>
                @endforeach
              </div> 
            </td>
            <td class="text-danger">{{$value->price}}</td>
            <td>{{$value->stock}}</td>
            <td>{{$value->created_at}}</td>
            <td>
              <a href="javascript:void(0);" onclick="layerOpen('/admin/product/audit?id={{$value->id}}', '审核', '800px', '80%');">
                {{Config('common.mall.product_status')[$value->status]}}
              </a>
            </td>
            <td>
              <a class="btn btn-primary btn-sm" href="/admin/product/edit?id={{$value->id}}{{ encodePrevPageParams(); }}">编辑</a>
              <div class="btn-group">
                <button class="btn btn-success btn-sm dropdown-toggle" data-bs-toggle="dropdown">更多</button>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="javascript:void(0);" onclick="layerOpen('/admin/product/audit?id={{$value->id}}', '审核', '800px', '80%');">审核</a></li>
                  <li><a class="dropdown-item" href="javascript:void(0);" onclick="deleteAction({{$value->id}})">删除</a></li>
                </ul>
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
      <div class="page my-3">{{ $products->appends(Request()->all())->render() }}</div>
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
      url: '/admin/product/delete?id=' + id,
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
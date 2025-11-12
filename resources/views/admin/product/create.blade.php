@extends('admin.shared._layout')
@section('pagecss')
<link rel="stylesheet" type="text/css" href="/static/admin/style/product.css" />
@endsection
@section('content')
<div class="main">
  <div class="bg-white p-4">
    <ol class="breadcrumb">
      <li class="breadcrumb-item">商品列表</li>
      <li class="breadcrumb-item active">新增</li>
    </ol>
    <div class="breadcrumb-stitle">
      <a href="javascript:history.go(-1);"><i class="iconfont goback"></i></a>
      <span class="txt">新增</span>
    </div>
  </div>
  <ul class="form-message m-4 p-4"></ul>
  <form class="mt-4" id="form" action="/admin/product/store" method="post" autocomplete="off">
    @csrf
    <input type="hidden" name="category_id" value="">
    <div class="m-4 pagebox">
      <div><b>基础信息</b></div>
      <div class="mt-4">
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end"><span class="text-danger">*</span> 商品分类：</label>
          <div class="col-auto">
            <div class="input-group">
              <div class="col-auto me-2" id="spc1">
                <select class="form-select">
                  <option value="">请选择</option>
                  @foreach($categorys as $key => $value)
                  <option value="{{$value->id}}">{{$value->name}}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-auto me-2 none" id="spc2"></div>
              <div class="col-auto none" id="spc3">
                <select class="form-select">
                  <option value="1">是</option>
                  <option value="2">否</option>
                </select>
              </div>
            </div>
          </div>
        </div>
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end"><span class="text-danger">*</span> 商品名字：</label>
          <div class="col-8">
            <input class="form-control" name="name" placeholder="">
          </div>
        </div>
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">商品封面图：</label>
          <div class="col-auto">
            <div class="fmr" style="width: 90px; height: 90px;" data-name="cover"></div>
          </div>
        </div>
        <div class="row mb-4 upload_images">
          <label class="col-2 col-form-label text-end">商品轮播图：</label>
          <div class="col-auto items">
            <div class="fmr item" style="width: 90px; height: 90px;" data-name="images[]"></div>
            <div class="fmr item" style="width: 90px; height: 90px;" data-name="images[]"></div>
            <div class="fmr item" style="width: 90px; height: 90px;" data-name="images[]"></div>
            <div class="fmr item" style="width: 90px; height: 90px;" data-name="images[]"></div>
            <div class="fmr item" style="width: 90px; height: 90px;" data-name="images[]"></div>
          </div>
        </div>
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">状态：</label>
          <div class="col-auto">
            <select class="form-select" name="status">
              @foreach(Config('common.mall.product_status') as $key => $value)
              <option value="{{$key}}" @if($key == 1) selected @endif>{{$value}}</option>
              @endforeach
            </select>
          </div>
        </div>
      </div>
    </div>

    <div class="m-4 pagebox">
      <div><b>销售信息</b></div>
      <div class="mt-4">
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">商品规格：</label>
          <div class="col-8">
            <div class="form-check form-check-inline pt-2">
              <input class="form-check-input" type="radio" name="specification_type" id="specification_type_1" value="单规格" checked>
              <label class="form-check-label" for="specification_type_1">单规格</label>
            </div>
            <div class="form-check form-check-inline pt-2">
              <input class="form-check-input" type="radio" name="specification_type" id="specification_type_2" value="多规格">
              <label class="form-check-label" for="specification_type_2">多规格</label>
            </div>
          </div>
        </div>
        <div id="specification102" class="none">
          <div class="row mb-3">
            <label class="col-2 col-form-label text-end">规格组合：</label>
            <div class="col-auto">
              <select class="form-select" name="specification_group_id">
                <option value="">请选择</option>
                @foreach($specification_groups as $value)
                <option value="{{$value->id}}">{{$value->name}}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>
        <div id="specification101" class="none">
          <div class="row mb-3">
            <label class="col-2 col-form-label text-end">选择规格：</label>
            <div class="col-8">
              <div class="specification mt-2" id="specification"></div>
            </div>
          </div>
          <div class="row mb-3 specification_group" id="specification_group" style="display: none">
            <label class="col-2 col-form-label text-end">规格组合：</label>
            <div class="col-8">
              <div class="mt-2">
                <div class="batch_operation">
                  <input type="text" id="batch_price" value="" style="width: 100px" placeholder="销售价">
                  <input type="text" id="batch_stock" class="ms-2" name="" value="" style="width: 100px" placeholder="库存">
                  <a class="btn btn-outline-primary btn-sm ms-2" onclick="fillData();">批量填充</a>
                  <a class="btn btn-outline-primary btn-sm ms-2" onclick="fillImageData();">填充商品封面图</a>
                </div>
                <table class="table text-center mt-3" id="skus"></table>
              </div>
            </div>
          </div>
        </div>
        <div id="specification100">
          <div class="row mb-3">
            <label class="col-2 col-form-label text-end">价格：</label>
            <div class="col-auto">
              <div class="input-group">
                <input type="text" class="form-control" name="price" value="" placeholder="">
                <span class="input-group-text" >元</span>
              </div>
            </div>
          </div>
          <div class="row mb-3">
            <label class="col-2 col-form-label text-end">库存：</label>
            <div class="col-auto">
              <input class="form-control" name="stock" value="" placeholder="">
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="m-4 pagebox">
      <div><b>商品详情</b></div>
      <div class="mt-4">
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">商品详情：</label>
          <div class="col-8">
            <textarea class="summernote" name="content"></textarea>
          </div>
        </div>
      </div>
    </div>

    <div class="m-4 pagebox">
      <div>
        <b>商品属性</b>
      </div>
      <div class="mt-4">
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">属性组合：</label>
          <div class="col-auto">
            <select class="form-select" name="attribute_group_id">
              <option value="">请选择</option>
              @foreach($attribute_groups as $value)
              <option value="{{$value->id}}">{{$value->name}}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div id="attributes"></div>
      </div>
    </div>

    <div class="m-4 pagebox none">
      <div><b>物流服务</b></div>
      <div class="mt-4">
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">发货时效：</label>
          <div class="col-8">
            @foreach(Config('common.mall.shipment_time') as $key => $value)
            <div class="form-check form-check-inline pt-2">
              <input class="form-check-input" type="radio" name="shipment_time" id="shipment_time_{{$key}}" value="{{$value}}" @if($key == 0) checked @endif>
              <label class="form-check-label" for="shipment_time_{{$key}}">{{$value}}</label>
            </div>
            @endforeach
          </div>
        </div>
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">物流方式：</label>
          <div class="col-8">
            @foreach(Config('common.mall.transport_way') as $key => $value)
            <div class="form-check form-check-inline pt-2">
              <input class="form-check-input" type="radio" name="transport_way" id="transport_way_{{$key}}" value="{{$value}}" @if($key == 0) checked @endif>
              <label class="form-check-label" for="transport_way_{{$key}}">{{$value}}</label>
            </div>
            @endforeach
          </div>
        </div>
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">包邮：</label>
          <div class="col-auto">
            <select class="form-select" name="free_shipping">
              <option value="1">是</option>
              <option value="2">否</option>
            </select>
          </div>
        </div>
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">运费模板：</label>
          <div class="col-auto">
            <select class="form-select" name="freight_tpl_id">
              <option value="">请选择</option>
              <option value="1">测试模板</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <div class="m-4 pagebox">
      <div><b>SEO优化</b></div>
      <div class="mt-4">
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">seo title：</label>
          <div class="col-8">
            <input class="form-control" name="seo_title" placeholder="">
          </div>
        </div>
      </div>
      <div class="mt-4">
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">seo keywords：</label>
          <div class="col-8">
            <input class="form-control" name="seo_keywords" placeholder="">
          </div>
        </div>
      </div>
      <div class="mt-4">
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">seo description：</label>
          <div class="col-8">
            <input class="form-control" name="seo_description" placeholder="">
          </div>
        </div>
      </div>
    </div>
    <div class="form-foot-blank"></div>
    <div class="form-foot">
      <div class="box">
        <button type="submit" class="btn btn-primary">提交信息</button>
      </div>
    </div>
  </form>
</div>
<form action="/admin/upload" method="post" id="upload_form" enctype="multipart/form-data" autocomplete="off" class="d-none">
  <input type="file" name="file" id="upload_file" />
</form>
@endsection
@section('pagejs')
@include('admin.shared._summernote')
@include('admin.shared._jquery_validation')
<script type="text/javascript" src="/static/admin/plugins/luck.file.upload.js"></script>
<script type="text/javascript" src="/static/admin/plugins/ejecttime/WdatePicker.js"></script>
<script type="text/javascript" src="/static/admin/script/product.js"></script>
<script type="text/javascript">
$("#form").validate({
  submitHandler: function() {
    layer.confirm('确认提交？', function() {
      layer.closeAll();
      var load = layer.load();
      $("#form").ajaxSubmit(function(res) {
        layer.close(load);
        if (res.code == 200) {
          layer.msg('操作成功', { time: 1500 }, function () { window.location.href = '/admin/product/list'; });
        } else if (res.code == 400) {
          layer.alert(res.message);
          return false;
        } else if (res.code == 401) {
          goLogin();
          return false;
        } else {
          layer.msg('操作失败');
          return false;
        }
      });
    });
  }
});

$(document).ready(function() {
  $('.upload_images .upload').click(function() {
    $('.upload_images .upload').removeClass('current_upload');
    $(this).addClass('current_upload');
    $('#upload_file').click();
  })

  $('#upload_file').change(function(event) {
    var load = layer.load();
    $("#upload_form").ajaxSubmit(function(res) {
      layer.close(load);
      event.target.value = '';
      if (res.code == 200) {
        if ($('.current_upload').parent('.items').hasClass('cover_items')) {
          var input_name = $('.current_upload').parent('.items').data('name');
          $('.current_upload').parent('.items').find('.upload').hide();
          var str = '<div class="image"><img src="'+ res.data.url +'"><i class="iconfont close"></i><input type="hidden" name="' + input_name + '" value="'+ res.data.url +'" /></div>';
        } else {
          var str = '<div class="image"><img src="'+ res.data.url +'"><i class="iconfont close"></i><input type="hidden" name="images[]" value="'+ res.data.url +'" /><input type="hidden" name="image_ids[]" /></div>';
        }

        $(str).insertBefore($('.current_upload').parent('.items').find('.upload'));
        // 限制图片数量
        if ($('.current_upload').parents('.upload_images').find('.image').length >= 50) {
          $('.current_upload').parents('.upload_images').find('.upload').hide();
        }
        $('.upload_images .upload').removeClass('current_upload');
      } else if (res.code == 400) {
        layer.msg(res.message);
      } else if (res.code == 401) {
        goLogin();
       } else {
        layer.msg('上传失败');
      }
    });
  })

  $('.upload_images').on('click', '.close', function() {
    $(this).parents('.upload_images').find('.upload').show();
    $(this).parent('.image').remove();
  })
})
</script>
@endsection

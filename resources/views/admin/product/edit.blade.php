@extends('admin.shared._layout')
@section('pagecss')
<link rel="stylesheet" type="text/css" href="/static/admin/style/product.css" />
@endsection
@section('content')
<div class="main">
  <div class="bg-white p-4">
    <ol class="breadcrumb">
      <li class="breadcrumb-item">商品列表</li>
      <li class="breadcrumb-item active">编辑</li>
    </ol>
    <div class="breadcrumb-stitle">
      <a href="javascript:history.go(-1);"><i class="iconfont goback"></i></a>
      <span class="txt">编辑</span>
    </div>
  </div>
  <ul class="form-message m-4 p-4"></ul>
  <form class="mt-4" id="form" action="/admin/product/update" method="post" autocomplete="off">
    @csrf
    <input type="hidden" name="id" value="{{$product->id}}">
    <input type="hidden" name="category_id" value="">
    <div class="m-4 pagebox">
      <div><b>基础信息</b></div>
      <div class="mt-4">
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end"><span class="text-danger">*</span> 商品分类：</label>
          <div class="col-auto pt-2" id="selected_category_box">
            <span>{{$selected_category}}</span>
            <a class="ms-2" href="javascript:void(0);" onclick="switchCategory('edit')">修改分类</a>
          </div>
          <div class="col-auto none" id="set_category_box">
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
              <a class="ms-2 pt-2" href="javascript:void(0);" onclick="switchCategory('cancel')">取消修改</a>
            </div>
          </div>
        </div>
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end"><span class="text-danger">*</span> 名字：</label>
          <div class="col-8">
            <input class="form-control" name="name" value="{{$product->name}}" placeholder="">
          </div>
        </div>
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">商品封面图：</label>
          <div class="col-auto">
            @if($product && !empty($product->cover))
            <div class="fmr" style="width: 90px; height: 90px;" data-name="cover">
              <i class="fmr_remove iconfont" href="javascript:void(0);"></i>
              <img src="{{$product->cover}}">
              <input type="hidden" name="cover" value="{{$product->cover}}">
            </div>
            @else
            <div class="fmr" style="width: 90px; height: 90px;" data-name="cover"></div>
            @endif
          </div>
        </div>
        <div class="row mb-4 upload_images">
          <label class="col-2 col-form-label text-end">商品轮播图：</label>
          <div class="col-auto items">
            @foreach($product->images as $value)
            <div class="fmr item" style="width: 90px; height: 90px;" data-name="images[]">
              <i class="fmr_remove iconfont" href="javascript:void(0);"></i>
              <img src="{{$value->image}}">
              <input type="hidden" name="images[]" value="{{$value->image}}" />
              <input type="hidden" name="image_ids[]" value="{{$value->id}}">
            </div>
            @endforeach
            @php
              $imageCount = count($product->images);
            @endphp
            @if($imageCount < 5)
            @for($i = 0; $i < (5 - $imageCount); $i++ )
            <div class="fmr item" style="width: 90px; height: 90px;" data-name="images[]"></div>
            @endFor
            @endif
          </div>
        </div>
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">状态：</label>
          <div class="col-auto">
            <select class="form-select" name="status">
              @foreach(Config('common.mall.product_status') as $key => $value)
              <option value="{{$key}}" @if($product->status == $key) selected @endif>{{$value}}</option>
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
              <input class="form-check-input" type="radio" name="specification_type" id="specification_type_1" value="单规格" @if($product->specification_type == '单规格') checked @endif>
              <label class="form-check-label" for="specification_type_1">单规格</label>
            </div>
            <div class="form-check form-check-inline pt-2">
              <input class="form-check-input" type="radio" name="specification_type" id="specification_type_2" value="多规格" @if($product->specification_type == '多规格') checked @endif>
              <label class="form-check-label" for="specification_type_2">多规格</label>
            </div>
          </div>
        </div>
        <div id="specification102" @if($product->specification_type == '单规格') class="none" @endif>
          <div class="row mb-3">
            <label class="col-2 col-form-label text-end">规格组合：</label>
            <div class="col-auto">
              <select class="form-select" name="specification_group_id">
                <option value="">请选择</option>
                @foreach($specification_groups as $value)
                <option value="{{$value->id}}" @if($product->specification_group_id == $value->id) selected @endif>{{$value->name}}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>
        <div id="specification101" @if($product->specification_type == '单规格') class="none" @endif>
          <div class="row mb-3">
            <label class="col-2 col-form-label text-end">选择规格：</label>
            <div class="col-8">
              <div class="specification mt-2" id="specification">
                @if(!empty($specifications))
                <div class="items">
                  @foreach($specifications as $key => $value)
                  <div class="item" data-id="{{$value->id}}" data-name="{{$value->name}}">
                    <div class="stitle">{{$value->name}}</div>
                    <div class="item_list">
                      @if(!empty($value->options))
                      @foreach($value->options as $key_option => $value_option)
                      <div class="il_item" data-id="{{$value_option->id}}" data-option="{{$value_option->option}}" onclick="radioCancelSelected(this);">
                        <i class="iconfont"></i>
                        <span class="txt">{{$value_option->option}}</span>
                      </div>
                      @endforeach
                      @else
                      <div class="msg">暂无选项</div>
                      @endif
                    </div>
                  </div>
                  @endforeach
                  <div class="actionbox"><a class="btn btn-outline-primary" onclick="combinationSpecification();">生成组合</a></div>
                </div>
                @endif
              </div>
            </div>
          </div>
          <div class="row mb-3 specification_group" id="specification_group" @if($product->specification_type == '单规格') class="none" @endif>
            <label class="col-2 col-form-label text-end">规格组合：</label>
            <div class="col-8">
              <div class="mt-2">
                <div class="batch_operation">
                  <input type="text" id="batch_price" value="" style="width: 100px" placeholder="销售价">
                  <input type="text" id="batch_stock" class="ms-2" name="" value="" style="width: 100px" placeholder="库存">
                  <a class="btn btn-outline-primary btn-sm ms-2" onclick="fillData();">批量填充</a>
                  <a class="btn btn-outline-primary btn-sm ms-2" onclick="fillImageData();">填充商品封面图</a>
                </div>
                <table class="table text-center mt-3" id="skus">
                  @if(!empty($product->skus))
                  <tr class="th">
                    @foreach($product->skus[0]->specifications as $key => $value)
                    <td class="dynamic_add" data-id="{{$value->specification_id}}">{{$value->specification_name}}</td>
                    @endforeach
                    <td>图片</td>
                    <td>销售价 <span class="text-danger">*</span></td>
                    <td>库存</td>
                    <td>商家编码</td>
                    <td>排序</td>
                    <td>操作</td>
                  </tr>
                  @foreach($product->skus as $key => $value)
                  <tr class="tr" data-id="{{$value->option_id_connect}}" data-name="{{$value->option_connect}}">
                    @foreach($value->specifications as $k => $v)
                    <td>{{$v->specification_option}}</td>
                    @endforeach
                    <td style="width: 80px">
                      @if(!empty($value->cover))
                      <div class="fmr" style="width: 70px; height: 70px;" data-name="skus[{{$value->option_id_connect}}][cover]" data-url="/admin/upload">
                        <i class="fmr_remove iconfont" href="javascript:void(0);"></i>
                        <img src="{{$value->cover}}">
                        <input type="hidden" name="skus[{{$value->option_id_connect}}][cover]" value="{{$value->cover}}">
                      </div>
                      @else
                      <div class="fmr" style="width: 70px; height: 70px;" data-name="skus[{{$value->option_id_connect}}][cover]" data-url="/admin/upload">
                        <input type="hidden" name="skus[{{$value->option_id_connect}}][cover]" value="">
                      </div>
                      @endif
                    </td>
                    <td><input type="text" name="skus[{{$value->option_id_connect}}][price]" value="{{$value->price}}" style="width: 80px"></td>
                    <td><input type="text" name="skus[{{$value->option_id_connect}}][stock]" value="{{$value->stock}}" style="width: 80px"></td>
                    <td><input type="text" name="skus[{{$value->option_id_connect}}][sku]" value="{{$value->sku}}" style="width: 100px"></td>
                    <td><input type="text" name="skus[{{$value->option_id_connect}}][sort]" value="{{$value->sort}}" style="width: 50px"></td>
                    <td><a class="btn btn-outline-danger btn-sm" onclick="removeSpecification(this);">移除</a></td>
                    <input type="hidden" name="skus[{{$value->option_id_connect}}][id]" value="{{$value->id}}">
                  </tr>
                  @endforeach
                  @endif
                </table>
              </div>
            </div>
          </div>
        </div>
        <div id="specification100" @if($product->specification_type != '单规格') style="display: none" @endif>
          <div class="row mb-3">
            <label class="col-2 col-form-label text-end">价格：</label>
            <div class="col-auto">
              <input class="form-control" name="price" value="{{$product->price}}" placeholder="">
            </div>
          </div>
          <div class="row mb-3">
            <label class="col-2 col-form-label text-end">库存：</label>
            <div class="col-auto">
              <input class="form-control" name="stock" value="{{$product->stock}}" placeholder="">
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="m-4 pagebox">
      <div><b>商品详情</b></div>
      <div class="mt-4">
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">详情：</label>
          <div class="col-8">
            <textarea class="summernote" name="content">{{$product->content}}</textarea>
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
              <option value="{{$value->id}}" @if($product->attribute_group_id == $value->id) selected @endif>{{$value->name}}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div id="attributes">
          @if(!empty($attributes))
          @foreach($attributes as $key => $value)
            @if($value->type == '输入')
            <div class="row mb-3">
              <label class="col-2 col-form-label text-end">
                @if($value->required == '是')
                <span class="text-danger">*</span>
                @endif
                {{$value->name}}：
              </label>
              <div class="col-8">
                <input class="form-control" name="attributes[{{$value->id}}]" value="{{$value->value}}" placeholder="">
              </div>
            </div>
            @endif
            @if($value->type == '选项')
            <div class="row mb-3">
              <label class="col-2 col-form-label text-end">
                @if($value->required == '是')
                <span class="text-danger">*</span>
                @endif
                {{$value->name}}：
              </label>
              <div class="col-auto">
                <select class="form-select" name="attributes[{{$value->id}}]">
                  <option value="">请选择</option>
                  @foreach($value->options as $key_option => $value_option)
                    <option value="{{$value_option->option}}" @if($value_option->option == $value->value) selected @endif>{{$value_option->option}}</option>
                  @endforeach
                </select>
              </div>
            </div>
            @endif
          @endforeach
          @endif
        </div>
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
              <input class="form-check-input" type="radio" name="shipment_time" id="shipment_time_{{$key}}" value="{{$value}}" @if($value == $product->shipment_time) checked @endif>
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
              <input class="form-check-input" type="radio" name="transport_way" id="transport_way_{{$key}}" value="{{$value}}" @if($value == $product->transport_way) checked @endif>
              <label class="form-check-label" for="transport_way_{{$key}}">{{$value}}</label>
            </div>
            @endforeach
          </div>
        </div>
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">包邮：</label>
          <div class="col-auto">
            <select class="form-select" name="free_shipping">
              <option value="1" @if($product->free_shipping == 1) selected @endif>是</option>
              <option value="2" @if($product->free_shipping == 2) selected @endif>否</option>
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
            <input class="form-control" name="seo_title" value="{{$product->seo_title}}" placeholder="">
          </div>
        </div>
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">seo keywords：</label>
          <div class="col-8">
            <input class="form-control" name="seo_keywords" value="{{$product->seo_keywords}}" placeholder="">
          </div>
        </div>
        <div class="row mb-3">
          <label class="col-2 col-form-label text-end">seo description：</label>
          <div class="col-8">
            <input class="form-control" name="seo_description" value="{{$product->seo_description}}" placeholder="">
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
  rules: {
    title: {required: true},
  },
  messages: {
    title: '标题不能为空',
  },
  submitHandler: function() {
    layer.confirm('确认提交？', function() {
      layer.closeAll();
      var load = layer.load();
      $("#form").ajaxSubmit(function(res) {
        layer.close(load);
        if (res.code == 200) {
          layer.msg('操作成功', { time: 1500 }, function () { window.location.href = '/admin/product/list{!! decodePrevPageParams(); !!}'; });
        } else if (res.code == 400) {
          layer.msg(res.message);
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
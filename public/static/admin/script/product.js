// input radio cancel selected
function radioCancelSelected(thisNode) {
  $(thisNode).toggleClass('on').siblings().removeClass('on');
}

// 组合销售规格
function combinationSpecification() {
  if ($('#specification .item').length == 0) {
    layer.msg('生成失败！请检查规格配置是否正确');
    return false;
  }

  // 已生成的销售规格ID
  var specification_id_selected = '';
  $('#skus .th .dynamic_add').each(function(index, element) {
    if (index == 0) {
      specification_id_selected += $(this).data('id');
    } else {
      specification_id_selected += '_' + $(this).data('id');
    }
  })

  // 已生成的销售选项ID
  var specification_option_id_selected = [];
  $('#skus .tr').each(function(index, element) {
    specification_option_id_selected.push($(this).data('id'));
  })

  var th_data = [];
  var tr_data = [];
  // 当前选中销售规格ID
  var specification_id_str = '';
  // 当前选中销售规格选项ID
  var specification_option_id_str = '';
  // 当前选中销售规格选项值
  var specification_option_value_str = '';
  $('#specification .item').each(function() {
    if ($(this).find('.il_item.on').length == 1) {
      data = {};
      data.id = $(this).data('id');
      data.name = $(this).data('name');
      th_data.push(data);
      specification_id_str += $(this).data('id') + '_';
      data = {};
      data.id = $(this).find('.il_item.on').data('id');
      data.name = $(this).find('.il_item.on').data('option');
      tr_data.push(data);
      specification_option_id_str += $(this).find('.il_item.on').data('id') + '_';
      specification_option_value_str += $(this).find('.il_item.on').data('option') + '_';
    }
  })

  if (specification_id_str != '') specification_id_str = specification_id_str.substr(0, specification_id_str.length - 1);
  if (specification_option_id_str != '') specification_option_id_str = specification_option_id_str.substr(0, specification_option_id_str.length - 1);
  if (specification_option_value_str != '') specification_option_value_str = specification_option_value_str.substr(0, specification_option_value_str.length - 1);

  // 检查销售规格
  if (specification_id_selected != '' && specification_id_selected != specification_id_str) {
    layer.msg('销售规格必须一致');
    return false;
  }
  // 检查销售规格选项
  if (specification_option_id_str == '') {
    layer.msg('请选择规格');
    return false;
  }
  if (in_array(specification_option_id_str, specification_option_id_selected)) {
    layer.msg('当前销售规格组已存在');
    return false;
  }

  var tr_str = '';
  tr_str += '<tr class="tr" data-id="' + specification_option_id_str + '" data-name="' + specification_option_value_str + '">';
  for (var i = 0; i < tr_data.length; i++) {
    tr_str += '<td>' + tr_data[i].name + '</td>';
  }
  tr_str += '<td style="width: 80px"><div class="fmr" style="width: 70px; height: 70px;" data-name="skus[' + specification_option_id_str + '][cover]"><input type="hidden" name="skus[' + specification_option_id_str + '][cover]" value=""></div></td>';
  tr_str += '<td><input type="text" name="skus[' + specification_option_id_str + '][price]" style="width: 80px"></td>';
  tr_str += '<td><input type="text" name="skus[' + specification_option_id_str + '][stock]" style="width: 80px"></td>';
  tr_str += '<td><input type="text" name="skus[' + specification_option_id_str + '][sku]" style="width: 100px" placeholder="为空自动生成"></td>';
  tr_str += '<td><input type="text" name="skus[' + specification_option_id_str + '][sort]" style="width: 50px"></td>';
  tr_str += '<td><a class="btn btn-outline-danger btn-sm" onclick="removeSpecification(this);">移除</a></td>';
  tr_str += '</tr>';

  // 显示SKU图片
  // $('#sku_image_switch').show();

  // 检查是否为二次生成
  if ($('#skus .th').length == 0) {
    var th_str = '<tr class="th">';
    for (var i = 0; i < th_data.length; i++) {
      th_str += '<td class="dynamic_add" data-id="' + th_data[i].id + '">' + th_data[i].name + '</td>';
    }
    th_str += '<td>图片</td>';
    th_str += '<td>销售价 <span class="text-danger">*</span></td>';
    th_str += '<td>库存</td>';
    th_str += '<td>商家编码</td>';
    th_str += '<td>排序</td>';
    th_str += '<td>操作</td>';
    th_str += '</tr>';
    th_str += tr_str;
    $('#skus').html(th_str);
    $('#specification_group').show();
  } else {
    //bootstrapConfig();
    $('#skus').append(tr_str);
  }
}

// 移除单个销售规格组
function removeSpecification(thisNode) {
  $(thisNode).parents('.tr').remove();
  var data_id = $(thisNode).parents('.tr').data('id');
}

// 销售规格组填充基础数据
function fillData() {
  let price = $('#batch_price').val();
  let stock = $('#batch_stock').val();
  if ($('#skus .tr').length == 0) return false;
  $('#skus .tr').each(function () {
    $(this).find('input').eq(1).val(price);
    $(this).find('input').eq(2).val(stock);
  })
}

// 填充封面图
function fillImageData() {
  var image = $('input[name="cover"]').val();
  if (image == '') {
    layer.msg('请先上传商品封面图');
    return false;
  }
  if ($('#skus .tr').length == 0) return false;
  $('#skus .tr').each(function () {
    var name = $(this).find('.fmr').eq(0).data('name');
    $(this).find('.fmr').eq(0).addClass('uploaded');
    var str = '';
    str += '<i class="fmr_remove iconfont" href="javascript:void(0);"></i>';
    str += '<img src="' + image + '">';
    str += '<input type="hidden" name="' + name + '" value="' + image + '">';
    $(this).find('.fmr').eq(0).html(str);
  })
}

// 商品规格 单规格/多规格 切换
$('input[name="specification_type"]').click(function() {
  if ($(this).val() == '单规格') {
    $('#specification100').show();
    $('#specification102').hide();
    $('#specification101').hide();
    $('#images_type').hide();
    $("select[name='specification_group_id']").val('');
  }
  if ($(this).val() == '多规格') {
    $('#specification102').show();
    $('#specification100').hide();
    $('#images_type').show();
  }
})

// 商品规格组合
$("select[name='specification_group_id']").change(function() {
    var group_id = $(this).val();
    if (group_id != '') {
      var load = layer.load();
      $.ajax({
        type: 'get',
        url: '/admin/product/getSpecifications?group_id=' + group_id,
        success: function(res) {
          layer.close(load);
          var str = '';
          str += '<div class="items">';
          for (var i = 0; i < res.data.length; i++) {
            str += '<div class="item" data-id="' + res.data[i].id + '" data-name="' + res.data[i].name + '">';
            str += '<div class="stitle">' + res.data[i].name + '</div>';
            str += '<div class="item_list">';
            if (res.data[i].options.length > 0) {
              for (var x = 0; x < res.data[i].options.length; x++) {
                str += '<div class="il_item" data-id="' + res.data[i].options[x].id + '" data-option="' + res.data[i].options[x].option + '" onclick="radioCancelSelected(this);">';
                str += '<i class="iconfont"></i>';
                str += '<span class="txt">' + res.data[i].options[x].option + '</span>';
                str += '</div>';
              }
            } else {
              str += '<div class="msg">暂无选项</div>';
            }
            str += '</div>';
            str += '</div>';
          }
          str += '<div class="actionbox"><a class="btn btn-outline-primary" onclick="combinationSpecification();">生成组合</a></div>';
          $('#specification').html(str);
        }
      })
      $('#specification101').show();
    } else {
      $('#specification101').hide();
    }
    $('#specification_group').hide();
    $('#skus').html('');
});

// 商品属性组合
$("select[name='attribute_group_id']").change(function() {
    var group_id = $(this).val();
    if (group_id != '') {
      var load = layer.load();
      $.ajax({
        type: 'get',
        url: '/admin/product/getAttributes?group_id=' + group_id,
        success: function(res) {
          layer.close(load);
          var str = '';

          for (var i = 0; i < res.data.length; i++) {
            if (res.data[i].type == '输入') {
              str += '<div class="row mb-3">';
              str += '<label class="col-2 col-form-label text-end">';
              if (res.data[i].required == '是') {
                str += '<span class="text-danger">*</span> ';
              }
              str += res.data[i].name + '：';
              str += '</label>';
              str += '<div class="col-8">';
              str += '<input class="form-control" name="attributes[' + res.data[i].id + ']" placeholder="">';
              str += '</div>';
              str += '</div>';
            }
            if (res.data[i].type == '选项') {
              str += '<div class="row mb-3">';
              str += '<label class="col-2 col-form-label text-end">';
              if (res.data[i].required == '是') {
                str += '<span class="text-danger">*</span> ';
              }
              str += res.data[i].name + '：';
              str += '</label>';
              str += '<div class="col-auto">';
              str += '<select class="form-select" name="attributes[' + res.data[i].id + ']">';
              str += '<option value="">请选择</option>';
              for (var x = 0; x < res.data[i].options.length; x++) {
                str += '<option value="'+ res.data[i].options[x].option +'">' + res.data[i].options[x].option + '</option>';
              }
              str += '</select>';
              str += '</div>';
              str += '</div>';
            }
          }
          $('#attributes').html(str);
        }
      })
      $('#attributes').show();
    } else {
      $('#attributes').html('');
      $('#attributes').hide();
    }
});

$('#spc1').on('change', 'select', function() {
  var id = $(this).val();
  $('input[name="category_id"]').val(id);
  $('#spc2').hide();
  $('#spc2').html('');
  $('#spc3').hide();
  $('#spc3').html('');
  if (id != '') {
    var layer_load = layer.load();
    $.ajax({
      url: '/admin/product/getCategorys',
      type: 'get',
      data: {
        parent_id: id
      },
      success: function(data) {
        layer.close(layer_load);
        var str = '<select class="form-select">';
        str += '<option value="">请选择</option>';
        for (var i = data.data.length - 1; i >= 0; i--) {
          str += '<option value="' + data.data[i].id + '">' + data.data[i].name + '</option>';
        }
        str += '</select>';
        $('#spc2').show();
        $('#spc2').html(str);
      }
    })
  }
})

$('#spc2').on('change', 'select', function() {
  var id = $(this).val();
  $('input[name="category_id"]').val(id);
  $('#spc3').hide();
  $('#spc3').html('');
  if (id != '') {
    var layer_load = layer.load();
    $.ajax({
      url: '/admin/product/getCategorys',
      type: 'get',
      data: {
        parent_id: id
      },
      success: function(data) {
        layer.close(layer_load);
        var str = '<select class="form-select">';
        str += '<option value="">请选择</option>';
        for (var i = data.data.length - 1; i >= 0; i--) {
          str += '<option value="' + data.data[i].id + '">' + data.data[i].name + '</option>';
        }
        str += '</select>';
        $('#spc3').show();
        $('#spc3').html(str);
      }
    })
  }
})

$('#spc3').on('change', 'select', function() {
  var id = $(this).val();
  $('input[name="category_id"]').val(id);
})

function switchCategory(type) {
  if (type == 'edit') {
    $('#selected_category_box').hide();
    $('#set_category_box').show();
  }
  if (type == 'cancel') {
    $('#selected_category_box').show();
    $('#set_category_box').hide();
    $('input[name="category_id"]').val('');
  }
}
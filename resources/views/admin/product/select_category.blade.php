@extends('admin.shared._layout')
@section('pagecss')
<style type="text/css">
ul, ol {
  padding-left: 0;
}
.select-product-category {}
.spc-step {
  margin-bottom: 20px;
  overflow: hidden;
}
.spc-step li {
  color: #CCC;
  float: left;
  font-size: 20px;
  padding: 10px;
}
.spc-step li.current {
  color: #27A9E3;
}
.spc-step li i.icon {
  font-size: 20px;
}
.spc-categorys {
  background-color: #FAFAFA;
  height: 310px;
  padding: 25px;
  margin: 10px auto;
  border: solid 1px #E6E6E6;
  overflow: hidden;
}
.spc-categorys .item {
  background: #FFF;
  vertical-align: top;
  letter-spacing: normal;
  word-spacing: normal;
  display: inline-block;
  margin-right: 15px;
  border: solid 1px #E6E6E6;
  width: 280px;
  height: 260px;
  padding: 8px;
  overflow: auto;
}
.spc-categorys .item.on {
    background-color: #eee;
}
.spc-categorys .item ul li {
  position: relative;
  font-size: 14px;
  color: #666;
  display: block;
  padding: 4px 8px;
  margin: 1px;
  overflow: hidden;
  cursor: pointer;
  white-space: nowrap !important;
  overflow: hidden !important;
  text-overflow: ellipsis !important;
  -webkit-box-orient: vertical;
}
.spc-categorys .item ul li.on {
  color: #3A87AD;
  background-color: #D9EDF7;
}
.spc-categorys .item ul li.on:after {
  float: right;
  font-size: 10px;
  position: absolute;
  right: 2px;
  top: 6px;
  content: "\e79b";
}
.spc-message {
  color: #C09853;
  background-color: #FCF8E3;
  padding: 8px 35px 8px 14px;
  margin: 10px auto;
  border: 1px solid #FBEED5;
  text-shadow: 0 1px 0 rgba(255, 255, 255, 0.5);
  font-size: 12px;
}
.spc-btn {
  text-align: center;
  margin-top: 20px;
}
.spc-btn button {
  background-color: #48CFAE;
  color: #FFF;
  padding: 8px 20px;
  border-radius: 3px;
  border: none 0;
  cursor: pointer;
}
.spc-btn.on button {
  background-color: #ccc;
  cursor: default;
}
</style>
@endsection
@section('content')
<div class="main">
  <div class="bg-white p-4">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="/admin/product/list">商品列表</a></li>
      <li class="breadcrumb-item active">选择分类</li>
    </ol>
    <div class="breadcrumb-stitle">
      <a href="javascript:history.go(-1);"><i class="iconfont goback"></i></a>
      <span class="txt">选择分类</span>
    </div>
  </div>
  <div class="pagebox m-4">
    <div class="select-product-category">
      <ul class="spc-step">
        <li class="current">
          <i class="icon iconfont">&#xe638;</i> 选择分类 <i class="iconfont arrow">&#xe79b;</i>
        </li>
        <li>
          <i class="icon iconfont">&#xe605;</i> 填写产品详情 <i class="iconfont arrow">&#xe79b;</i>
        </li>
        <li>
          <i class="icon iconfont">&#xe658;</i> 产品发布成功</i>
        </li>
      </ul>
      <div class="spc-categorys">
        <div class="item" id="spc1">
          <ul>
            @foreach($categorys as $value)
            <li class="iconfont" data-id="{{$value->id}}">{{$value->name}}</li>
            @endforeach
          </ul>
        </div>
        <div class="item on" id="spc2"></div>
        <div class="item on" id="spc3"></div>
      </div>
      <div class="spc-message">请选择产品分类</div>
      <div class="spc-btn on"><button>下一步，填写产品信息</button></div>
      <input type="hidden" name="selected_category_id">
    </div>
  </div>
</div>
@endsection
@section('pagejs')
<script type="text/javascript">
$('#spc1 li').click(function() {
  resetSpc(1);
  $(this).addClass('on');
  $('input[name="selected_category_id"]').val($(this).attr('data-id'));
  setSpcMessage();
  var layer_load = layer.load();
  $.ajax({
    url: '/admin/product/getCategorys',
    type: 'get',
    data: {
      parent_id: $(this).attr('data-id')
    },
    success: function(data) {
      layer.close(layer_load);
      var str = '<ul>';
      for (var i = data.data.length - 1; i >= 0; i--) {
        str += '<li class="iconfont" data-id="' + data.data[i].id + '">' + data.data[i].name + '</li>';
      }
      str += '</ul>';
      $('#spc2').html(str);
      $('#spc2').removeClass('on');
    }
  })
})

$('#spc2').on('click', 'li', function() {
  resetSpc(2);
  $(this).addClass('on');
  $('input[name="selected_category_id"]').val($(this).attr('data-id'));
  setSpcMessage();
  var layer_load = layer.load();
  $.ajax({
    url: '/admin/product/getCategorys',
    type: 'get',
    data: {
      parent_id: $(this).attr('data-id')
    },
    success: function(data) {
      layer.close(layer_load);
      var str = '<ul>';
      for (var i = data.data.length - 1; i >= 0; i--) {
        str += '<li class="iconfont" data-id="' + data.data[i].id + '">' + data.data[i].name + '</li>';
      }
      str += '</ul>';
      $('#spc3').html(str);
      $('#spc3').removeClass('on');
    }
  })
})

$('#spc3').on('click', 'li', function() {
  resetSpc(3);
  $(this).addClass('on');
  setSpcMessage();
  $('input[name="selected_category_id"]').val($(this).attr('data-id'));
})

function resetSpc(ident) {
  if (ident == 1) {
    $('#spc1 li').removeClass('on');
    $('#spc2').html('');
    $('#spc3').html('');
    $('#spc3').addClass('on');
    $('.spc-btn').removeClass('on');
    $('.spc-btn').attr('onClick', 'createSelectCategory()');
  }
  if (ident == 2) {
    $('#spc2 li').removeClass('on');
    $('#spc3').html('');
  }
  if (ident == 3) {
    $('#spc3 li').removeClass('on');
  }
}

function setSpcMessage() {
  var str = '';
  str += '您当前选择的产品分类是：'+$('#spc1 li.on').html();
  if ($('#spc2 li.on').html() != undefined) str += ' > '+$('#spc2 li.on').html();
  if ($('#spc3 li.on').html() != undefined) str += ' > '+$('#spc3 li.on').html();
  $('.spc-message').html(str);
}

function createSelectCategory() {
  window.location.href = '/admin/product/create?category_id='+$('input[name="selected_category_id"]').val();
}
</script>
@endsection
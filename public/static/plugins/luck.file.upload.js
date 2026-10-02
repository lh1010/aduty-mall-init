var default_upload_img = '../images/icon_upload.png';

$('body').on('click', '.luckFU', function() {
  luckFU();
})

function luckFU() {
	if ($("#luckFU_form").length > 0) $("#luckFU_form").remove();
	var thisNode = $(event.target);
	if (!thisNode.hasClass('luckFU')) thisNode = $(event.target).parent('.luckFU');
	var url = thisNode.attr('data-url');
	var name = thisNode.attr('data-name') == undefined ? 'file' : thisNode.attr('data-name');
  var str = '';
	str += '<form action="' + url + '" method="post" id="luckFU_form" enctype="multipart/form-data" style="display:none" >'
	str += '<input type="file" name="file" id="luckFU_file" />'
	str += '</form>'
	$(document.body).append(str);
	$("#luckFU_file").click();

	$("#luckFU_file").change(function() {
		var load = layer.load();
		$("#luckFU_form").ajaxSubmit(function(data) {
			layer.close(load);
			if (data.code == 401) {
          goLogin();
      } else if (data.code == 200) {
      	var str = '';
      	str += '<i class="luckFU_remove iconfont" href="javascript:void(0);"></i>';
    		str += '<img src="' + data.data.url + '" />';
    		str += '<input type="hidden" name="' + name + '" value="' + data.data.url + '" />';
    		thisNode.html(str);
    		thisNode.addClass('uploaded');
      } else if (data.code == 400) {
          layer.msg(data.message);
      } else {
          layer.msg('上传失败');
      }
		});
	})
}

$('body').on('click', '.luckFU_remove', function() {
	event.stopPropagation();
	luckFU_delImage();
	return false;
})

function luckFU_delImage() {
	var str = '';
    var thisNode = $(event.target);
    if (!thisNode.hasClass('luckFU_remove')) {
        thisNode = thisNode.closest('.luckFU_remove');
    }
    var parent = thisNode.parent();
    if (parent.find("input[type=hidden]").length > 0) {
        str += '<input type="hidden" name="' + parent.find("input[type=hidden]").attr('name') + '" value=""/>';
    }
    parent.removeClass('uploaded');
    parent.html(str);
    return false;
}

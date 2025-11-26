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
	var user_token = $('#user_token').val();
  var str = '';
	str += '<form action="' + url + '" method="post" id="luckFU_form" enctype="multipart/form-data" style="display:none" >'
	str += '<input type="hidden" name="user_token" value="' + user_token + '" />'
	str += '<input type="file" name="file" id="luckFU_file" />'
	str += '</form>'
	$(document.body).append(str);
	$("#luckFU_file").click();

	$("#luckFU_file").change(function() {
		var load = layer.load();
		$("#luckFU_form").ajaxSubmit(function(res) {
			layer.close(load);
			if (res.code == 401) {
        goLogin();
      } else if (res.code == 200) {
      	var str = '';
      	str += '<i class="luckFU_remove iconfont" href="javascript:void(0);"></i>';
    		str += '<img src="' + res.data.url + '" />';
    		str += '<input type="hidden" name="' + name + '" value="' + res.data.url + '" />';
    		thisNode.html(str);
    		thisNode.addClass('uploaded');
      } else if (res.code == 400) {
        layer.msg(res.message);
      } else {
        layer.msg('上传失败');
      }
		});
	})
}

$('.luckFU').on('click', '.luckFU_remove', function() {
	luckFU_delImage();
})

function luckFU_delImage() {
	var str = '';
	var thisNode = $(event.target);
	if (thisNode.parent().find("input[type=hidden]").length > 0) {
		str += '<input type="hidden" name="' + thisNode.parent().find("input[type=hidden]").attr('name') + '" value=""/>';
	}
	thisNode.parent().removeClass('uploaded');
	thisNode.parent().html(str);
	event.stopPropagation();
	return false;
}
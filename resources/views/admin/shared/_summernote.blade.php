<link rel="stylesheet" href="/static/admin/plugins/summernote/summernote-lite.css">
<link rel="stylesheet" href="/static/admin/plugins/summernote/summernote-bs5.min.css">
<script type="text/javascript" src="/static/admin/plugins/summernote/summernote-lite.js"></script>
<script type="text/javascript" src="/static/admin/plugins/summernote/lang/summernote-zh-CN.js"></script>
<script type="text/javascript">
$(document).ready(function() {
  $('.summernote').summernote({
  	placeholder: '输入内容......',
  	lang:'zh-CN',
  	toolbar: [
  		['style', ['style']],
	    ['style', ['bold', 'italic', 'underline', 'clear']],
	    ['font', ['strikethrough', 'superscript', 'subscript']],
	    ['fontsize', ['fontsize']],
	    ['para', ['ul', 'ol', 'paragraph']],
	    ['insert', ['link']],
	    ['mypicture', ['mypicture']],
	    ['view', ['fullscreen', 'codeview']]
		],
		height: 300,
    	fontSizes: ['8', '9', '10', '11', '12', '14', '18', '24', '36'],
		buttons: {
		  mypicture: mypicture
		}
  });

  $('span.note-icon-caret').remove();
  $('.note-editable').css('background', '#fff');
});

var mypicture = function (context) {
  var ui = $.summernote.ui;
  var button = ui.button({
    contents: '<i class="note-icon-picture"/>',
    tooltip: '插入图片',
    click: function () {
      // 直接上传
      //$('#upload_file').click();

      // 图片空间
      $(this).parents('.note-editor').prev('.summernote').addClass('summernote_checked');
      layer.open({
          type: 2,
          title: '图片空间',
          area: ['827px', '485px'],
          content: ['/admin/freeAccess/file/manager?use_ident=summernote', 'no'],
      });
    }
  });
  return button.render();
}	
</script>
<form action="/admin/file/upload" method="post" enctype="multipart/form-data" style="display:none" id="upload_form" autocomplete="off">
  <input type="file" name="file" id="upload_file" />
</form>
<script type="text/javascript">
$('#upload_file').change(function() {
  $('#upload_form').ajaxSubmit(function(res) {
    $('.summernote').summernote('insertImage', res.data.path);
  })
})
</script>

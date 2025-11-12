<link rel="stylesheet" type="text/css" href="/static/admin/plugins/editor.md/css/editormd.css" />
<script type="text/javascript" src="/static/admin/plugins/editor.md/editormd.min.js"></script>
<script type="text/javascript">
var editor;
$(document).ready(function(){
  editor = editormd("editormd", {
    width: "100%",
    height: "400px",
    path: "/static/admin/plugins/editor.md/lib/",
    saveHTMLToTextarea: true,
    htmlDecode: "script,iframe",
    watch: false,
    toolbarIcons : function() {
      return [
        "undo",
        "redo",
        "|",
        "bold",
        "del",
        "italic",
        "quote",
        "h1",
        "h2",
        "h3",
        "h4",
        "h5",
        "|",
        "list-ul",
        "list-ol",
        "hr",
        "|",
        "link",
        "image",
        //"mypicture",
        "code",
        "code-block",
        "||",
        "watch",
        "fullscreen",
        "preview"
      ]
    },
    imageUpload: true,
    imageFormats: ["jpg", "jpeg", "gif", "png", "bmp", "webp"],
    imageUploadURL: "/admin/upload/editormd",

    // toolbarIconsClass: {
    //   mypicture: "fa-image"
    // },
    // toolbarHandlers: {
    // 	mypicture: function(cm, icon, cursor, selection) {
    //     $('#upload_file').click();		
    //   },
    // }
  });
});
</script>

<!-- <form action="/admin/upload" method="post" enctype="multipart/form-data" style="display:none" id="upload_form">
  <input type="file" name="file" id="upload_file" />
</form> -->

<script type="text/javascript">
// $('#upload_file').change(function() {
//   var load = layer.load();
//   $('#upload_form').ajaxSubmit(function(res) {
//     layer.close(load);
//     var str = '![](' + res.data.path + ')';
//     editor.setMarkdown(editor.getMarkdown() + str);
//   })
// })
</script>
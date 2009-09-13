{literal}
$(document).ready(function(){
  /* auto-hide the image/file upload fields */
  $('#file2').parent().hide();
  $('#file3').parent().hide();
  $('#file4').parent().hide();
  $('#file-upload-2').parent().hide();
  $('#file-upload-3').parent().hide();
  $('#file-upload-4').parent().hide();
  
  /* show the next upload field */
  $('#file1').change(function(){$('#file2').parent().show();});
  $('#file2').change(function(){$('#file3').parent().show();});
  $('#file3').change(function(){$('#file4').parent().show();});
  $('#file-upload-1').change(function(){$('#file-upload-2').parent().show();});
  $('#file-upload-2').change(function(){$('#file-upload-3').parent().show();});
  $('#file-upload-3').change(function(){$('#file-upload-4').parent().show();});
  
  /* hide the new topic form */
  $('#post_reply').before('<button class="button" id="show_post_reply">Post Reply</button>');
  $('#post_reply').hide();
  $('#show_post_reply').click(function(){
    $('#post_reply').show('fast');
    $(this).hide();
  });
  $('.post_reply_link').click(function(){
    $('#post_reply').show();
  });
});

{/literal}
{include file='jojo_forum_post_reply_error_checking.tpl'}
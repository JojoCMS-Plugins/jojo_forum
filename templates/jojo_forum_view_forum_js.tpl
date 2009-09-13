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
  $('#new_topic').before('<button class="button" id="show_new_topic">New Topic</button>');
  $('#new_topic').hide();
  $('#show_new_topic').click(function(){
    $('#new_topic').show('fast');
    $(this).hide();
  });
});

{/literal}
{include file='jojo_forum_new_topic_error_checking.tpl'}
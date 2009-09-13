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
});

function editPostErrorChecking()
{
  var errors=new Array();
  var i=0;
  
  if ($('#post').val() == '') {
    errors[i++]='Post is a required field';
  }

  if (errors.length==0) {
    return(true);
  } else {
    alert(errors.join("\n"));
    return(false);
  }

}
{/literal}
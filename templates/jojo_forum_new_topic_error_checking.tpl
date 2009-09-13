{literal}
function newTopicErrorChecking()
{
  var errors=new Array();
  var i=0;
  
  if ($('#subject').val() == '') {
    errors[i++]='Subject is a required field';
  }
  
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
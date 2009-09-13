<form action="{$RELATIVE_URL}" method="post" name="editform" enctype="multipart/form-data" onsubmit="return checkForm(this)">
<input type="hidden"  name="action" value="edittopic" />
<input type="hidden"  name="forumid" value="{$topic.ft_forumid}" />
<input type="hidden"  name="topicid" value="{$topic.forumtopicid}" />

<div class="post">
<p>Topic Title:<br />
  <input type="text" name="topictitle" size="40" id="topictitle" value="{$topic.ft_title|escape:"htmlall"}" /></p>
 <p>Search Engine Title:<br />
  <input type="text" name="seotitle" size="40" id="seotitle" value="{$topic.ft_seotitle|escape:"htmlall"}" /></p>
 {if $is_moderator}<label><input type="checkbox" name="sticky" id="sticky" value="sticky" title="Sticky topics will stay at the top of the list"{if $topic.ft_sticky=='yes'} checked="checked"{/if} /> Make this topic sticky</label><br />{/if}
<p><input class="button" type="submit" name="btn_save" value="Save Changes" /></p>
</div>

</form>
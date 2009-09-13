<form action="{$RELATIVE_URL}" method="post" name="editform" enctype="multipart/form-data" onsubmit="return checkForm(this)">
  <input type="hidden" name="MAX_FILE_SIZE" value="2000000" />
  <input type="hidden"  name="action" value="editpost" />
  <input type="hidden"  name="topicid" value="{$post.fp_topicid}" />
  <input type="hidden"  name="postid" value="{$post.forumpostid}" />
  
  <div id="edit_post">
    <label for="post">Post:</label><br />
    <textarea rows="10" cols="50" name="post" id="post">{$post.fp_bbbody|escape:"htmlall"}</textarea>
    <br />
    <input class="button" type="submit" name="btn_save" value="Save Changes" />
    <div id="attachmentImages">
      <h3>Attach Images</h3>
      <div><input type="file" name="file1" id="file1" size="" value=""></div>
      <div><input type="file" name="file2" id="file2" size="" value=""></div>
      <div><input type="file" name="file3" id="file3" size="" value=""></div>
      <div><input type="file" name="file4" id="file4" size="" value=""></div>
      <em>Up to 4 images can be attached at a time to each post, max size 1 megabyte</em>
      {if $post.images}
      <h3>Remove Images</h3>
      Use the checkboxes next to each image to delete<br />
      {section name=i loop=$post.images}
      	<input type="checkbox" name="deleteimage[]" value="{$post.images[i]|escape:"htmlall"}" /> Delete {$post.images[i]}<br />
      	<img class="boxed" src="images/h60/forum-images/{$post.forumpostid}/{$post.images[i]}" alt="" /> <br />
      {/section}
      {/if}
    </div>
  
    <div id="attachmentFiles">
      <h3>Attach Files</h3>
      <div><input type="file" name="file-upload-1" id="file-upload-1" size="" value=""></div>
      <div><input type="file" name="file-upload-2" id="file-upload-2" size="" value=""></div>
      <div><input type="file" name="file-upload-3" id="file-upload-3" size="" value=""></div>
      <div><input type="file" name="file-upload-4" id="file-upload-4" size="" value=""></div>
      <em>Up to 4 files can be attached at a time to each post, max size 1 megabyte. Please contact an admin if you need to attach a file larger than 1Mb</em>
      {if $post.files}
      <h3>Remove Files</h3>
      Use the checkboxes next to each image to delete<br />
      {section name=i loop=$post.files}
      	<input type="checkbox" name="deletefile[]" value="{$post.files[i]|escape:"htmlall"}" /> Delete <a href="downloads/forum-files/{$post.forumpostid}/{$post.files[i]}">{$post.files[i]}</a><br />
      {/section}
      {/if}
    </div>
    <div class="clear"></div>
</div>


</form>
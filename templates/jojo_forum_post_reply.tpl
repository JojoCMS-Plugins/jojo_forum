{if $topic.ft_locked == "yes"}
    <div style="text-align: center"><img src="images/cms/icons/lock.png" alt="This topic is locked" /> This topic is locked, no replies can be posted</div>

{elseif $userid || $OPTIONS.forum_allow_guest_posts=='yes'}
    <!-- [Post Reply] -->
    <div id="post_reply">
      <form action="{$prefix}/reply/{$topic.forumtopicid}/" method="post" name="editform" enctype="multipart/form-data" onsubmit="return postReplyErrorChecking();">
      <a name="reply"></a>
      <h2>Post Reply</h2>
    
      <input type="hidden" name="MAX_FILE_SIZE" value="1000000" />
      <input type="hidden" name="action" value="postreply" />
      <input type="hidden" name="topicid" value="{$topic.forumtopicid}" />
      
      {if !$userid}
        <p>You are not currently logged in, however guest posts <strong>are</strong> allowed on this forum. If you already have a user account, please <a href="{if $issecure}{$SECUREEURL}{else}{$SITEURL}{/if}/login/{$RELATIVE_URL}" rel="nofollow">Log In</a> first. New users can <a href="register/{if $RELATIVE_URL}{$RELATIVE_URL}{else}{$pg_url}/{/if}" title="Register for Forums" rel="nofollow">Register</a> for an account, or post as a 'guest' below.</p>
        <label for="name">Name:</label><br />
        <input type="text" size="30" name="name" id="name" value="{$smarty.post.name}" /> *<br />
      {/if}
      <label for="post">Post:</label><br />
      <textarea rows="10" cols="50" name="post" id="post">{$smarty.post.post}</textarea><br /><br />
      {if $userid}<input type="checkbox" name="subscribe" id="subscribe" value="subscribe" checked="checked" /> <label for="subscribe">Subscribe to email updates on this topic.</label>{/if}
      <br />
      
      {* CAPTCHA is always present for guest posts *}
      {if !$userid}
      <label for="captchacode">Are you Human?</label><br />
        <img src="external/php-captcha/visual-captcha.php" width="200" height="60" alt="Visual CAPTCHA" /><br />
        <label for="captchacode">Enter code shown above</label><br />
        <input type="text" size="8" name="captchacode" id="captchacode" value="{$captchacode}" /> *<br />
        <em>Code is not case-sensitive</em><br />
      {/if}
    
      <input type="submit" class="button" name="btn_newtopic" value="Submit" />
    
      <div id="attachmentImages">
        <h3>Attach Images</h3>
        <div><input type="file" name="file1" id="file1" size="" value=""></div>
        <div><input type="file" name="file2" id="file2" size="" value=""></div>
        <div><input type="file" name="file3" id="file3" size="" value=""></div>
        <div><input type="file" name="file4" id="file4" size="" value=""></div>
        <em>Up to 4 images can be attached to each post, max size 1 megabyte</em>
      </div>
  
      <div id="attachmentFiles">
        <h3>Attach Files</h3>
        <div><input type="file" name="file-upload-1" id="file-upload-1" size="" value=""></div>
        <div><input type="file" name="file-upload-2" id="file-upload-2" size="" value=""></div>
        <div><input type="file" name="file-upload-3" id="file-upload-3" size="" value=""></div>
        <div><input type="file" name="file-upload-4" id="file-upload-4" size="" value=""></div>
        <p><em>Up to 4 files can be attached at a time to each post, max size 1 megabyte. Please contact an admin if you need to attach a file larger than 1Mb</em></p>
      </div>
    </form>
    </div>

{else}
  You must be logged in to post a reply
{/if}
{if $userid || $OPTIONS.forum_allow_guest_posts=='yes'}
<!-- [New Topic] -->
<a name="new-topic"></a>
<div id="new_topic">
<h2>New Topic</h2>
  <form method="post" name="editform" action="{$prefix}/new/{$forum.forumid}/" enctype="multipart/form-data" onsubmit="return newTopicErrorChecking();" >
  <input type="hidden"  name="action" value="newtopic" />
  <input type="hidden"  name="forumid" value="{$forum.forumid}" />

    {if !$userid}
    <p>You are not currently logged in, however guest posts <strong>are</strong> allowed on this forum. If you already have a user account, please <a href="{if $issecure}{$SECUREEURL}{else}{$SITEURL}{/if}/login/{$RELATIVE_URL}" rel="nofollow">Log In</a> first. New users can <a href="register/{if $RELATIVE_URL}{$RELATIVE_URL}{else}{$pg_url}/{/if}" title="Register for Forums" rel="nofollow">Register</a> for an account, or post as a 'guest' below.</p>
    <label>Name:</label><br />
    <input type="text" size="30" name="name" id="name" value="{$smarty.post.name}" /> *<br />
    {/if}
    <label>Subject:</label><br />
    <input type="text" size="30" name="subject" id="subject" value="{$smarty.post.subject}" /> *<br />
    <label>Post:</label><br />
    <textarea rows="10" cols="50" name="post" id="post">{$smarty.post.post}</textarea>
    <br />
    {if $is_moderator}<label><input type="checkbox" name="sticky" id="sticky" value="sticky" title="Sticky topics will stay at the top of the list" /> Make this topic sticky</label><br />{/if}
    
    {* CAPTCHA is always present for guest posts *}
    {if !$userid}
    <label for="captchacode">Are you Human?</label><br />
      <img src="external/php-captcha/visual-captcha.php" width="200" height="60" alt="Visual CAPTCHA" /><br />
      <label for="captchacode">Enter code shown above</label><br />
      <input type="text" size="8" name="captchacode" id="captchacode" value="{$captchacode}" /> *<br />
      <em>Code is not case-sensitive</em><br />
    {/if}
    
    <input class="button" type="submit" name="btn_newtopic" value="Submit" />

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
  You must be <a href="{if $issecure}{$SECUREEURL}{else}{$SITEURL}{/if}/login/{$RELATIVE_URL}" rel="nofollow">logged in</a> to post a new topic.
{/if}
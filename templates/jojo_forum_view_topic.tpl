{if $posts}

{$intro}<br /><br />

<!-- [Topic Navigation] -->
<table width="100%">
  <tr>
    <td class="forum-back-nav"><a href="forums/"><img class="icon" src="images/cms/icons/application_home.png" alt="" /></a> <a href="forums/">Back to Forum Index</a> : <a href="{$forum.url}">Back to {$forum.fm_name}</a> {if $userid}: <a href="{$RELATIVE_URL}#reply" class="post_reply_link" title="Reply to this topic">Reply</a>{/if}</td>
    <td class="pagination">{strip}
{if $pagination}Page {$pagination}{else}&nbsp;{/if}
{if $username}
{if $subscribed}
<a href="{$RELATIVE_URL}unsubscribe/" title="Unsubscribe from email updates to this topic" rel="nofollow"><img class="icon" src="images/cms/icons/email_delete.png" alt="Unsubscribe" /></a>
{else}
<a href="{$RELATIVE_URL}subscribe/" title="Subscribe to email updates to this topic" rel="nofollow"><img class="icon" src="images/cms/icons/email_add.png" alt="Subscribe" /></a>
{/if}
{/if}
&nbsp;<a href="{$RELATIVE_URL}rss/" title="RSS feed for this topic" rel="nofollow"><img class="icon" src="images/cms/icons/feed.png" alt="RSS" /></a>
{/strip}</td>
  </tr>
</table>

<div class="posts">
{foreach from=$posts item=post}
  <!-- [Post by {$post.author|escape:"htmlall"}] -->
  <div class="post {cycle values="row1,row2"}">
  <a name="{$post.forumpostid}"></a>
    <div class="info">
      <strong class="username">{if $post.authorurl}<a href="{$post.authorurl}">{/if}{$post.author|escape:"htmlall"}{if !$post.authorid && ($post.author != 'Guest')} (guest){/if}{if $post.authorurl}</a>{/if}</strong>
      {if $post.authortagline}<p class="tagline">{$post.authortagline|escape:"htmlall"}</p>{/if}
      {if $post.authoravatar}<img src="{if $post.animated}downloads/users/{$post.authoravatar}{else}images/w75/users/{$post.authoravatar}{/if}" alt="{$post.author|escape:"htmlall"}" title="{$post.author|escape:"htmlall"}" />{/if}
      <p><span title="{$post.fp_datetime|date_format:"%A, %e %B %Y %I:%M%p"}">{$post.postdate}</span><br />
      Posts: {$post.authornumposts}</p>
    </div>

    <div class="body">
      <div id="body_{$post.forumpostid}">
      {$post.body}
      </div>
      {if $post.images}
      <div class="forum-images">
      {$post.imagelayout}
      {foreach from=$post.images2 item=image}
        <a href="images/600/forum-images/{$post.forumpostid}/{$images}" rel="lightbox" onclick="return false;"><img class="boxed" src="images/h150/forum-images/{$post.forumpostid}/{$images}" alt="" /></a>
      {/foreach}
      </div>
      {/if}
      {if $post.files}
      <h4>Attached Files</h4>
      <ul class="attachments">
      {foreach from=$post.filesdata item=file}
        <li style="background-image: url('{$file.logo}');"><a href="downloads/forum-files/{$post.forumpostid}/{$file.file}">{$file.file}</a> ({$file.size})</li>
      {/foreach}
      </ul>
      {/if}
      {if $post.authorsignature}<div class="signature">{$post.authorsignature}</div>{/if}
      <div id="source_{$post.forumpostid}" style="display: none;">{$post.fp_bbbody|escape:"htmlall"}</div>
    </div>

    <div class="options">
        {if $userid}
        <a href="{$RELATIVE_URL}#reply" class="post_reply_link" title="Reply Quoting this post" rel="nofollow" onclick="document.getElementById('post').value = '[quote={$post.author|escape:"htmlall"}]'+document.getElementById('source_{$post.forumpostid}').innerHTML+'[/quote]\n';"><img class="icon" src="images/cms/icons/comments.png" alt="Quote" title="Reply quoting this post" /></a>
        {/if}
        {if $group_admin || $group_moderator || ($userid == $post.fp_posterid && $userid > 0)}
        <a href="edit-post/{$post.forumpostid}/index/" title="Edit this Post" rel="nofollow"><img class="icon" src="images/cms/icons/comment_edit.png" alt="Edit Post" title="Edit this post or add images / attachments" /></a>
        {/if}
        {if $group_admin}
        <a href="delete-post/{$post.forumpostid}/index/" title="Delete this post" rel="nofollow" onclick="return confirmdelete();"><img class="icon" src="images/cms/icons/delete.png" alt="Delete" title="Delete this post" /></a>
        {/if}
        {if $group_admin && $post.fp_ip}
        <a href="#" rel="nofollow" title="IP Address Logged" onclick="alert('IP address: {$post.fp_ip}'); return false;"><img class="icon" src="images/cms/icons/transmit_blue.png" alt="IP Address" title="View the IP Address for this post" /></a>
        {/if}
        </div>

  </div>
{/foreach}
</div>

<!-- [Topic Navigation] -->
<table width="100%">
  <tr>
    <td class="forum-back-nav"><a href="forums/">Back to Forum Index</a> : <a href="{$forum.url}">Back to {$forum.fm_name}</a></td>
    <td class="pagination">
{strip}
    {if $pagination}Page {$pagination}{else}&nbsp;{/if}
    {if $username}
        {if $subscribed}
            <a href="{$RELATIVE_URL}unsubscribe/" title="Unsubscribe from email updates to this topic" rel="nofollow"><img class="icon" src="images/cms/icons/email_delete.png" alt="Unsubscribe" /></a>
        {else}
            <a href="{$RELATIVE_URL}subscribe/" title="Subscribe to email updates to this topic" rel="nofollow"><img class="icon" src="images/cms/icons/email_add.png" alt="Subscribe" /></a>
        {/if}
    {/if}
    &nbsp;<a href="{$RELATIVE_URL}rss/" title="RSS feed for this topic" rel="nofollow"><img class="icon" src="images/cms/icons/feed.png" alt="RSS" /></a>
{/strip}</td>
  </tr>
</table>
{strip}
    {if $username}
        <p>
        {if $subscribed}
            You will receive email updates to this topic - <a href="{$RELATIVE_URL}unsubscribe/" rel="nofollow">unsubscribe</a>.
        {else}
            Subscribe to email updates to this topic - <a href="{$RELATIVE_URL}subscribe/" rel="nofollow">subscribe</a>.
        {/if}
        </p>
    {/if}
{/strip}
{/if}

{include file='jojo_forum_post_reply.tpl'}

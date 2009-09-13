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
{section name=p loop=$posts}
  <!-- [Post by {$posts[p].author|escape:"htmlall"}] -->
  <div class="post {cycle values="row1,row2"}">
  <a name="{$posts[p].forumpostid}"></a>
    <div class="info">
      <strong>{if $posts[p].authorurl}<a href="{$posts[p].authorurl}">{/if}{$posts[p].author|escape:"htmlall"}{if !$posts[p].authorid && ($posts[p].author != 'Guest')} (guest){/if}{if $posts[p].authorurl}</a>{/if}</strong>
      {if $posts[p].authortagline}<p class="tagline">{$posts[p].authortagline|escape:"htmlall"}</p>{/if}
      {if $posts[p].authoravatar}<img src="{if $posts[p].animated}downloads/users/{$posts[p].authoravatar}{else}images/w75/users/{$posts[p].authoravatar}{/if}" alt="{$posts[p].author|escape:"htmlall"}" title="{$posts[p].author|escape:"htmlall"}" />{/if}
      <p><span title="{$posts[p].fp_datetime|date_format:"%A, %e %B %Y %I:%M%p"}">{$posts[p].postdate}</span><br />
      Posts: {$posts[p].authornumposts}</p>
    </div>

    <div class="body">
      <div id="body_{$posts[p].forumpostid}">
      {$posts[p].body}
      </div>
      {if $posts[p].images}
      <div class="forum-images">
      {$posts[p].imagelayout}
      {section name=i loop=$posts[p].images2}
        <a href="images/600/forum-images/{$posts[p].forumpostid}/{$posts[p].images[i]}" rel="lightbox" onclick="return false;"><img class="boxed" src="images/h150/forum-images/{$posts[p].forumpostid}/{$posts[p].images[i]}" alt="" /></a>
      {/section}
      </div>
      {/if}
      {if $posts[p].files}
      <h4>Attached Files</h4>
      <ul class="attachments">
      {section name=i loop=$posts[p].filesdata}
        <li style="background-image: url('{$posts[p].filesdata[i].logo}');"><a href="downloads/forum-files/{$posts[p].forumpostid}/{$posts[p].filesdata[i].file}">{$posts[p].filesdata[i].file}</a> ({$posts[p].filesdata[i].size})</li>
      {/section}
      </ul>
      {/if}
      {if $posts[p].authorsignature}<div class="signature">{$posts[p].authorsignature}</div>{/if}
      <div id="source_{$posts[p].forumpostid}" style="display: none;">{$posts[p].fp_bbbody|escape:"htmlall"}</div>
    </div>

    <div class="options">
        {if $userid}
        <a href="{$RELATIVE_URL}#reply" class="post_reply_link" title="Reply Quoting this post" rel="nofollow" onclick="document.getElementById('post').value = '[quote={$posts[p].author|escape:"htmlall"}]'+document.getElementById('source_{$posts[p].forumpostid}').innerHTML+'[/quote]\n';"><img class="icon" src="images/cms/icons/comments.png" alt="Quote" title="Reply quoting this post" /></a>
        {/if}
        {if $group_admin || $group_moderator || ($userid == $posts[p].fp_posterid && $userid > 0)}
        <a href="edit-post/{$posts[p].forumpostid}/index/" title="Edit this Post" rel="nofollow"><img class="icon" src="images/cms/icons/comment_edit.png" alt="Edit Post" title="Edit this post or add images / attachments" /></a>
        {/if}
        {if $group_admin}
        <a href="delete-post/{$posts[p].forumpostid}/index/" title="Delete this post" rel="nofollow" onclick="return confirmdelete();"><img class="icon" src="images/cms/icons/delete.png" alt="Delete" title="Delete this post" /></a>
        {/if}
        {if $group_admin && $posts[p].fp_ip}
        <a href="#" rel="nofollow" title="IP Address Logged" onclick="alert('IP address: {$posts[p].fp_ip}'); return false;"><img class="icon" src="images/cms/icons/transmit_blue.png" alt="IP Address" title="View the IP Address for this post" /></a>
        {/if}
        </div>

  </div>
{/section}
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
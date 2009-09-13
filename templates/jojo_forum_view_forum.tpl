<div class="view-forum">
{if !$content && $intro}<p>{$intro}</p>{/if}
{if $topics}
<!-- [Forum Navigation] -->
<table>
  <tr>
    <td class="forum-back-nav"><a href="forums/">Back to Forum Index</a></td>
    <td class="pagination">{if $pagination}Page {$pagination}{else}&nbsp;{/if}{** <a href="#new-topic"><img class="icon" src="images/cms/icons/add.png" alt="" title="" /></a>**}</td>
  </tr>
</table>

<!-- [Topic List] -->
<table>
<thead>
  <tr>
    <th colspan="2">Topic</th>
    <th>Replies</th>
    <th>Author</th>
    <th>Last Post</th>
  </tr>
</thead>
<tbody>
{section name=t loop=$topics}
  <!-- [Topic: {$topics[t].ft_title}] -->
  <tr>
    <td class="viewforum-info{if $topics[t].ft_locked == "yes"} locked{elseif $topics[t].fresh == true} new-posts{elseif $topics[t].ft_sticky=='yes'} sticky{else} open-topic{/if}">&nbsp;</td>
    <td class="viewforum-topic-name"><a class="viewforum-topic-name" href="{$topics[t].url}">{$topics[t].ft_title}</a>
    {if $group_admin}<a href="edit-topic/{$topics[t].forumtopicid}/index/" title="Edit this Topic" rel="nofollow"><img class="icon" src="images/cms/icons/comment_edit.png" alt="Edit topic" title="Edit topic" /></a>{/if}
    {if $topics[t].pagination}<br />{$topics[t].pagination}{/if}</td>
    <td class="viewforum-num-replies">{$topics[t].numreplies}</td>
    <td class="viewforum-author">{if $topics[t].authorid}<a href="{$topics[t].authorurl}" rel="nofollow">{/if}{$topics[t].author|escape:"htmlall"}{if !$topics[t].authorid && ($topics[t].author != 'Guest')} (guest){/if}{if $topics[t].authorid}</a>{/if}</td>
    {*<td class="viewforum-num-views">{$topics[t].views}</td>*}
    <td class="viewforum-last-post">{if $topics[t].lastpost}<span title="{$topics[t].lastposttimestamp|date_format:"%A, %e %B %Y %I:%M%p"}">{$topics[t].lastpost}</span><br />{if $topics[t].ft_lastposterid}<a href="{$topics[t].lastposterurl}" rel="nofollow">{/if}{$topics[t].lastposter}{if $topics[t].ft_lastposterid}</a>{/if}{else}no posts{/if}</td>
  </tr>
{/section}
</tbody>
</table>

<!-- [Forum Navigation] -->
<table width="100%">
  <tr>
    <td class="forum-back-nav"><a href="forums/">Back to Forum Index</a></td>
    <td class="pagination">{if $pagination}Page {$pagination}{else}&nbsp;{/if}</td>
  </tr>
</table>
<br />

{/if}
{include file='jojo_forum_new_topic.tpl'}
</div>
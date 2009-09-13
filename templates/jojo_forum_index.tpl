<div class="forum-index">
{if !$numshow}
You must login to view these forums (below). If you do not have a forum account, you will need to <a href="register/" title="Register for Forums" rel="nofollow">Register</a>.
{else}
{if $forums}

<table>

<thead>
  <tr>
    <th colspan="2">Forum</th>
    <th>Topics</th>
    <th>Posts</th>
    <th>Last Post</th>
  </tr>
</thead>

<tbody>
{section name=f1 loop=$forums}
  {if $forums[f1].fm_parent == 0}
  {if $forums[f1].viewpermission}

  <tr class="forum-group">
    <td colspan="5" title="{$forums[f1].fm_desc}">{$forums[f1].fm_name}</td>
  </tr>
  {section name=f loop=$forums}
  {if $forums[f1].forumid == $forums[f].fm_parent}
  {if $forums[f].viewpermission}
  <tr>
    <td class="index-info{if $forums[f].fresh == true} new-posts{else} open-forum{/if}">&nbsp;</td>
    <td class="index-forum-name"{if $forums[f].fm_image1} style="background-image: url('images/default/forums/{$forums[f].fm_image1}'); background-position: center right; background-repeat: no-repeat;"{/if}><a href="{$forums[f].url}">{$forums[f].fm_name}</a>{if $forums[f].fm_desc}<br />{$forums[f].fm_desc}{/if}</td>
    <td class="index-num-topics">{$forums[f].numtopics}</td>
    <td class="index-num-posts">{$forums[f].numposts}</td>
    <td class="index-last-post">{if $forums[f].lastposttimestamp}<span title="{$forums[f].lastposttimestamp|date_format:"%A, %e %B %Y %I:%M%p"}">{$forums[f].lastpost}</span><br />{if $forums[f].lastposterurl}<a href="{$forums[f].lastposterurl}">{/if}{if $forums[f].lastposter}{$forums[f].lastposter}{if !$forums[f].lastposterid && ($forums[f].lastposter != 'Guest')} (guest){/if}{else}Guest{/if}{if $forums[f].lastposterurl}</a>{/if}{else}{/if}</td>
  </tr>
  {/if}
  {/if}
  {/section}
  {/if}
  {/if}
{/section}
</tbody>

</table>

{/if}
{/if}
</div>
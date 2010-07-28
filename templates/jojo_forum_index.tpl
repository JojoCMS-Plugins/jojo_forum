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
{foreach from=$forums item=f1}
  {if $f1.fm_parent == 0}
  {if $f1.viewpermission}

  <tr class="forum-group">
    <td colspan="5" title="{$f1.fm_desc}">{$f1.fm_name}</td>
  </tr>
  {foreach from=$forums item=f2}
  {if $f1.forumid == $f2.fm_parent}
  {if $f2.viewpermission}
  <tr>
    <td class="index-info{if $f2.fresh == true} new-posts{else} open-forum{/if}">&nbsp;</td>
    <td class="index-forum-name"{if $f2.fm_image1} style="background-image: url('images/default/forums/{$f2.fm_image1}'); background-position: center right; background-repeat: no-repeat;"{/if}><a href="{$f2.url}">{$f2.fm_name}</a>{if $f2.fm_desc}<br />{$f2.fm_desc}{/if}</td>
    <td class="index-num-topics">{$f2.numtopics}</td>
    <td class="index-num-posts">{$f2.numposts}</td>
    <td class="index-last-post">{if $f2.lastposttimestamp}<span title="{$f2.lastposttimestamp|date_format:"%A, %e %B %Y %I:%M%p"}">{$f2.lastpost}</span><br />{if $f2.lastposterurl}<a href="{$f2.lastposterurl}">{/if}{if $f2.lastposter}{$f2.lastposter}{if !$f2.lastposterid && ($f2.lastposter != 'Guest')} (guest){/if}{else}Guest{/if}{if $f2.lastposterurl}</a>{/if}{else}{/if}</td>
  </tr>
  {/if}
  {/if}
  {/foreach}
  {/if}
  {/if}
{/foreach}
</tbody>

</table>

{/if}
{/if}
</div>
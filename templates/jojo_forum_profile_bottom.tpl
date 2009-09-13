{if $posts}
<div id="recent_posts">
  <h2>Recent forum posts</h2>
  <ul>
  {section name=p loop=$posts}
  <li>{$posts[p].date_friendly} - <a href="{$posts[p].url}" title="{$posts[p].fp_body|truncate:30|escape:'htmlall'}">{$posts[p].topic}</a></li>
  {/section}
  </ul>
</div>
{/if}
{include file='errors.tpl'}
{if $content}
{$content}
{/if}

<div class="forums">

{** EDIT POST **}
{if $action == "editpost"}
{include file="jojo_forum_edit_post.tpl"}
{/if}

{** EDIT TOPIC **}
{if $action == "edittopic"}
{include file="jojo_forum_edit_topic.tpl"}
{/if}

{** MEMBER PROFILE **}
{* Note: this functionality has moved into jojo_community plugin *}
{if $action == "userprofile"}
{include file='jojo_forum_profile.tpl'}
{/if}

{** VIEW TOPIC **}
{if $action == "viewtopic"}
{include file="jojo_forum_view_topic.tpl"}
{/if}

{** VIEW VIEW FORUM **}
{if $action == "viewforum"}
{include file="jojo_forum_view_forum.tpl"}
{/if}

{** POST REPLY **}
{if $action == "reply"}
{include file="jojo_forum_post_reply.tpl"}
{/if}

{** NEW TOPIC **}
{if $action == "new"}
{include file="jojo_forum_new_topic.tpl"}
{/if}

{** FORUM INDEX **}
{if $action == "index"}
{include file="jojo_forum_index.tpl"}
{/if}

{** PAGE FOOTER - PRESENT ON ALL FORUM PAGES **}
{include file="jojo_forum_footer.tpl"}

</div>
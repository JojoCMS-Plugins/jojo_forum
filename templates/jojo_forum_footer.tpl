<div id="login">
{if $username}
<p><br />You are logged in as {$username}. <a href="logout/"><img class="icon" src="images/cms/icons/status_offline.png" alt="" /></a> <a href="logout/">logout</a>  <a href="user-profile/"><img class="icon" src="images/cms/icons/user_edit.png" alt="" /></a> <a href="user-profile/">Edit Profile</a></p>
{else}
<p><br /><b>{$loginmessage|default:"You are not logged in"}</b><br />
{if $OPTIONS.forum_allow_guest_posts=='yes'}It is recommended that you{else}You need to{/if} <a href="register/{if $RELATIVE_URL}{$RELATIVE_URL}{else}{$pg_url}/{/if}" title="Register for Forums" rel="nofollow">Register</a> or <a href="{if $issecure}{$SECUREEURL}{else}{$SITEURL}{/if}/login/{$RELATIVE_URL}" rel="nofollow">Log In</a> before posting on these forums.</p>
{/if}
</div>
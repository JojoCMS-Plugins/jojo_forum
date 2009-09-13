{* this functionality has moved to the jojo_community plugin. Please ensure this is installed. *}
{if $userprofile.avatar}
<img class="boxed" src="images/200/users/{$userprofile.avatar}" alt="{$userprofile.us_login|escape:"htmlall"}" title="{$userprofile.us_login|escape:"htmlall"}" />
<br />
{/if}
Username: {$userprofile.us_login|escape:"htmlall"}<br />

<?php
/**
 *                    Jojo CMS
 *                ================
 *
 * Copyright 2007 Harvey Kane <code@ragepank.com>
 * Copyright 2007 Michael Holt <code@gardyneholt.co.nz>
 * Copyright 2007 Melanie Schulz <mel@gardyneholt.co.nz>
 *
 * See the enclosed file license.txt for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @author  Harvey Kane <code@ragepank.com>
 * @author  Michael Cochrane <code@gardyneholt.co.nz>
 * @author  Melanie Schulz <mel@gardyneholt.co.nz>
 * @license http://www.fsf.org/copyleft/lgpl.html GNU Lesser General Public License
 * @link    http://www.jojocms.org JojoCMS
 */

$_USERTIMEZONE   = 0;
$_SERVERTIMEZONE = 0;

$numrecentposts = Jojo::getOption('form_num_recentposts', 0);
$groupposts = Jojo::getOption('form_group_posts', 'yes');

if ($numrecentposts) {
	$join_option = ($groupposts == 'yes') ? 'fp.forumpostid=ft.ft_lastpostid' : 'fp.fp_topicid=ft.forumtopicid';
    $query = "SELECT fp.*, us.us_login, ft.ft_title FROM {forumtopic} ft";
    $query .= " LEFT JOIN {forumpost} fp ON ($join_option)";
    $query .= " LEFT JOIN {user} us ON (fp.fp_posterid=us.userid)";
    $query .= " ORDER BY fp.fp_datetime DESC";
    $query .= " LIMIT $numrecentposts" ;
    $recentposts = Jojo::selectQuery($query);
    foreach ($recentposts as &$post) {
        $post['username'] = !empty($post['us_login']) ? $post['us_login'] : 'Guest';
        $post['body'] = strip_tags($post['fp_body']);
        $post['title'] =  htmlspecialchars($post['ft_title'], ENT_QUOTES, 'UTF-8', false);
        $post['url'] = _SITEURL.'/'. Jojo::rewrite('topics',$post['fp_topicid'],$post['ft_title'],'') . "#" . $post['forumpostid'];
        $post['date'] = date("d M Y H:i", $post['fp_datetime']);
    }
    
    $smarty->assign('recentposts', $recentposts);
}

/*
                 <div class="sidebarbox">
                    <h2>Recent Forum Posts</h2>
                    {foreach from=$recentposts item=p}
                        <p>{$p.body|truncate:50:"..."}</p>
                        <p class="links">by {$p.username} in <a class="link" href="{$p.url}">{$p.title}</a> on {$p.date}</p>
                    {/foreach}       
                  </div>
*/
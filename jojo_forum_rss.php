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

//require_once(_BASEPLUGINDIR . '/jojo_article/jojo_forum.php');
class JOJO_Plugin_Jojo_forum_rss extends JOJO_Plugin
{

    function _getContent()
    {
        $topicid = Util::getFormData('id',0);
        $forumaction = Util::getFormData('action','');

        if ($forumaction == 'topics') {
            /* specific topic RSS feed */
            $topics = Jojo::selectQuery("SELECT * FROM {forumtopic} WHERE forumtopicid = ?", array($topicid));
            $topic = $topics[0];

            $rss  = "<?xml version=\"1.0\" ?".">\n";
            $rss .= "<rss version=\"2.0\">\n";
            $rss .= "<channel>\n";
            $rss .= "<title>" . _SITETITLE." - " . htmlentities($topic['ft_title']) . "</title>\n";
            $rss .= "<description></description>\n";
            $rss .= "<link>"._SITEURL.'/'. Jojo::rewrite('topics',$topicid,$topic['ft_title'],'') . "</link>\n";
            $rss .= "<copyright>" . htmlentities(_SITETITLE) . " " . date('Y', time()) . "</copyright>\n";

            $posts = Jojo::selectQuery("SELECT * FROM {forumpost} WHERE fp_topicid = ? ORDER BY fp_datetime DESC LIMIT 50", array($topicid));
            $n = count($posts);
            for ($i = 0; $i < $n; $i++) {
                
                /* Everyone group must have view permission for the post to appear in feed */
                $forumperms = new Jojo_Permissions();
                $forumPermissions = $forumperms->getPermissions('forum', $topic['ft_forumid']);
                if (!$forumperms->hasPerm(array('everyone'), 'view')) {
                    continue;
                }
                
                $users = Jojo::selectQuery("SELECT * FROM {user} WHERE userid = ? LIMIT 1", $posts[$i]['fp_posterid']);
                $username = !empty($users[0]['us_login']) ? $users[0]['us_login'] : 'Guest';
                $posts[$i]['fp_body'] = Jojo::relative2absolute($posts[$i]['fp_body'],_SITEURL);
                $rss .= "<item>\n";
                $rss .= "<title>" . htmlentities($topic['ft_title'],ENT_QUOTES,'UTF-8') ." (post by ".htmlentities($username,ENT_QUOTES,'UTF-8').")</title>\n";
                $rss .= "<description>" . str_replace("&middot;", "", $this->rssEscape($posts[$i]['fp_body'])) . "</description>\n";
                $rss .= "<link>"._SITEURL.'/'. Jojo::rewrite('topics',$topicid,$topic['ft_title'],'') . "#".$posts[$i]['forumpostid']. "</link>\n";
                $rss .= "<pubDate>" . date("D, d M Y H:i:s O", $posts[$i]['fp_datetime']) . "</pubDate>\n";
                $rss .= "</item>\n";
            }
            $rss .= "</channel>\n";
            $rss .= "</rss>\n";
        } elseif (false) {
            /* specific forum RSS feed */

        } else {
            /* all forums RSS feed */

            $rss  = "<?xml version=\"1.0\" ?".">\n";
            $rss .= "<rss version=\"2.0\">\n";
            $rss .= "<channel>\n";
            $rss .= "<title>" . _SITETITLE." - All Forum posts" . "</title>\n";
            $rss .= "<description></description>\n";
            $rss .= "<link>"._SITEURL."/forums/</link>\n";
            $rss .= "<copyright>" . htmlentities(_SITETITLE) . " " . date('Y',time()) . "</copyright>\n";

            $posts = Jojo::selectQuery("SELECT * FROM {forumpost} WHERE 1 ORDER BY fp_datetime DESC LIMIT 50");
            $n = count($posts);
            for ($i = 0; $i < $n; $i++) {
            
                $topic = Jojo::selectRow("SELECT * FROM {forumtopic} WHERE forumtopicid=? LIMIT 1", $posts[$i]['fp_topicid']);
            
                /* Everyone group must have view permission for the post to appear in feed */
                $forumperms = new Jojo_Permissions();
                $forumPermissions = $forumperms->getPermissions('forum', $topic['ft_forumid']);
                if (!$forumperms->hasPerm(array('everyone'), 'view')) {
                    continue;
                }
            
                $users = Jojo::selectQuery("SELECT * FROM {user} WHERE userid=? LIMIT 1", $posts[$i]['fp_posterid']);
                $username = !empty($users[0]['us_login']) ? $users[0]['us_login'] : 'Guest';
                
                $posts[$i]['fp_body'] = Jojo::relative2absolute($posts[$i]['fp_body'],_SITEURL);
                $rss .= "<item>\n";
                $rss .= "<title>" . htmlentities($topic['ft_title'],ENT_QUOTES,'UTF-8') ." (post by ".htmlentities($username,ENT_QUOTES,'UTF-8').")</title>\n";
                $rss .= "<description>" . str_replace("&middot;", "", $this->rssEscape($posts[$i]['fp_body'])) . "</description>\n";
                $rss .= "<link>"._SITEURL.'/'. Jojo::rewrite('topics',$topic['forumtopicid'],$topic['ft_title'],'') . "#".$posts[$i]['forumpostid']. "</link>\n";
                $rss .= "<pubDate>" . date("D, d M Y H:i:s O", $posts[$i]['fp_datetime']) . "</pubDate>\n";
                $rss .= "</item>\n";
            }
            $rss .= "</channel>\n";
            $rss .= "</rss>\n";
        }

        header('Content-type: application/xml');
        echo $rss;
        exit;
    }

    function getCorrectUrl()
    {
        $forumaction = Util::getFormData('action','');
        $id = Util::getFormData('id',0);

        if (($forumaction == 'topics') && ($id > 0)) {
            $topics = Jojo::selectQuery("SELECT * FROM {forumtopic} WHERE forumtopicid = ? LIMIT 1", array($id));
            $expectedurl = _SITEURL.'/'. Jojo::rewrite('topics',$topics[0]['forumtopicid'],$topics[0]['ft_title'],'').'rss/';
        } elseif (($forumaction == 'forums') && ($id > 0)) {
            $forums = Jojo::selectQuery("SELECT * FROM {forum} WHERE forumid = ? LIMIT 1", array($id));
            $expectedurl = _SITEURL.'/'. Jojo::rewrite('forums',$forums[0]['forumid'],$forums[0]['fm_name'],'').'rss/';
        } else {
            return parent::getCorrectUrl();
        }
        return $expectedurl;
    }

    function rssEscape($data) {
        return str_replace('<', '&lt;', str_replace('>', '&gt;', str_replace('"', '&quot;', str_replace('&', '&amp;', $data))));
    }
}
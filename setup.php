<?php
/**
 *                    Jojo CMS
 *                ================
 *
 * Copyright 2007-2008 Harvey Kane <code@ragepank.com>
 * Copyright 2007-2008 Michael Holt <code@gardyneholt.co.nz>
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



/* create forum table */
if (!Jojo::tableExists('forum')) {
    echo "Table <b>forum</b> Does not exist - creating empty table<br />";
    $query = "
    CREATE TABLE {forum} (
        `forumid` int(11) NOT NULL auto_increment,
        `fm_name` varchar(255) NOT NULL default '',
        `fm_seotitle` varchar(255) NOT NULL default '',
        `fm_desc` varchar(255) NOT NULL default '',
        `fm_url` varchar(255) NOT NULL default '',
        `fm_body` text NOT NULL,
        `fm_parent` int(11) NOT NULL default '0',
        `fm_order` int(11) NOT NULL default '0',
        `auth_reply` varchar(255) NOT NULL default '',
        `fm_status` varchar(255) NOT NULL default '',
        `fm_image1` varchar(255) NOT NULL default '',
        `fm_permissions` text NOT NULL,
        PRIMARY KEY  (`forumid`)
        ) TYPE=MyISAM;
    ";
    Jojo::updateQuery($query);
}

/* create forumtopic table */
if (!Jojo::tableExists('forumtopic')) {
    echo "Table <b>forumtopic</b> Does not exist - creating empty table<br />";
    $query = "
        CREATE TABLE {forumtopic} (
        `forumtopicid` int(11) NOT NULL auto_increment,
        `ft_title` varchar(255) NOT NULL default '',
        `ft_posterid` int(11) NOT NULL default '0',
        `ft_postername` varchar(255) NOT NULL default '',
        `ft_datetime` int(11) NOT NULL default '0',
        `ft_forumid` int(11) NOT NULL default '0',
        `ft_sticky` enum('yes','no') NOT NULL default 'no',
        `ft_locked` enum('yes','no') NOT NULL default 'no',
        `ft_views` int(11) NOT NULL default '0',
        PRIMARY KEY  (`forumtopicid`),
        KEY `forumid` (`ft_forumid`)
        ) TYPE=MyISAM;
    ";
    Jojo::updateQuery($query);
}

/* create forumpost table */
/*
if (!Jojo::tableExists('forumpost')) {
    echo "Table <b>forumpost</b> Does not exist - creating empty table<br />";
    $query = "
        CREATE TABLE {forumpost} (
        `forumpostid` int(11) NOT NULL auto_increment,
        `fp_topicid` int(11) NOT NULL default '0',
        `fp_posterid` int(11) NOT NULL default '0',
        `fp_postername` varchar(255) NOT NULL default '',
        `fp_datetime` int(11) NOT NULL default '0',
        `fp_body` text NOT NULL,
        `fp_bbbody` text NOT NULL,
        `fp_ip` varchar(255) NOT NULL default '',
        `fp_edited` int(11) default '0',
        `fp_editedcount` int(11) NOT NULL default '0',
        PRIMARY KEY  (`forumpostid`),
        KEY `topicid` (`fp_topicid`)
        ) TYPE=MyISAM;
    ";
    Jojo::updateQuery($query);
}
*/

/* create forumsubscription table */
if (!Jojo::tableExists('forumsubscription')) {
    echo "Table <b>forumsubscription</b> Does not exist - creating empty table<br />";
    $query = "
        CREATE TABLE {forumsubscription} (
        `userid` INT NOT NULL DEFAULT '0',
        `topicid` INT NOT NULL DEFAULT '0',
        `lastviewed` INT NOT NULL DEFAULT '0',
        `lastemailed` INT NOT NULL DEFAULT '0',
        `lastupdated` INT NOT NULL DEFAULT '0'
        ) TYPE = MYISAM ;
    ";
    Jojo::updateQuery($query);
}

/* reset the post count, which is cached */
echo "Resetting Post count<br />";
    /*$topics = Jojo::selectQuery("
        SELECT forumtopicid, ft_forumid, COUNT(*) AS num,
        SUBSTRING( MAX( CONCAT(LPAD(fp_datetime,15,'0'),fp_posterid) ), 16) AS lastposterid,
        0.00+LEFT( MAX( CONCAT(LPAD(fp_datetime,15,'0'),fp_posterid) ), 15) AS lastpost
        FROM `forumtopic` AS ft
        LEFT JOIN `forumpost` AS fp
        ON ft.forumtopicid = fp.fp_topicid
        WHERE 1
        GROUP BY ft_forumid
        ORDER BY fp_datetime DESC
        ");
    */
$topics = Jojo::selectQuery("SELECT * FROM {forumtopic} WHERE 1");

for ($i=0; $i<count($topics); $i++) {
    $data = Jojo::selectQuery("SELECT COUNT(*) AS numposts FROM {forumpost} WHERE fp_topicid= ? GROUP BY fp_topicid DESC", array($topics[$i]['forumtopicid']));
    $numposts = isset($data[0]['numposts']) ? $data[0]['numposts'] : 0;
    $data = Jojo::selectQuery("SELECT forumpostid, fp_posterid, fp_datetime AS lastpost FROM {forumpost} WHERE fp_topicid = ? ORDER BY fp_datetime DESC LIMIT 1", array($topics[$i]['forumtopicid']));
    if (count($data)) {
        $lastpost = $data[0]['lastpost'];
        $lastpostid = $data[0]['forumpostid'];
        $lastposter = $data[0]['fp_posterid'];
    } else {
        $lastpost   = 0;
        $lastpostid = 0;
        $lastposter = 0;
        $numposts   = 0;
    }
    Jojo::updateQuery("UPDATE {forumtopic} SET ft_lastpostid = ?, ft_numposts = ?,
                      ft_lastposterid = ?, ft_lastpostdate = ? WHERE forumtopicid = ? LIMIT 1",
                      array($lastpostid, $numposts, $lastposter, $lastpost, $topics[$i]['forumtopicid']));
}

/* add extra fields to user table */
if (Jojo::tableExists('user')) {
    /* Forum Times - a serialized array of forum ids and the last time this user visited the forum */
    if (!Jojo::fieldexists('user','us_forumtimes')) {
        echo "Add <b>us_forumtimes</b> to <b>user</b><br />";
        Jojo::structureQuery("ALTER TABLE {user} ADD `us_forumtimes` text NOT NULL;");
    }

    /* Topic Times - a serialized array of topic ids and the last time this user visited the topic */
    if (!Jojo::fieldexists('user','us_topictimes')) {
        echo "Add <b>us_topictimes</b> to <b>user</b><br />";
        Jojo::structureQuery("ALTER TABLE {user} ADD `us_topictimes` text NOT NULL;");
    }

    /* Interesting Topic - a serialized array of topic ids and whether the user is interested - used for hiding topics the user isnt interested in */
    if (!Jojo::fieldexists('user','us_topicinterest')) {
        echo "Add <b>us_topicinterest</b> to <b>user</b><br />";
        Jojo::structureQuery("ALTER TABLE {user} ADD `us_topicinterest` text NOT NULL;");
    }

    /* Avatar - small image used for forums */
    if (!Jojo::fieldexists('user','us_avatar')) {
        echo "Add <b>us_avatar</b> to <b>user</b><br />";
        Jojo::structureQuery("ALTER TABLE {user} ADD `us_avatar` VARCHAR( 255 ) NOT NULL;");
    }

    /* tagline - short tag to show in forums - also known as "Rank" in PHPBB. Note the size of the VARCHAR field... */
    if (!Jojo::fieldexists('user','us_tagline')) {
        echo "Add <b>us_tagline</b> to <b>user</b><br />";
        Jojo::structureQuery("ALTER TABLE {user} ADD `us_tagline` VARCHAR( 25 ) NOT NULL;");
    }

    /* used for import on MOD site - no longer required */
    if (Jojo::fieldexists('user','us_backupsignature')) {
        echo "Delete <b>us_backupsignature</b> from <b>user</b><br />";
        Jojo::structureQuery("ALTER TABLE {user} DROP `us_backupsignature`;");
    }

    /* us_bbsignature */
    if (!Jojo::fieldexists('user','us_bbsignature')) {
        echo "Add <b>us_bbsignature</b> to <b>user</b><br />";
        Jojo::structureQuery("ALTER TABLE {user} ADD `us_bbsignature` TEXT NOT NULL;");
        Jojo::updateQuery("UPDATE {user} SET us_bbsignature=us_signature WHERE 1");
        echo "Cache <b>signatures</b> as HTML<br />";
        $users = Jojo::selectQuery("SELECT userid, us_bbsignature FROM {user} WHERE 1");
        for ($i=0;$i<count($users);$i++) {

            $bb = new bbconverter;
            $bb->setBbCode($users[$i]['us_bbsignature']);
            $html = $bb->convert('bbcode2html');
            Jojo::updateQuery("UPDATE {user} SET us_signature = ? WHERE userid = ? LIMIT 1", array($html, $users[$i]['userid']));
        }
    }

}



if (!Jojo::fieldExists('forum','fm_lastpostid')) {
    echo "Add <b>fm_lastpostid</b> to <b>forum</b><br />";
    Jojo::structureQuery("ALTER TABLE {forum} ADD `fm_lastpostid` INT(11) DEFAULT 0 NOT NULL  ;");
}

if (!Jojo::fieldExists('forum','fm_seotitle')) {
    echo "Add <b>fm_seotitle</b> to <b>forum</b><br />";
    Jojo::structureQuery("ALTER TABLE {forum} ADD `fm_seotitle` VARCHAR(255) DEFAULT '' NOT NULL  ;");
}

if (!Jojo::fieldExists('forum','fm_url')) {
    echo "Add <b>fm_url</b> to <b>forum</b><br />";
    Jojo::structureQuery("ALTER TABLE {forum} ADD `fm_url` VARCHAR(255) DEFAULT '' NOT NULL  ;");
}

if (!Jojo::fieldExists('forum','fm_bbbody')) {
    echo "Add <b>fm_bbbody</b> to <b>forum</b><br />";
    Jojo::structureQuery("ALTER TABLE {forum} ADD `fm_bbbody` TEXT DEFAULT '' NOT NULL  ;");
}

if (!Jojo::fieldExists('forumtopic','ft_numposts')) {
    echo "Add <b>ft_numposts</b> to <b>forumtopic</b><br />";
    Jojo::structureQuery("ALTER TABLE {forumtopic} ADD `ft_numposts` INT(11) DEFAULT 0 NOT NULL  ;");
}
if (!Jojo::fieldExists('forumtopic','ft_lastpostdate')) {
    echo "Add <b>ft_lastpostdate</b> to <b>forumtopic</b><br />";
    Jojo::structureQuery("ALTER TABLE {forumtopic} ADD `ft_lastpostdate` INT(11) DEFAULT 0 NOT NULL  ;");
}
if (!Jojo::fieldExists('forumtopic','ft_lastposterid')) {
    echo "Add <b>ft_lastposterid</b> to <b>forumtopic</b><br />";
    Jojo::structureQuery("ALTER TABLE {forumtopic} ADD `ft_lastposterid` INT(11) DEFAULT 0 NOT NULL  ;");
}
if (!Jojo::fieldExists('forumtopic','ft_lastpostid')) {
    echo "Add <b>ft_lastpostid</b> to <b>forumtopic</b><br />";
    Jojo::structureQuery("ALTER TABLE {forumtopic} ADD `ft_lastpostid` INT(11) DEFAULT 0 NOT NULL  ;");
}

if (!Jojo::fieldExists('forumtopic','ft_seotitle')) {
    echo "Add <b>ft_seotitle</b> to <b>forumtopic</b><br />";
    Jojo::structureQuery("ALTER TABLE {forumtopic} ADD `ft_seotitle` VARCHAR(255) DEFAULT '' NOT NULL  ;");
}

if (!Jojo::fieldExists('forumtopic','ft_body')) {
    echo "Add <b>ft_body</b> to <b>forumtopic</b><br />";
    Jojo::structureQuery("ALTER TABLE {forumtopic} ADD `ft_body` TEXT DEFAULT '' NOT NULL  ;");
}

if (!Jojo::fieldExists('forumtopic','ft_bbbody')) {
    echo "Add <b>ft_bbbody</b> to <b>forumtopic</b><br />";
    Jojo::structureQuery("ALTER TABLE {forumtopic} ADD `ft_bbbody` TEXT DEFAULT '' NOT NULL  ;");
}


$data = Jojo::selectQuery("SELECT * FROM {page} WHERE pg_link='jojo_plugin_jojo_forum'");
if (!count($data)) {
    echo "Adding <b>Forum</b> Page to menu<br />";
    $forumpageid = Jojo::insertQuery("INSERT INTO {page} SET pg_title='Forums', pg_link='jojo_plugin_jojo_forum', pg_url='forums'");
} else {
    $forumpageid = $data[0]['pageid'];
}

/* forum RSS feeds */
$data = Jojo::selectQuery("SELECT * FROM {page} WHERE pg_link='jojo_plugin_jojo_forum_rss'");
if (!count($data)) {
    echo "Adding <b>Forum RSS</b> Page to menu<br />";
    $forumpageid = Jojo::insertQuery("INSERT INTO {page} SET pg_title='Forum RSS Feed', pg_link='jojo_plugin_jojo_forum_rss', pg_url='forums/rss', pg_parent=?, pg_mainnav='no', pg_breadcrumbnav='no', pg_footernav='no', pg_sitemapnav='no', pg_xmlsitemapnav='no'", array($forumpageid));
}


/* add Edit forum page to admin section */
$data = Jojo::selectQuery("SELECT * FROM {page} WHERE pg_url='admin/edit/forum'");
if (!count($data)) {
    echo "Adding <b>Edit Forum</b> Page to menu<br />";
    Jojo::insertQuery("INSERT INTO {page} SET pg_title='Edit Forums', pg_link='jojo_plugin_admin_edit', pg_url='admin/edit/forum', pg_parent= ?, pg_order=5", array($_ADMIN_CONTENT_ID));
}

/* add some sample forums to get started */
if (Jojo::tableExists('forum')) {
    $data = Jojo::selectRow("SELECT COUNT(*) AS numforums FROM {forum}");
    if (!$data['numforums']) {
        $parentid = Jojo::insertQuery("INSERT INTO {forum} SET fm_name='General forums', fm_parent=0"); //category
        Jojo::insertQuery("INSERT INTO {forum} SET fm_name='Test forum', fm_url='test', fm_parent=?", $parentid); //test forum
    }
}
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
 * @author  Michael Cochrane <mikec@jojocms.org>
 * @author  Melanie Schulz <mel@gardyneholt.co.nz>
 * @license http://www.fsf.org/copyleft/lgpl.html GNU Lesser General Public License
 * @link    http://www.jojocms.org JojoCMS
 * @package jojo_article
 */

$table = 'forumpost';
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
        KEY `topicid` (`fp_topicid`),
        FULLTEXT KEY `body` (`fp_body`)
        ) TYPE=MyISAM;
";

/* Check table structure */
$result = Jojo::checkTable($table, $query);

/* Output result */
if (isset($result['created'])) {
    echo sprintf("jojo_forum: Table <b>%s</b> Does not exist - created empty table.<br />", $table);
}

if (isset($result['added'])) {
    foreach ($result['added'] as $col => $v) {
        echo sprintf("jojo_forum: Table <b>%s</b> column <b>%s</b> Does not exist - added.<br />", $table, $col);
    }
}

if (isset($result['different'])) Jojo::printTableDifference($table, $result['different']);

/**/
$indexed = array();
$indexes = Jojo::selectQuery("SHOW INDEX FROM {forumpost}");
foreach ($indexes as $k => $v) {
    $indexed[] = $v['Key_name'];
}

if (!in_array('body', $indexed)) {
    echo "Add INDEX <b>body</b> to <b>page</b><br />";
    Jojo::structureQuery("ALTER TABLE {forumpost} ADD FULLTEXT `body` (`fp_body`)");
}
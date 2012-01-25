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



class Jojo_Plugin_Jojo_forum extends Jojo_Plugin
{

    function addSubscription($userid, $topicid) {
        if (!$userid) return false;
        /* remove any existing subscription */
        Jojo::deleteQuery("DELETE FROM {forumsubscription} WHERE userid=? AND topicid=?", array($userid, $topicid));
        Jojo::insertQuery("INSERT INTO {forumsubscription} SET userid=?, topicid=?, lastviewed=?, lastemailed=0", array($userid, $topicid, time()));
        return true;
    }

    function removeSubscription($userid, $topicid) {
        if (!$userid) return false;
        Jojo::deleteQuery("DELETE FROM {forumsubscription} WHERE userid=? AND topicid=?", array($userid, $topicid));
        return true;
    }

    function isSubscribed($userid, $topicid) {
        $data = Jojo::selectRow("SELECT * FROM {forumsubscription} WHERE userid=? AND topicid=?", array($userid, $topicid));
        return (count($data)) ? true : false;
    }

    function markSubscriptionsUpdated($topicid) {
        Jojo::insertQuery("UPDATE {forumsubscription} SET lastupdated=? WHERE topicid=?", array(time(), $topicid));
    }

    function markSubscriptionsViewed($userid, $topicid) {
        Jojo::insertQuery("UPDATE {forumsubscription} SET lastviewed=? WHERE userid=? AND topicid=? LIMIT 1", array(time(), $userid, $topicid));
    }

    function processSubscriptionEmails($limit=3) {
        $subscriptions = Jojo::selectQuery("SELECT fs.*, ft.ft_title, us.us_login, us.us_email FROM {forumsubscription} fs LEFT JOIN {forumtopic} ft ON (fs.topicid=ft.forumtopicid) LEFT JOIN {user} us ON (fs.userid=us.userid) WHERE (lastupdated > lastviewed) AND (lastviewed > lastemailed) LIMIT ?", $limit);
        foreach ($subscriptions as $sub) {
            $subject  = 'Reply notification: ' . $sub['ft_title'];
            $message  = 'A reply has been posted to the topic "' . $sub['ft_title'] . '" on ' . Jojo::getOption('sitetitle') . ' forums. You are subscribed to receive notifications of any updates to this topic.';
            $message .= "To view the updated topic, please visit the following link.\n";
            $message .= _SITEURL . '/' . Jojo::rewrite('topics', $sub['topicid'], $sub['ft_title'], '');
            if (@Jojo::simpleMail($sub['us_login'], $sub['us_email'], $subject, $message)) {
                Jojo::updateQuery("UPDATE {forumsubscription} SET lastemailed=? WHERE userid=? AND topicid=?", array(time(), $sub['userid'], $sub['topicid']));
            }
        }
    }

    function getUserData($userid) {
        global $posters;

        /* Check if data already exists for this user */
        if (isset($posters[$userid])) {
            return true;
        }

        /* Build an array of User Data to save queries later on */
        //$data = Jojo::selectQuery("SELECT userid, us_login AS login, COUNT(*) AS numposts, us_avatar AS avatar, us_tagline AS tagline, us_signature AS signature, us_bbsignature FROM {user} INNER JOIN forumpost ON userid = fp_posterid WHERE userid=? GROUP BY userid LIMIT 1", $userid);
        $user = Jojo::selectRow("SELECT userid, us_login AS login, us_email AS email, us_avatar AS avatar, us_tagline AS tagline, us_signature AS signature, us_bbsignature FROM {user} WHERE userid=?", $userid);
        if (!count($user)) return false;
        $posters[$userid]        = array();
        $posters[$userid]        = $user;
        $posters[$userid]['url'] = $userid > 0 ? Jojo::rewrite('profile', $userid, $user['login']) : '';
        $data                    = Jojo::selectQuery("SELECT COUNT(*) AS numposts FROM {forumpost} WHERE fp_posterid=?", $userid);

        if (count($data)) {
            $posters[$userid]['numposts'] = $data[0]['numposts'];
        } else {
            $posters[$userid]['numposts'] = 0;
        }

        /* Animated Gifs are ok - don't resize them */
        if (isset($posters[$userid]['avatar']) && (Jojo::getfileextension($posters[$userid]['avatar']) == 'gif')) {
            $posters[$userid]['animated'] = true;
        }

        return true;
    }

    function uploadImage($postid, $name) {

        if (isset($_FILES[$name])) {

            $filename = $_FILES[$name]['name']; //for convenience
            //We must not allow PHP files to be uploaded to the server as the visitor could guess the location and execute them.
            $ext = strtolower(Jojo::getfileextension($filename));
            if (in_array($ext, array('php', 'php3', 'php4', 'inc', 'phtml'))) {
                echo "You cannot upload PHP files into this system for security reasons. If you really need to, please Zip them first and upload the Zip file.";
                exit();
            }

            //TODO: Check destination directory exists etc
            $destination = '';
            //Check error codes
            switch ($_FILES[$name]['error']) {
                case UPLOAD_ERR_INI_SIZE: //1
                    //error
                    break;
                case UPLOAD_ERR_FORM_SIZE: //2
                    //error
                    break;
                case UPLOAD_ERR_PARTIAL: //3
                    //error
                    break;
                case UPLOAD_ERR_NO_FILE: //4 - this is only a problem if it's a required field
                    //remember, a required field only needs to be set the first time, perhaps its better to check this somewhere else
                    //if ($this->fd_required == "yes") {
                    //    $this->error = "Required field";
                    //}
                    break;
                case 6: // UPLOAD_ERR_NO_TMP_DIR - for some odd reason the constant wont work
                    //error
                    //log for administrator
                    break;
                case UPLOAD_ERR_OK: //0
                    //check for empty file
                    if($_FILES[$name]["size"] == 0) {
                        //error
                    }
                    if (!is_uploaded_file($_FILES[$name]['tmp_name'])) { //improve this code when you have time - will work, but needs fleshing out
                        //log
                        die("Upload error. Script will now halt.");
                    }
                    //All appears good, so attempt to move to final resting place
                    $destination = _DOWNLOADDIR.'/forum-images/'.$postid.'/'.basename($filename);

                    /* Make the directory */
                    if (!file_exists(dirname($destination))) Jojo::recursiveMkdir(dirname($destination), 0777);

                    //Ensure file does not already exist on server, rename if it does
                    $i=1;
                    while (file_exists($destination)){
                        $i++;
                        $newname     = $i."_".$filename;
                        $destination = _DOWNLOADDIR.'/forum-images/'.$postid.'/'.$newname;
                    }

                    if (move_uploaded_file($_FILES[$name]['tmp_name'], $destination)) {
                        $message = "Upload successful";
                    } else {
                        //log
                        die("File upload error. Script will now halt.");
                    }
                    break;
                default:
                    //this code shouldn't execute - 0 should be the default
            }
            return basename($destination);
        }
        return '';
    }

    /////////////////////////CONVERT TIME ZONE////////////////////////////////////////////
    /*
    Converts a timestamp to another timestamp taking into account the timezone of the server and the user
    $server and $user are in hours, NZ = 12
    */
    function convertTimeZone($timestamp, $server=0, $user=0) {
      return $timestamp - ($server * 60 * 60) + ($user * 60 * 60);
    }

    function uploadFile($postid, $name)
    {
        if (isset($_FILES[$name])) {

            $filename = $_FILES[$name]['name']; //for convenience
            //We must not allow PHP files to be uploaded to the server as the visitor could guess the location and execute them.
            $ext = strtolower(Jojo::getfileextension($filename));
            if (in_array($ext, array('php', 'php3', 'php4', 'inc', 'phtml'))) {
                echo "You cannot upload PHP files into this system for security reasons. If you really need to, please Zip them first and upload the Zip file.";
                exit();
            }

            //TODO: Check destination directory exists etc
            $destination = '';
            //Check error codes
            switch ($_FILES[$name]['error']) {
                case UPLOAD_ERR_INI_SIZE: //1
                    //error
                    break;
                case UPLOAD_ERR_FORM_SIZE: //2
                    //error
                    break;
                case UPLOAD_ERR_PARTIAL: //3
                    //error
                    break;
                case UPLOAD_ERR_NO_FILE: //4 - this is only a problem if it's a required field
                    //remember, a required field only needs to be set the first time, perhaps its better to check this somewhere else
                    //if ($this->fd_required == "yes") {
                    //    $this->error = "Required field";
                    //}
                    break;
                case 6: // UPLOAD_ERR_NO_TMP_DIR - for some odd reason the constant wont work
                    //error
                    //log for administrator
                    break;
                case UPLOAD_ERR_OK: //0
                    //check for empty file
                    if($_FILES[$name]["size"] == 0) {
                        //error
                    }
                    if (!is_uploaded_file($_FILES[$name]['tmp_name'])) { //improve this code when you have time - will work, but needs fleshing out
                        //log
                        die("Upload error. Script will now halt.");
                    }
                    //All appears good, so attempt to move to final resting place
                    $destination = _DOWNLOADDIR.'/forum-files/'.$postid.'/'.basename($filename);

                    /* Make the directory */
                    Jojo::recursiveMkdir(dirname($destination), 0777);

                    //Ensure file does not already exist on server, rename if it does
                    $i=1;
                    while (file_exists($destination)){
                        $i++;
                        $newname = $i."_".$filename;
                        $destination = _DOWNLOADDIR.'/forum-files/'.$postid.'/'.$newname;
                    }

                    if (move_uploaded_file($_FILES[$name]['tmp_name'], $destination)) {
                        $message = "Upload successful";
                    } else {
                        //log
                        die("File upload error. Script will now halt.");
                    }
                    break;
                default:
                    //this code shouldn't execute - 0 should be the default
            }
            return basename($destination);
        }
        return '';
    }

    function recursiveScandir($dir = './', $sort = 0)
    {
        $dir_open = @ opendir($dir);
        if (! $dir_open)
            return false;
        while (($dir_content = readdir($dir_open)) !== false)
            $files[] = $dir_content;
        if ($sort == 1)
            rsort($files, SORT_STRING);
        else
            sort($files, SORT_STRING);
        return $files;
    }

    /* template hook - shows 'recent posts' on the suer profile */
    function profile_bottom() {
        $userid = Jojo::getFormData('id', false);
        if (!$userid) return false;

        global $smarty, $_USERGROUPS;
        $maxage   = 90; //don't show posts older than this (days)
        $maxposts = 5; //max number of recent posts to show

        /* retrieve recent post activity from the database. This may be more data than we need, but remember we need to delete the posts where the forum is password protected */
        $postdata = Jojo::selectQuery("SELECT * FROM {forumpost} WHERE fp_posterid=? AND fp_datetime>? GROUP BY fp_topicid ORDER BY fp_datetime DESC LIMIT 100", array($userid, strtotime('-'.$maxage.' days')));
        $n = count($postdata);

        $posts = array();
        if ($n) {
            $forumperms = new Jojo_Permissions();
            for ($i=0; $i<$n; $i++) {
                /* enforce 'maxposts' setting */
                if (count($posts) >= $maxposts) {
                    break;
                }
                $topic = Jojo::selectRow("SELECT * FROM {forumtopic} WHERE forumtopicid=?", $postdata[$i]['fp_topicid']);
                /* topic doesn't exist */
                if (empty($topic['forumtopicid'])) {
                    continue;
                }
                /* ensure the logged-in user is allowed to see the forum this user has been posting in */
                $forumPermissions = $forumperms->getPermissions('forum', $topic['ft_forumid']);
                if (!$forumperms->hasPerm($_USERGROUPS, 'view')) {
                    continue;
                }
                $postdata[$i]['topic'] = $topic['ft_title'];
                $postdata[$i]['url'] = 'topics/'.$topic['forumtopicid'].'/'.Jojo::cleanUrl($topic['ft_title']).'/';
                $postdata[$i]['date_friendly'] = Jojo::mysql2date(date('Y-m-d', $postdata[$i]['fp_datetime']), 'friendly'); //no, I'm not proud of this
                $posts[] = $postdata[$i];
            }
            $smarty->assign('posts', $posts);
        }
        return $smarty->fetch('jojo_forum_profile_bottom.tpl');
    }

    function isModerator()
    {
        global $_USERGROUPS;

        static $_cache;
        if (isset($_cache)) return $_cache;
        $_cache = false;

        /* $moderators is an array of all usergroups that are allowed to moderate forums */
        $moderators_str = Jojo::getOption('forum_moderator_groups', 'admin');
        $moderators = explode(',', str_replace(' ', '', $moderators_str));

        foreach ($_USERGROUPS as $group) {
            if (in_array($group, $moderators)) {
                $_cache = true;
                break;
            }
        }
        return $_cache;
    }

    function _getContent()
    {
        global $smarty, $_USERGROUPS, $_USERID, $posters, $_SERVERTIMEZONE, $_USERTIMEZONE;

        $content = array();

        $smarty->assign('is_moderator', self::isModerator());
        $smarty->assign('prefix', 'forums');

        $forumaction = Jojo::getFormData('action', 'forums');
        $id          = Jojo::getFormData('id', 0);
        $forumurl    = Jojo::getFormData('forumurl', '');

        /* if working off a URL, get the forumid */
        if (!empty($forumurl) && empty($id)) {
            $forum = Jojo::selectRow("SELECT forumid FROM {forum} WHERE fm_url=?", array($forumurl));
            if ($forum) {
                $id      = $forum['forumid'];
                $forumid = $forum['forumid'];
            }
        }

        $forumperms = new Jojo_Permissions(); //Used for checking access in other parts of this script

        /* Initialize all variables */
        $pagenumber = Jojo::getFormData('pagenumber', 1);
        $profileid  = Jojo::getFormData('profileid',  0);
        $forumid    = Jojo::getFormData('forumid',    0);
        $topicid    = Jojo::getFormData('topicid',    0);
        $postid     = Jojo::getFormData('postid',     0);
        $action     = Jojo::getFormData('action',     '');
        $body       = Jojo::getFormData('post',       '');
        $subject    = Jojo::getFormData('subject',    '');
        $seotitle   = Jojo::getFormData('seotitle',   '');
        $topictitle = Jojo::getFormData('topictitle', '');
        $userid     = isset($_USERID) ? $_USERID : 0;

//echo $forumaction.' - ' . $action ; exit;
        //get details of currently logged in user
        //$userid = 0; //ID of the currently logged in user - 0 = guest
        $username = Jojo::getFormData('name', 'Guest');

        if ($action != '') {

            if ($action == 'subscribe') {
                if ($_USERID) Jojo_Plugin_Jojo_forum::addSubscription($_USERID, $id);
                Jojo::redirect(_SITEURL.'/topics/' . $id . '/index/', 302);

            } elseif ($action == 'unsubscribe') {
                if ($_USERID) Jojo_Plugin_Jojo_forum::removeSubscription($_USERID, $id);
                Jojo::redirect(_SITEURL . '/topics/' . $id . '/index/', 302);

            } elseif ($action == 'newtopic') {

                /* POST NEW TOPIC */
                $errors = array();

                if (empty($subject)) {
                    echo 'Please enter a subject';
                    exit();
                }

                $captchacode = Jojo::getFormData('captchacode', '');
                if (empty($_USERID) && !PhpCaptcha::Validate($captchacode)) {
                    $errors[] = 'Invalid code entered';
                }

                /* Forum Security - must have 'view' permission to post */
                $forumperms = new Jojo_Permissions();
                $forumPermissions = $forumperms->getPermissions('forum', $forumid);
                if (!$forumperms->hasPerm($_USERGROUPS, 'view')) {
                    echo 'Access Denied';
                    exit();
                }

                /* ensure guests aren't posting unless options allows this */
                if (!$userid && Jojo::getOption('forum_allow_guest_posts', 'no') == 'no') {
                    echo 'Guest posts are not allowed on this forum.';
                    exit();
                }

                /* only moderators can make a post sticky */
                $sticky = Jojo::getFormData('sticky', false);
                $sticky = ($sticky && self::isModerator()) ? 'yes' : 'no';

                if (count($errors)) {
                    $smarty->assign('errors', $errors);
                    $forumaction = 'new';
                } else {
                    $topicid = Jojo::insertQuery("INSERT INTO {forumtopic} SET ft_title=?, ft_posterid=?, ft_postername=?, ft_datetime=?, ft_forumid=?, ft_sticky=?, ft_locked='no'", array($subject, $userid, $username, time(), $forumid, $sticky));
                    $htmlbody = Jojo::bb2Html($body);

                    $forumpostid = Jojo::insertQuery("INSERT INTO {forumpost} SET fp_topicid=?, fp_posterid=?, fp_postername=?, fp_datetime=?, fp_bbbody=?, fp_body=?, fp_ip=?", array($topicid, $userid, $username, time(), $body, $htmlbody, Jojo::getIp()));
                    if (isset($_FILES['file1'])) {self::uploadImage($forumpostid, 'file1');}
                    if (isset($_FILES['file2'])) {self::uploadImage($forumpostid, 'file2');}
                    if (isset($_FILES['file3'])) {self::uploadImage($forumpostid, 'file3');}
                    if (isset($_FILES['file4'])) {self::uploadImage($forumpostid, 'file4');}

                    if (isset($_FILES['file-upload-1'])) {self::uploadFile($forumpostid, 'file-upload-1');}
                    if (isset($_FILES['file-upload-2'])) {self::uploadFile($forumpostid, 'file-upload-2');}
                    if (isset($_FILES['file-upload-3'])) {self::uploadFile($forumpostid, 'file-upload-3');}
                    if (isset($_FILES['file-upload-4'])) {self::uploadFile($forumpostid, 'file-upload-4');}

                    /* Update last post variable in forum */
                    Jojo::updateQuery("UPDATE {forum} SET fm_lastpostid=? WHERE forumid=? LIMIT 1", array($forumpostid, $forumid));
                    Jojo::updateQuery("UPDATE {forumtopic} SET ft_lastpostid=?, ft_numposts=1, ft_lastposterid=?, ft_lastpostdate=? WHERE forumtopicid=? LIMIT 1", array($forumpostid, $userid, time(), $topicid));

                    Jojo::redirect(_SITEURL.'/topics/'.$topicid.'/index/', 302);
                }
            }

            /* POST REPLY */

            if ($action == 'postreply') {

                $errors = array();

                /* get forum id */
                $data = Jojo::selectRow("SELECT ft_forumid FROM {forumtopic} WHERE forumtopicid=?", $topicid);
                $forumid = !empty($data['ft_forumid']) ? $data['ft_forumid'] : false;

                /* Forum Security - must have 'view' permission to post */
                $forumperms = new Jojo_Permissions();
                $forumPermissions = $forumperms->getPermissions('forum', $forumid);
                if (!$forumperms->hasPerm($_USERGROUPS, 'view')) {
                    echo 'Access Denied';
                    exit();
                }

                /* ensure guests aren't posting unless options allows this */
                if (!$userid && Jojo::getOption('forum_allow_guest_posts', 'no') == 'no') {
                    echo 'Guest posts are not allowed on this forum.';
                    exit();
                }

                $captchacode = Jojo::getFormData('captchacode', '');
                if (empty($_USERID) && !PhpCaptcha::Validate($captchacode)) {
                    $errors[] = 'Invalid code entered';
                }

                $htmlbody = Jojo::bb2Html($body);

                if (count($errors)) {
                    $smarty->assign('errors', $errors);
                    $forumaction = 'reply';
                } else {
                    $forumpostid = Jojo::insertQuery("INSERT INTO {forumpost} SET fp_topicid=?, fp_posterid=?, fp_postername=?, fp_datetime=?, fp_bbbody=?, fp_body=?, fp_ip=?", array($topicid, $userid, $username, time(), $body, $htmlbody, Jojo::getIp()));
                    if (isset($_FILES['file1'])) {self::uploadImage($forumpostid, 'file1');}
                    if (isset($_FILES['file2'])) {self::uploadImage($forumpostid, 'file2');}
                    if (isset($_FILES['file3'])) {self::uploadImage($forumpostid, 'file3');}
                    if (isset($_FILES['file4'])) {self::uploadImage($forumpostid, 'file4');}

                    if (isset($_FILES['file-upload-1'])) {self::uploadFile($forumpostid, 'file-upload-1');}
                    if (isset($_FILES['file-upload-2'])) {self::uploadFile($forumpostid, 'file-upload-2');}
                    if (isset($_FILES['file-upload-3'])) {self::uploadFile($forumpostid, 'file-upload-3');}
                    if (isset($_FILES['file-upload-4'])) {self::uploadFile($forumpostid, 'file-upload-4');}

                    /* Update last post variable in forum */
                    Jojo::updateQuery("UPDATE {forum} SET `fm_lastpostid`=? WHERE `forumid`=? LIMIT 1", array($forumpostid, $forumid));
                    Jojo::updateQuery("UPDATE {forumtopic} SET `ft_lastpostid`=?, `ft_numposts`=ft_numposts+1, ft_lastposterid=?, ft_lastpostdate=? WHERE forumtopicid=? LIMIT 1", array($forumpostid, $userid, time(), $topicid));

                    /* subscribe the user to the topic */
                    $subscribe = Jojo::getFormData('subscribe',false);
                    if ($subscribe) Jojo_Plugin_Jojo_forum::addSubscription($userid, $topicid);
                    Jojo_Plugin_Jojo_forum::markSubscriptionsUpdated($topicid);

                    /* Find number of last page */
                    $data = Jojo::selectRow("SELECT COUNT(*) AS numrecords FROM {forumpost} WHERE fp_topicid=? ORDER BY fp_datetime", $topicid);
                    $numrecords = $data['numrecords'];
                    $numperpage = Jojo::getOption('forum_posts_per_page');
                    if (empty($numperpage)) $numperpage = 10;
                    $lastpage = ceil($numrecords / $numperpage);
                    $pagecode = ($lastpage > 1) ? 'p'.$lastpage : ''; //eg p2, p3 etc - p1 is not used for page1, just leave it blank

                    Jojo::redirect(_SITEURL . '/topics/' . $topicid . $pagecode . '/index/', 302);
                }
            }


            if ($action == 'editpost') {

                /* only moderators or the original poster can edit the post */
                if (!self::isModerator()) {
                    $post = Jojo::selectRow("SELECT fp_posterid FROM {forumpost} WHERE forumpostid=?", $postid);
                    if (empty($post['fp_posterid']) || ($post['fp_posterid'] != $_USERID)) {
                        echo 'Access denied: You are only able to edit your own posts.';
                        exit;
                    }
                }

                $htmlbody = Jojo::bb2Html($body);
                Jojo::updateQuery("UPDATE {forumpost} SET fp_bbbody=?, fp_body=?, fp_edited=?, fp_editedcount=fp_editedcount+1 WHERE forumpostid=? LIMIT 1", array($body, $htmlbody, time(), $postid));

                if (isset($_FILES['file1'])) {Jojo_Plugin_Jojo_forum::uploadImage($postid, 'file1');}
                if (isset($_FILES['file2'])) {Jojo_Plugin_Jojo_forum::uploadImage($postid, 'file2');}
                if (isset($_FILES['file3'])) {Jojo_Plugin_Jojo_forum::uploadImage($postid, 'file3');}
                if (isset($_FILES['file4'])) {Jojo_Plugin_Jojo_forum::uploadImage($postid, 'file4');}

                if (isset($_FILES['file-upload-1'])) {Jojo_Plugin_Jojo_forum::uploadFile($postid, 'file-upload-1');}
                if (isset($_FILES['file-upload-2'])) {Jojo_Plugin_Jojo_forum::uploadFile($postid, 'file-upload-2');}
                if (isset($_FILES['file-upload-3'])) {Jojo_Plugin_Jojo_forum::uploadFile($postid, 'file-upload-3');}
                if (isset($_FILES['file-upload-4'])) {Jojo_Plugin_Jojo_forum::uploadFile($postid, 'file-upload-4');}

                /* Delete Images */
                if (isset($_POST['deleteimage'])) {
                    foreach ($_POST['deleteimage'] as $k => $filename) {
                        unlink(_DOWNLOADDIR.'/forum-images/'.$postid.'/'.$filename);
                    }
                }

                /* Delete Files */
                if (isset($_POST['deletefile'])) {
                    foreach ($_POST['deletefile'] as $k => $filename) {
                        unlink(_DOWNLOADDIR.'/forum-files/' . $postid . '/' . $filename);
                    }
                }

                Jojo::redirect(_SITEURL.'/topics/' . $topicid . '/index/');
            }

            if ($action == 'edittopic') {
              if (!empty($topictitle)) {
                    /* only moderators can edit the stickness of a post */
                    if (self::isModerator()) {
                        $sticky = Jojo::getFormData('sticky', false);
                        $sticky = $sticky ? 'yes' : 'no';

                        Jojo::updateQuery("UPDATE {forumtopic} SET ft_title=?, ft_seotitle=?, ft_sticky=? WHERE forumtopicid=? LIMIT 1", array($topictitle, $seotitle, $sticky, $topicid));
                    } else {
                        Jojo::updateQuery("UPDATE {forumtopic} SET ft_title=?, ft_seotitle=? WHERE forumtopicid=? LIMIT 1", array($topictitle, $seotitle, $topicid));
                    }
                }
                Jojo::redirect(_SITEURL.'/topics/' . $topicid . '/index/', 302);
            }

            if ($action == 'deletepost') {

                /* this code seems to be a duplicate of below - is it needed? */

                $post = Jojo::selectRow("SELECT fp_posterid, fp_topicid FROM {forumpost} WHERE forumpostid=?", $postid);
                $topicid = isset($post['fp_topicid']) ? $post['fp_topicid'] : 0;

                /* only moderators or the original poster can delete a post */
                if (!$_USERID || (!self::isModerator() && ($post['fp_posterid']) != $_USERID)) {
                    echo 'Access denied: You are only able to delete your own posts.';
                    exit;
                }
                print_r($data);exit;
                /* if deleting the first post in a topic, delete the whole topic */
                $data = Jojo::selectRow("SELECT forumpostid FROM {forumpost} WHERE fp_topicid=? ORDER BY fp_datetime LIMIT 1", $topicid);
                if ($data['forumpostid'] == $postid) {
                    /* this is the first post */
                    Jojo::deleteQuery("DELETE FROM {forumpost} WHERE `fp_topicid`=? LIMIT 1", $topicid);
                    Jojo::deleteQuery("DELETE FROM {forumtopic} WHERE `fp_topicid`=? LIMIT 1", $topicid);
                } else {
                    /* this is not the first post */
                    $numdeleted = 1;
                    Jojo::deleteQuery("DELETE FROM {forumpost} WHERE `forumpostid`=? LIMIT 1", $postid);
                    Jojo::updateQuery("UPDATE {forumtopic} SET `ft_numposts`=`ft_numposts`-".$numdeleted." WHERE `forumtopicid`=? LIMIT 1", $topicid);
                }


                header('location: ' . _SITEURL . '/topics/' . $topicid . '/index/');
                exit();
            }
        }

        $posters = array();

        /* EDIT POST */

        if ($forumaction == 'edit-post') {
            $forumpostid = $id;
            $post = Jojo::selectRow("SELECT fp.*, ft.ft_forumid FROM {forumpost} fp LEFT JOIN {forumtopic} ft ON (fp.fp_topicid=ft.forumtopicid) WHERE `forumpostid`=?", $forumpostid);
            if (!count($post)) {
                echo 'Post does not exist. It may have been deleted.';
                exit();
            }
            /* must be logged in and group must have view permission */
            if (!$_USERID) {
                echo 'Access denied: You must be logged in to be able to edit posts.';
                exit();
            }

            $forumperms = new Jojo_Permissions();
            $forumPermissions = $forumperms->getPermissions('forum',$post['ft_forumid']);
            if (!$forumperms->hasPerm($_USERGROUPS, 'view')) {
                echo 'Access Denied';
                exit();
            }

            /* Make a list of images */
            $post['images'] = Jojo::scanDirectory(_DOWNLOADDIR.'/forum-images/'.$forumpostid.'/');

            /* Make a list of files */
            $post['files'] = Jojo::scanDirectory(_DOWNLOADDIR.'/forum-files/'.$forumpostid.'/');

            /* Add Edit Breadcrumb */
            $breadcrumbs                      = $this->_getBreadCrumbs();
            $breadcrumb                       = array();
            $breadcrumb['name']               = 'Edit Post';
            $breadcrumb['rollover']           = 'Edit Post';
            $breadcrumb['url']                =  Jojo::rewrite('edit-post', $forumpostid, 'index', '');
            $breadcrumbs[count($breadcrumbs)] = $breadcrumb;

            $smarty->assign('post', $post);
            $smarty->assign('action', 'editpost');
            $content['title']       = 'Edit Post';
            $content['seotitle']    = 'Edit Post';
            $content['javascript']  = $smarty->fetch('jojo_forum_edit_post_js.tpl');
            $content['breadcrumbs'] = $breadcrumbs;


        /* EDIT TOPIC */
        } elseif ($forumaction == 'edit-topic') {
            $forumtopicid = $id;

            $topic = Jojo::selectRow("SELECT * FROM {forumtopic} WHERE forumtopicid=?", $forumtopicid);
            if (!count($topic)) {
                echo "Topic does not exist. It may have been deleted.";
                exit();
            }

            /* Forum Security - Group must have view permission */
            $forumperms = new Jojo_Permissions();
            $forumPermissions = $forumperms->getPermissions('forum', $topic['ft_forumid']);
            if (!$forumperms->hasPerm($_USERGROUPS, 'view')) {
                echo 'Access Denied';
                exit();
            }

            /* Add Edit Breadcrumb */
            $breadcrumbs                      = $this->_getBreadCrumbs();
            $breadcrumb                       = array();
            $breadcrumb['name']               = 'Edit Topic';
            $breadcrumb['rollover']           = 'Edit Topic';
            $breadcrumb['url']                =  Jojo::rewrite('edit-topic', $topicid, 'index', '');
            $breadcrumbs[count($breadcrumbs)] = $breadcrumb;

            $smarty->assign('topic', $topic);
            $smarty->assign('action', 'edittopic');
            $content['title']       = 'Edit Topic';
            $content['seotitle']    = 'Edit Topic';
            $content['breadcrumbs'] = $breadcrumbs;

        /* DELETE POST */

        } elseif ($forumaction == 'delete-post') {
            $postid = $id;
            $post = Jojo::selectRow("SELECT * FROM {forumpost} WHERE forumpostid=?", $postid);

            if (empty($post['forumpostid'])) {
                echo "Post does not exist. It may have already been deleted.";
                exit();
            }

            $topicid = isset($post['fp_topicid']) ? $post['fp_topicid'] : 0;

            /* only moderators or the original poster can delete a post */
            if (!$_USERID || (!self::isModerator() && ($post['fp_posterid']) != $_USERID)) {
                echo 'Access denied: You are only able to delete your own posts.';
                exit;
            }

            /* if deleting the first post in a topic, delete the whole topic */
            $data = Jojo::selectRow("SELECT forumpostid FROM {forumpost} WHERE fp_topicid=? ORDER BY fp_datetime LIMIT 1", $topicid);
            if ($data['forumpostid'] == $postid) {
                /* this is the first post */

                $topic = Jojo::selectRow("SELECT * FROM {forumtopic} WHERE forumtopicid=?", $topicid);
                $forum = Jojo::selectRow("SELECT * FROM {forum} WHERE forumid=?", $topic['ft_forumid']);

                $posts = Jojo::selectQuery("SELECT * FROM {forumpost} WHERE fp_topicid=?", $topicid);
                foreach ($posts as $deletepost) {
                    /* delete post images */
                    $imagedir = _DOWNLOADDIR.'/forum-images/'.$deletepost['forumpostid'].'/';
                    if (file_exists($imagedir)) {
                        $d = dir($imagedir);
                        while($entry = $d->read()) {
                         if ($entry!= "." && $entry!= "..") unlink($entry);
                        }
                        $d->close();
                        rmdir($imagedir);
                    }

                    /* delete post files */
                    $filedir = _DOWNLOADDIR.'/forum-files/'.$deletepost['forumpostid'].'/';
                    if (file_exists($filedir)) {
                        $d = dir($filedir);
                        while($entry = $d->read()) {
                         if ($entry!= "." && $entry!= "..") unlink($entry);
                        }
                        $d->close();
                        rmdir($filedir);
                    }
                }

                Jojo::deleteQuery("DELETE FROM {forumpost} WHERE `fp_topicid`=? LIMIT 1", $topicid);
                Jojo::deleteQuery("DELETE FROM {forumtopic} WHERE `forumtopicid`=? LIMIT 1", $topicid);
                if (!empty($forum['fm_url'])) {
                    header('location: ' . _SITEURL . '/forums/' . $forum['fm_url'].'/', 302);
                } else {
                    header('location: ' . _SITEURL . '/forums/' . $forum['forumid'] . '/index/', 302);
                }

            } else {
                /* this is not the first post */
                $numdeleted = 1;

                /* delete post images */
                $imagedir = _DOWNLOADDIR.'/forum-images/'.$postid.'/';
                if (file_exists($imagedir)) {
                    $d = dir($imagedir);
                    while($entry = $d->read()) {
                     if ($entry!= "." && $entry!= "..") unlink($entry);
                    }
                    $d->close();
                    rmdir($imagedir);
                }

                /* delete post files */
                $filedir = _DOWNLOADDIR.'/forum-files/'.$postid.'/';
                if (file_exists($filedir)) {
                    $d = dir($filedir);
                    while($entry = $d->read()) {
                     if ($entry!= "." && $entry!= "..") unlink($entry);
                    }
                    $d->close();
                    rmdir($filedir);
                }

                Jojo::deleteQuery("DELETE FROM {forumpost} WHERE `forumpostid`=? LIMIT 1", $postid);
                Jojo::updateQuery("UPDATE {forumtopic} SET `ft_numposts`=`ft_numposts`-".$numdeleted." WHERE `forumtopicid`=? LIMIT 1", $topicid);
                header('location: ' . _SITEURL . '/topics/' . $topicid . '/index/', 302);
            }





        /* MEMBER PROFILE */
        /* this functionality has moved to jojo_community. It's left here for backwards compatibility only. Please ensure jojo_community plugin is installed. */
        } elseif ($forumaction == 'profiles') {
            Jojo_Plugin_Jojo_forum::getUserData($id);
            $profile = $posters[$id];

            $this->qt = 'user';
            $this->qid = $id;

            /* Add Profile Breadcrumb */
            $breadcrumbs = $this->_getBreadCrumbs();
            $breadcrumb                       = array();
            $breadcrumb['name']               = $profile['login'];
            $breadcrumb['rollover']           = $profile['login'];
            $breadcrumb['url']                = $profile['url'];
            $breadcrumbs[count($breadcrumbs)] = $breadcrumb;

            /* Assign Smarty variables */
            $smarty->assign('action',  'userprofile');
            $smarty->assign('profile', $profile);


            $userprofile = Jojo::selectRow("SELECT * FROM {user} WHERE userid=?", $id);

            /* ensure avatar file exists */
            if ( ($userprofile['us_avatar'] != '') &&  Jojo::fileExists(_DOWNLOADDIR.'/users/'.$userprofile['us_avatar']) ) {
                $userprofile['avatar'] = $userprofile['us_avatar'];
            }

            $smarty->assign('userprofile', $userprofile);

            $content['title']       = ucfirst($profile['login']);
            $content['seotitle']    = ucfirst($profile['login']);
            $content['breadcrumbs'] = $breadcrumbs;


        /* POST DETAIL (VIEW POST) */
        //This is not currently used - it could be a bit spammy. This would be one page per post, ie a permalink
        } elseif ($forumaction == 'posts') {
            $smarty->assign('action','viewpost');


        /* VIEW TOPIC */
        } elseif ($forumaction == 'topics') {
            $topicid = $id;

            /* Set quick edit vars */
            $this->qt = 'forumtopic';
            $this->qid = $topicid;

            /* Constants for Pagination - should be user defined */
            $postsperpage = Jojo::getOption('forum_posts_per_page');
            if (empty($postsperpage)) $postsperpage = 10;
            $startrecord = $postsperpage * ($pagenumber-1);

            /* Get data for the topic */
            $topic = Jojo::selectRow("SELECT * FROM {forumtopic} WHERE `forumtopicid`=?", $topicid);
            $topic['ft_title'] = htmlspecialchars($topic['ft_title'], ENT_COMPAT, 'UTF-8', false);
            $topic['url'] =  Jojo::rewrite('topics', $topic['forumtopicid'], $topic['ft_title'], '');

            /* Get data for the forum */
            $forum = Jojo::selectRow("SELECT * FROM {forum} WHERE `forumid`=?", $topic['ft_forumid']);
            $forum['fm_name_unescaped'] = $forum['fm_name'];
            $forum['fm_name'] = htmlspecialchars($forum['fm_name'], ENT_COMPAT, 'UTF-8', false);
            $forum['url'] =  Jojo_Plugin_Jojo_forum::getForumUrl($forum);

            /* Forum Security - Group must have view permission */
            $forumperms = new Jojo_Permissions();
            $forumPermissions = $forumperms->getPermissions('forum',$forum['forumid']);
            if (!$forumperms->hasPerm($_USERGROUPS, 'view')) {
                echo 'Access Denied';
                exit();
            }

            /* build intro paragraph */
            $intro_template = Jojo::getOption('forum_topic_intro_text', '<strong>[topic]</strong>, a forum discussion on [site]. Join us for more discussions on <em>[topic]</em> on our [forum] forum.');
            $intro = str_replace(array('[topic]', '[forum]', '[site]'), array(ucfirst($topic['ft_title']), ucfirst($forum['fm_name']), _SITETITLE), $intro_template);
            $smarty->assign('intro', $intro);

            /* Update Last View time for this topic - save to User profile */
            if (isset($_USERID) && $_USERID!=0) {
                $smarty->assign('subscribed', Jojo_Plugin_Jojo_forum::isSubscribed($_USERID, $topicid));
                $topictimesarr = Jojo::selectRow("SELECT `us_topictimes` FROM {user} WHERE `userid`=?", $_USERID);
                $topictimes = array();
                if (!empty($topictimesarr['us_topictimes'])) $topictimes = unserialize($topictimesarr['us_topictimes']);
                $topictimes[$topicid] = time();
                Jojo::updateQuery("UPDATE {user} SET us_topictimes=? WHERE userid=? LIMIT 1", array(serialize($topictimes), $_USERID));
                Jojo_Plugin_Jojo_forum::markSubscriptionsViewed($_USERID, $topicid);
            }

            /* Get data for all posts in the topic */
            $data = Jojo::selectQuery("SELECT COUNT(*) AS numrecords FROM {forumpost} WHERE fp_topicid=? ORDER BY `fp_datetime`", $topicid);
            $numrecords = $data[0]['numrecords'];
            $posts = Jojo::selectQuery("SELECT * FROM {forumpost} WHERE `fp_topicid`=? ORDER BY `fp_datetime` LIMIT $startrecord, $postsperpage", array($topicid));
            $n = count($posts);
            /* redirect back to page 1 if page 2+ has no content */
            if (!$n && $pagenumber>1) {
                Jojo::redirect(_SITEURL . '/' . $topic['url']);
            }
            for ($i=0; $i<$n; $i++) {
                Jojo_Plugin_Jojo_forum::getUserData($posts[$i]['fp_posterid']);
                $posts[$i]['url']              = Jojo::rewrite('posts', $posts[$i]['forumpostid'], 'index', '');
                $posts[$i]['author']           = ($posts[$i]['fp_posterid']!=0) ? Jojo::either($posters[$posts[$i]['fp_posterid']]['login'], $posts[$i]['fp_postername']) : Jojo::either($posts[$i]['fp_postername'], 'Guest');
                $posts[$i]['authorid']         = $posts[$i]['fp_posterid'];
                $posts[$i]['authorurl']        = isset($posters[$posts[$i]['fp_posterid']]['url']) ? $posters[$posts[$i]['fp_posterid']]['url'] : '';
                $posts[$i]['authortagline']    = isset($posters[$posts[$i]['fp_posterid']]['tagline']) ? $posters[$posts[$i]['fp_posterid']]['tagline'] : '';
                $posts[$i]['authorsignature']  = isset($posters[$posts[$i]['fp_posterid']]['signature']) ? $posters[$posts[$i]['fp_posterid']]['signature'] : '';
                if ( isset($posters[$posts[$i]['fp_posterid']]['avatar']) && Jojo::fileExists(_DOWNLOADDIR.'/users/'.$posters[$posts[$i]['fp_posterid']]['avatar'])) {
                    $posts[$i]['authoravatar'] = $posters[$posts[$i]['fp_posterid']]['avatar'];
                    $posts[$i]['animated']     = isset($posters[$posts[$i]['fp_posterid']]['animated']) ? $posters[$posts[$i]['fp_posterid']]['animated'] : '';
                }
                $posts[$i]['authornumposts'] = isset($posters[$posts[$i]['fp_posterid']]['numposts']) ? $posters[$posts[$i]['fp_posterid']]['numposts'] : '';
                //$posts[$i]['fp_body'] = bbcode_format($posts[$i]['fp_body']);
                //$posts[$i]['body'] =  ($posts[$i]['fp_body'] != '') ? $posts[$i]['fp_body'] : bbcode2html($posts[$i]['fp_bbbody']);
                //$posts[$i]['body'] = bbcode2html($posts[$i]['fp_bbbody']); //for now, let's regenerate BBCode every time

                if (true) { //always regenerate HTML from BBCode
                //if ($posts[$i]['fp_body'] == '') {
                    $bb = new bbconverter;
                    $bb->nofollow = true;
                    $bb->setBbCode($posts[$i]['fp_bbbody']);
                    $posts[$i]['body'] = $bb->convert('bbcode2html');
                    //cache a copy for next time
                    Jojo::updateQuery("UPDATE {forumpost} SET fp_body=? WHERE forumpostid=? LIMIT 1", array($posts[$i]['body'], $posts[$i]['forumpostid']));
                } else {
                    $posts[$i]['body'] = $posts[$i]['fp_body'];
                }
                $posts[$i]['postdate'] = Jojo::relativeDate(Jojo_Plugin_Jojo_forum::convertTimeZone($posts[$i]['fp_datetime'], $_SERVERTIMEZONE, $_USERTIMEZONE));
                $posts[$i]['images']   = Jojo::scanDirectory(_DOWNLOADDIR.'/forum-images/'.$posts[$i]['forumpostid'].'/');

                if ($posts[$i]['images']) {
                    require_once(_BASEPLUGINDIR . '/jojo_core/external/bbconverter/magazinelayout.class.php');
                    $template = "<a href=\"images/650/[image]\" rel=\"lightbox\" onclick=\"return false;\"><img src=\"images/[size]/[image]\" alt=\"\" /></a>";
                    $mag = new magazinelayout(450, 3, $template);
                    foreach ($posts[$i]['images'] as $k => $image) {
                        $mag->addImage(_DOWNLOADDIR . '/forum-images/' . $posts[$i]['forumpostid'] . '/' . $image,'forum-images/' . $posts[$i]['forumpostid'] . '/' . $image);
                    }
                    $posts[$i]['imagelayout'] = $mag->getHtml();
                }

                $posts[$i]['files'] = Jojo::scanDirectory(_DOWNLOADDIR . '/forum-files/' . $posts[$i]['forumpostid'] . '/');
                if ($posts[$i]['files']) {
                    $posts[$i]['filesdata'] = array();
                    $x = 0;
                    foreach ($posts[$i]['files'] as $k => $file) {
                        $posts[$i]['filesdata'][$x] = array();
                        $posts[$i]['filesdata'][$x]['file'] = $file;
                        $posts[$i]['filesdata'][$x]['size'] = Jojo::roundBytes(filesize(_DOWNLOADDIR . '/forum-files/' . $posts[$i]['forumpostid'] . '/' . $file));
                        $posts[$i]['filesdata'][$x]['type'] = Jojo::getFileExtension($file);
                        if (Jojo::fileExists(_BASEPLUGINDIR . '/jojo_core/images/cms/filetypes/' . $posts[$i]['filesdata'][$x]['type'] . '.gif')) { //display logo image (dependent on file extension) if one exists, otherwise use the default (txt)
                            $posts[$i]['filesdata'][$x]['logo'] = 'images/cms/filetypes/' . $posts[$i]['filesdata'][$x]['type'] . '.gif';
                        } else {
                            $posts[$i]['filesdata'][$x]['logo'] = "images/cms/filetypes/default.gif";
                        }
                        $x++;
                    }

                }
            }

            /* Add Forum Breadcrumb */
            $breadcrumbs = $this->_getBreadCrumbs();
            $breadcrumb = array();
            $breadcrumb['name']               = $forum['fm_name_unescaped'];
            $breadcrumb['rollover']           = Jojo::either($forum['fm_seotitle'], $forum['fm_name_unescaped']);
            $breadcrumb['url']                = Jojo_Plugin_Jojo_forum::getForumUrl($forum);
            $breadcrumbs[count($breadcrumbs)] = $breadcrumb;

            /* Add Topic Breadcrumb */
            $breadcrumb = array();
            $breadcrumb['name']     = $topic['ft_title'];
            $breadcrumb['rollover'] = $topic['ft_title'];
            $breadcrumb['url']      =  Jojo::rewrite('topics', $topic['forumtopicid'], $topic['ft_title'], '');
            $breadcrumbs[count($breadcrumbs)] = $breadcrumb;

            /* Pagination */
            $pagination = '';
            if ($numrecords > 0) {
                $numpages = ceil($numrecords / $postsperpage);
                $pagecount = 1;
                $span = 5; //only show span pages either side of current page - don't want to see hundreds of page links
                $pagination = '';
                if ($numpages > 1) { //don't bother with pagination if only one page
                    if ($pagenumber > 1) {$pagination .= "<a href=\"". Jojo::rewrite('topic',$topic['forumtopicid'],$topic['ft_title'],'s','',($pagenumber-1))."\" title=\"Previous page\">&lt;&lt;</a> ";}
                    while ($pagecount <= $numpages) {
                        if ( ($pagecount < ($pagenumber+$span)) and ($pagecount > ($pagenumber-$span)) ) {
                            if ($pagecount != $pagenumber) {$pagination .= "<a href=\"". Jojo::rewrite('topic',$topic['forumtopicid'],$topic['ft_title'],'s','',$pagecount)."\" title=\"Go to page $pagecount\">[$pagecount]</a> ";} else {$pagination .= "<b>[$pagecount]</b> ";}
                        }
                        $pagecount = $pagecount + 1;
                    }
                    if ($pagenumber < $numpages) {$pagination .= "<a href=\"". Jojo::rewrite('topic',$topic['forumtopicid'],$topic['ft_title'],'s','',($pagenumber+1))."\" title=\"Next page\">&gt;&gt;</a> ";}
                }
            }

            /* Assign Smarty variables */
            $smarty->assign('action',     'viewtopic');
            $smarty->assign('forum',      $forum);
            $smarty->assign('topic',      $topic);
            $smarty->assign('posts',      $posts);
            $smarty->assign('pagination', $pagination);

            $content['title']       = $topic['ft_title'];
            $content['seotitle']    = Jojo::either($topic['ft_seotitle'], $topic['ft_title'].' | '.$forum['fm_name']);
            $content['breadcrumbs'] = $breadcrumbs;
            $content['javascript']  = $smarty->fetch('jojo_forum_view_topic_js.tpl');
            $content['rssicon'][$topic['ft_title']] = _SITEURL . '/' . Jojo::rewrite('topics', $topic['forumtopicid'], $topic['ft_title'], '').'rss/';


        /* VIEW FORUM */
        } elseif (($forumaction == 'forums') && ($id > 0)) {
            $span = 20; //only show span pages either side of current page - don't want to see hundreds of page links
            $numpages = 1;
            $forumid = $id;

            /* Constants for Pagination - should be user defined */
            $topicsperpage = Jojo::getOption('forum_topics_per_page');
            if (empty($topicsperpage)) $topicsperpage = 25;
            $postsperpage = Jojo::getOption('forum_posts_per_page');
            if (empty($postsperpage)) $postsperpage = 10;
            $startrecord = $topicsperpage * ($pagenumber-1);

            /* Get data for the forum */
            $forum = Jojo::selectRow("SELECT * FROM {forum} WHERE forumid=?", array($forumid));
            $forum['url'] =  Jojo_Plugin_Jojo_forum::getForumUrl($forum['forumid'], $pagenumber);

            /* Forum Security - Group must have view permission */
            $forumperms = new Jojo_Permissions();
            $forumPermissions = $forumperms->getPermissions('forum', $forumid);
            if (!$forumperms->hasPerm($_USERGROUPS, 'view')) {
                echo 'Access Denied';
                exit();
            }

            /* build intro paragraph */
            $intro_template = Jojo::getOption('forum_intro_text', '<strong>[forum]</strong>, a forum topic on [site]. Join in the <em>[forum]</em> discussions on our community forum.');
            $intro = str_replace(array('[forum]', '[site]'), array(ucfirst($forum['fm_name']), _SITETITLE), $intro_template);
            $smarty->assign('intro', $intro);

            /* Get topic times array from user - this is an array of the last time each topic was visited */
            if (isset($_USERID) && $_USERID!=0) {
                $topictimesarr = Jojo::selectRow("SELECT us_topictimes FROM {user} WHERE userid=?", $_USERID);
                $topictimes = array();
                $topictimes = unserialize($topictimesarr['us_topictimes']);
            }

            /* Update Last View time for this forum - save to User profile */
            if (isset($_USERID) && $_USERID!=0) {
                $forumtimesarr = Jojo::selectRow("SELECT us_forumtimes FROM {user} WHERE userid=?", $_USERID);
                $forumtimes = array();
                $forumtimes = unserialize($forumtimesarr['us_forumtimes']);
                $forumtimes[$forumid] = strtotime('now');
                Jojo::updateQuery("UPDATE {user} SET `us_forumtimes`=? WHERE `userid`=? LIMIT 1", array(serialize($forumtimes), $_USERID));
            }

            $data = Jojo::selectQuery("SELECT COUNT(*) AS numrecords FROM {forumtopic} WHERE ft_forumid=?", $forumid);
            $numrecords = $data[0]['numrecords'];

            /* Get data for all topics in the forum */
            $topics = Jojo::selectQuery("SELECT * FROM {forumtopic} WHERE ft_forumid=? ORDER BY ft_sticky, ft_lastpostdate DESC LIMIT $startrecord, $topicsperpage", $forumid);
            $n = count($topics);
            for ($i=0; $i<$n; $i++) {
                Jojo_Plugin_Jojo_forum::getUserData($topics[$i]['ft_posterid']);
                Jojo_Plugin_Jojo_forum::getUserData($topics[$i]['ft_lastposterid']);
                $topics[$i]['url']           = Jojo::rewrite('topics', $topics[$i]['forumtopicid'], $topics[$i]['ft_title'],'');
                $topics[$i]['authorid']      = $topics[$i]['ft_posterid'];
                $topics[$i]['author']        = !empty($posters[$topics[$i]['ft_posterid']]['login']) ? $posters[$topics[$i]['ft_posterid']]['login'] : 'Guest';
                $topics[$i]['authorurl']     = !empty($posters[$topics[$i]['ft_posterid']]['url']) ? $posters[$topics[$i]['ft_posterid']]['url'] : '';
                $topics[$i]['numreplies']    = $topics[$i]['ft_numposts'] - 1;
                $topics[$i]['lastpost']      = Jojo::relativeDate(Jojo_Plugin_Jojo_forum::convertTimeZone($topics[$i]['ft_lastpostdate'], $_SERVERTIMEZONE, $_USERTIMEZONE));
                //$topics[$i]['lastposterid']  = $topics[$i]['ft_lastposterid'];
                $topics[$i]['lastposter']    = !empty($posters[$topics[$i]['ft_lastposterid']]['login']) ? $posters[$topics[$i]['ft_lastposterid']]['login'] : 'Guest';
                $topics[$i]['lastposterurl'] = !empty($posters[$topics[$i]['ft_lastposterid']]['url']) ? $posters[$topics[$i]['ft_lastposterid']]['url'] : '';
                if (isset($topictimes)) {
                    $topics[$i]['fresh']     = !empty($topictimes[$topics[$i]['forumtopicid']]) ? ($topics[$i]['ft_lastpostdate'] > $topictimes[$topics[$i]['forumtopicid']]) : false;
                } else {
                    $topics[$i]['fresh'] = true;
                }

                /* Topic Pagination - this logic is different to other pagination so dont use copy paste */
                $pagination = '';
                $pagination_array = array();
                if ($topics[$i]['numreplies'] > 0) {
                    $numpages = ceil($topics[$i]['numreplies'] / $postsperpage);
                    $pagecount = 1;
                    $pagination = '';
                    if ($numpages > 1) { //don't bother with pagination if only one page
                        $pagination .= 'Page ';
                        //$successcount = 0;
                        while ($pagecount <= $numpages) {
                            if ( ($pagecount < ($pagenumber+$span)) and ($pagecount > ($pagenumber-$span)) ) {
                                $pagination_array[] = "<a href=\"". Jojo::rewrite('topic', $topics[$i]['forumtopicid'], $topics[$i]['ft_title'], 's', '', $pagecount)."\" title=\"Go to page $pagecount\">$pagecount</a>";
                              //$successcount++;
                            }
                            $pagecount = $pagecount + 1;
                        }
                    }
                }

                $topics[$i]['pagination'] = $pagination.implode($pagination_array,', ');

                if ($numpages > $span) {
                  $topics[$i]['pagination'] .= " ... <a href=\"" . Jojo::rewrite('topic', $topics[$i]['forumtopicid'], $topics[$i]['ft_title'], 's', '', $numpages) . "\" title=\"Go to page $numpages\">$numpages</a>";
                }
            }

            /* Pagination */
            $pagination = '';
            if ($numrecords > 0) {
                $numpages = ceil($numrecords / $topicsperpage);
                $pagecount = 1;
                $span = 5; //only show span pages either side of current page - don't want to see hundreds of page links
                $pagination = '';
                if ($numpages > 1) { //don't bother with pagination if only one page
                    if ($pagenumber > 1) {$pagination .= '<a href="'.Jojo_Plugin_Jojo_forum::getForumUrl($forum, $pagenumber-1).'" title="Previous page">&lt;&lt;</a> ';}
                    while ($pagecount <= $numpages) {
                        if ( ($pagecount < ($pagenumber+$span)) and ($pagecount > ($pagenumber-$span)) ) {
                            if ($pagecount != $pagenumber) {
                                $pagination .= '<a href="' . Jojo_Plugin_Jojo_forum::getForumUrl($forum, $pagecount) . '" title="Go to page ' . $pagecount . '">[' . $pagecount . ']</a> ';
                            } else {
                                $pagination .= "<b>[$pagecount]</b> ";
                            }
                        }
                        $pagecount = $pagecount + 1;
                    }
                    if ($pagenumber < $numpages) {$pagination .= "<a href=\"" . Jojo_Plugin_Jojo_forum::getForumUrl($forum, $pagenumber+1) . "\" title=\"Next page\">&gt;&gt;</a> ";}
                }
            }


            /* Add Forum Breadcrumb */
            $breadcrumbs = $this->_getBreadCrumbs();
            $breadcrumb = array();
            $breadcrumb['name']               = $forum['fm_name'];
            $breadcrumb['rollover']           = Jojo::either($forum['fm_seotitle'], $forum['fm_name']);
            $breadcrumb['url']                = Jojo_Plugin_Jojo_forum::getForumUrl($forum);
            $breadcrumbs[count($breadcrumbs)] = $breadcrumb;

            $smarty->assign('action',     'viewforum');
            $smarty->assign('forum',      $forum);
            $smarty->assign('topics',     $topics);
            $smarty->assign('pagination', $pagination);

            $content['title']            = $forum['fm_name'];
            $content['seotitle']         =  Jojo::either($forum['fm_seotitle'], $forum['fm_name'].' | '.$this->page['pg_title']);
            $content['breadcrumbs']      = $breadcrumbs;
            $content['metadescription']  = '';
            $content['meta_description'] = '';
            $content['javascript']       = $smarty->fetch('jojo_forum_view_forum_js.tpl');
            $smarty->assign('content', $forum['fm_body']);

        /* POST REPLY */
        } elseif ($forumaction == 'reply') {
            $topicid = Jojo::getFormData('id', false);
            $topic = Jojo::selectRow("SELECT * FROM {forumtopic} WHERE forumtopicid=?", $topicid);
            $smarty->assign('action', 'reply');
            $smarty->assign('topic', $topic);
            $content['javascript']  = $smarty->fetch('jojo_forum_post_reply_error_checking.tpl');

        /* NEW TOPIC */
        } elseif ($forumaction == 'new') {
            $forumid = Jojo::getFormData('id', false);
            $forum = Jojo::selectRow("SELECT * FROM {forum} WHERE forumid=?", $forumid);
            $smarty->assign('action', 'new');
            $smarty->assign('forum', $forum);
            $content['javascript']  = $smarty->fetch('jojo_forum_new_topic_error_checking.tpl');

        /* VIEW FORUM LIST (FORUM INDEX, DEFAULT) */
        } else {

            /* Get number of topics in all forums */
            $data = Jojo::selectQuery("SELECT `ft_forumid`, COUNT(*) AS num FROM {forumtopic} WHERE 1 GROUP BY `ft_forumid`");
            $numtopics = array();
            $n = count($data);
            for ($i=0;$i<$n;$i++) { //key the array by userid
                $numtopics[$data[$i]['ft_forumid']] = $data[$i]['num'];
            }

            /* Get forum times array from user - this is an array of the last time each topic was visited */
            if (isset($_USERID) && $_USERID!=0) {
                $forumtimesarr = Jojo::selectRow("SELECT `us_forumtimes` FROM {user} WHERE `userid`=?", $_USERID);
                $forumtimes    = array();
                $forumtimes    = unserialize($forumtimesarr['us_forumtimes']);
            }

            /* Main query to get all data from forums */
            $forums = Jojo::selectQuery("SELECT * FROM {forum} ORDER BY fm_order");
            $numshow = 0;
            $numview = 0;

            $n = count($forums);
            for ($i=0;$i<$n;$i++) {
                $forums[$i]['url'] =  Jojo::urlPrefix(false).Jojo_Plugin_Jojo_forum::getForumUrl($forums[$i]);
                $data                    = Jojo::selectQuery("SELECT COUNT(*) AS numtopics FROM {forumtopic} WHERE `ft_forumid`=? GROUP BY `ft_forumid`", $forums[$i]['forumid']);
                $forums[$i]['numtopics'] = isset($data[0]['numtopics']) ? $data[0]['numtopics'] : 0;;
                $data                    = Jojo::selectQuery("SELECT SUM(ft_numposts) AS numposts FROM {forumtopic} WHERE `ft_forumid`=? GROUP BY `ft_forumid`", $forums[$i]['forumid']);
                $forums[$i]['numposts']  = isset($data[0]['numposts']) ? $data[0]['numposts'] : 0;
                //$data = Jojo::selectQuery("SELECT MAX(ft_lastpostdate) AS lastpost FROM forumtopic WHERE ft_forumid=".$forums[$i]['forumid']." GROUP BY ft_forumid");
                //$forums[$i]['lastposttimestamp'] = $data[0]['lastpost'];

                /* last post details */
                $lastpost = Jojo::selectRow("SELECT * FROM {forumpost} WHERE `forumpostid`=?", $forums[$i]['fm_lastpostid']);
                if (count($lastpost)) {
                    Jojo_Plugin_Jojo_forum::getUserData($lastpost['fp_posterid']);
                    $forums[$i]['lastposttimestamp'] = Jojo::relativeDate(Jojo_Plugin_Jojo_forum::convertTimeZone($lastpost['fp_datetime'], $_SERVERTIMEZONE, $_USERTIMEZONE));
                    $forums[$i]['lastpost']          = Jojo::relativeDate(Jojo_Plugin_Jojo_forum::convertTimeZone($lastpost['fp_datetime'], $_SERVERTIMEZONE, $_USERTIMEZONE));
                    $forums[$i]['lastposterid']      = $lastpost['fp_posterid'];
                    $forums[$i]['lastposter']        = ($lastpost['fp_posterid']) ? $posters[$lastpost['fp_posterid']]['login'] : $lastpost['fp_postername'];
                    $forums[$i]['lastposterurl']     = ($lastpost['fp_posterid']) ? $posters[$lastpost['fp_posterid']]['url'] : '';
                }
                $forums[$i]['fresh'] = (isset($forums[$i]['lastposttimestamp']) && isset($forumtimes[$forums[$i]['forumid']]) && ($forums[$i]['lastposttimestamp'] > $forumtimes[$forums[$i]['forumid']]));

                /* Check if the user has permission to this forum */
                $forumPermissions = $forumperms->getPermissions('forum', $forums[$i]['forumid']);

                if ($forumperms->hasPerm($_USERGROUPS, 'show')) {
                    $forums[$i]['showpermission'] = true;
                    $numshow++;
                }

                if ($forumperms->hasPerm($_USERGROUPS, 'view')) {
                    $forums[$i]['viewpermission'] = true;
                    $numview++;
                }
            }

            $smarty->assign('forums',  $forums);
            $smarty->assign('action', 'index');
            $smarty->assign('numshow', $numshow);
            $smarty->assign('numview', $numview);
            $smarty->assign('content', $this->page['pg_body']);
            $content['rssicon']['All forum posts'] = _SITEURL.'/forums/rss/';
        }


        /* Stats for footer - present on all forum pages */

        /* Find username - check $posters array first to save a query */
        if (isset($_USERID)) {
            $smarty->assign('userid', $_USERID);
           if (isset($posters[$_USERID])  && $_USERID!=0) {
                $smarty->assign('username', $posters[$_USERID]['login']);
                $smarty->assign('userurl', $posters[$_USERID]['url']);
            } elseif ($_USERID!=0) {
                $thisuser = Jojo::selectRow("SELECT `us_login` FROM {user} WHERE `userid`=?", $_USERID);
                $smarty->assign('username', $thisuser['us_login']);
                $smarty->assign('userurl', Jojo::rewrite('profile', $_USERID, $thisuser['us_login']));
            }
            /* Find usergroups and send to smarty */
            foreach ($_USERGROUPS as $k => $group) {
                $smarty->assign('group_' . $group, true);
            }
        }

        $content['content'] = $smarty->fetch('jojo_forum.tpl');

        /* at the end of each page request, process some subscription notifications (to spread the load) */
        $this->processSubscriptionEmails();

        return $content;
    }

    function sitemap($sitemap)
    {
        global $_USERGROUPS;
        $perms     = new Jojo_Permissions();
        $forumtree = new hktree();
        $forumtree->addNode('index', 0, 'Forum Index', 'forums/');
        $forums = Jojo::selectQuery("SELECT * FROM {forum} ORDER BY `fm_order`");
        $n = count($forums);
        for ($i = 0; $i < $n; $i++) {
            /* ensure user has permission to show forums */
            $perms->getPermissions('forum', $forums[$i]['forumid']);
            if ($perms->hasPerm($_USERGROUPS, 'show')) {
                $forums[$i]['url'] = $forums[$i]['fm_parent'] == 0 ? '' :  Jojo::urlPrefix(false).Jojo_Plugin_Jojo_forum::getForumUrl($forums[$i]);
                $forumtree->addNode($forums[$i]['forumid'], $forums[$i]['fm_parent'], $forums[$i]['fm_name'], $forums[$i]['url']);
            }
        }

        /* Add to the sitemap array */
        $sitemapsection = array();
        $sitemapsection['title']  = 'Forums';
        $sitemapsection['tree']   = $forumtree->asArray();
        $sitemapsection['order']  = 4;
        $sitemapsection['header'] = '';
        $sitemapsection['footer'] = '';
        $sitemap[]                = $sitemapsection;

        return $sitemap;
    }

    function xmlSitemap($sitemap)
    {
        /* Forums */
        $data = Jojo::selectQuery("SELECT * FROM {forum}");
        $n = count($data);
        for ($i=0;$i<$n;$i++) {
            $url = _SITEURL . '/'. Jojo_Plugin_Jojo_forum::getForumUrl($data[$i]);
            $lastmod = '';
            $priority = 0.7;
            $sitemap[$url] = array($url, $lastmod, 'weekly', $priority);
        }

        /* Forum Topics */
        $data = Jojo::selectQuery("SELECT * FROM {forumtopic}");
        $n = count($data);
        for ($i = 0; $i < $n; $i++) {
            $url = _SITEURL . '/'. Jojo::rewrite('topics', $data[$i]['forumtopicid'], $data[$i]['ft_title'], '');
            $lastmod = '';
            $priority = 0.4;
            $sitemap[$url] = array($url, $lastmod, 'weekly', $priority);
        }
        return $sitemap;
    }

    /**
     * Site Search
     *
     */
    function search($results, $keywords)
    {
        global $_USERGROUPS;
        $keywords_str = implode(' ', $keywords);

        $data = Jojo::selectQuery("SELECT
                                    forumpostid, fp_topicid, fp_body, ft.ft_forumid, ft.ft_title,
                                    MATCH(fp_body) AGAINST (?)  AS relevance,
                                    MATCH(ft.ft_title) AGAINST (?) * 100 AS relevance_topic
                                   FROM
                                    {forumpost} fp
                                   LEFT JOIN {forumtopic} ft ON (fp_topicid=forumtopicid)
                                   WHERE
                                    MATCH(fp_body) AGAINST (?) > 0
                                    OR
                                    MATCH(ft.ft_title) AGAINST (?) > 0
                                   ORDER BY
                                    relevance + relevance_topic DESC
                                   LIMIT 100",
                                   array($keywords_str, $keywords_str, $keywords_str, $keywords_str));

        foreach ($data as $d) {
            /* check forum Security - don't list topics in forums you don't have access to */
            $forumperms = new Jojo_Permissions();
            $forumPermissions = $forumperms->getPermissions('forum', $d['ft_forumid']);
            if (!$forumperms->hasPerm($_USERGROUPS, 'view')) continue;

            /* what page of the topic is our post on? */
            $postsperpage = Jojo::getOption('forum_posts_per_page');
            $postsperpage = empty($postsperpage) ? 10 : $postsperpage;
            $pagenumber = 1;
            $posts = Jojo::selectQuery("SELECT forumpostid FROM {forumpost} WHERE fp_topicid=? ORDER BY `fp_datetime`", $d['fp_topicid']);
            foreach ($posts as $i => $post) {
                if ($post['forumpostid'] == $d['forumpostid']) {
                    $pagenumber = ceil(($i+1) / $postsperpage);
                    break;
                }
            }

            $result = array();
            $result['relevance']   = $d['relevance'] + $d['relevance_topic'];
            $result['title']       = $d['ft_title'];
            $result['body']        = $d['fp_body'];
            $result['url']         = Jojo::rewrite('topics', $d['fp_topicid'], $d['ft_title'], '', '', $pagenumber);
            $result['absoluteurl'] = _SITEURL . '/' . $result['url'];
            $result['type']        = 'forum';
            $results[]             = $result;
        }

        return $results;
    }

    /*
     * function accepts either an ID, or an array (dataset from previous query) as first argument. Using an array saves an extra query
     */
    function getForumUrl($data, $pagenumber = 1)
    {
        static $_cache;

        if (is_array($data)) {
            $forum = $data;
            if (!isset($data['forumid'])) {
                return false;
            }
            $forumid = $data['forumid'];
        } else {
            $forumid = $data;
            $forum = Jojo::selectRow("SELECT * FROM {forum} WHERE forumid = ?", $forumid);
            if (!$forum) {
                return false;
            }
        }

        if (isset($_cache[$forumid][$pagenumber])) {
            return $_cache[$forumid][$pagenumber];
        }

        if (!empty($forum['fm_url'])) {
            $url = ($pagenumber > 1) ? 'forums/' . $forum['fm_url'] . '/p' . $pagenumber . '/' : 'forums/' . $forum['fm_url'] . '/';
            $_cache[$forumid][$pagenumber] = $url;
            return $url;
        }
        if (isset($forum['fm_name'])) {
            $url =  Jojo::rewrite('forums', $forumid, $forum['fm_name'], '', '', $pagenumber);
            $_cache[$forumid][$pagenumber] = $url;
            return $url;
        }
        return false;
    }

    function getCorrectUrl()
    {
        $id          = Jojo::getFormData('id', 0);
        $forumaction = Jojo::getFormData('action', 'forums');
        $forumurl    = Jojo::getFormData('forumurl', '');
        $pagenumber  = Jojo::getFormData('pagenumber', 1);

        if ($forumaction == 'reply') {
            return _PROTOCOL.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        } elseif ($forumaction == 'new') {
            return _PROTOCOL.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        } elseif ($forumaction == 'edit-post') {
            return _SITEURL . '/' . Jojo::rewrite('edit-post', $id, 'index', '');
        } elseif ($forumaction == 'editpost') {
            return _SITEURL . '/' . Jojo::rewrite('edit-post', $id, 'index', '');
        } elseif ($forumaction == 'edit-topic') {
            return _SITEURL . '/' . Jojo::rewrite('edit-topic', $id, 'index', '');
        } elseif ($forumaction == 'edittopic') {
            return _SITEURL . '/' . Jojo::rewrite('edit-topic', $id, 'index', '');
        } elseif ($forumaction == 'delete-post') {
            return _SITEURL . '/' . Jojo::rewrite('delete-post', $id, 'index', '');
        } elseif ($forumaction == 'profiles') {
            $profile = Jojo::selectRow("SELECT * FROM {user} WHERE userid = ?", $id);
            if ($profile) {
                return _SITEURL . '/' . Jojo::rewrite('user-profile', $profile['userid'], $profile['us_login'], '');
            }
        } elseif (($forumaction == 'topics')) {
            $topic = Jojo::selectRow("SELECT * FROM {forumtopic} WHERE forumtopicid = ?", $id);
            if ($topic) {
                return _SITEURL . '/' . Jojo::rewrite('topics', $topic['forumtopicid'], $topic['ft_title'], '', '', $pagenumber);
            }
        } elseif (($forumaction == 'postreply')) {
            $topic = Jojo::selectRow("SELECT * FROM {forumtopic} WHERE forumtopicid = ?", $id);
            if ($topic) {
                return _SITEURL . '/forums/reply/'.$id.'/';
            }
        } elseif (($forumaction == 'subscribe') || ($forumaction == 'unsubscribe')) {
            $topic = Jojo::selectRow("SELECT * FROM {forumtopic} WHERE forumtopicid = ?", $id);
            if ($topic) {
                return _SITEURL . '/' . Jojo::rewrite('topics', $topic['forumtopicid'], $topic['ft_title'], '', '', $pagenumber) . $forumaction . '/';
            }
        } elseif (!empty($forumurl)) {
            $forum = Jojo::selectRow("SELECT * FROM {forum} WHERE fm_url = ?", $forumurl);
            if ($forum) {
                return _SITEURL . '/' . Jojo_Plugin_Jojo_forum::getForumUrl($forum, $pagenumber);
            }
        } elseif (($forumaction == 'forums') && ($id > 0)) {
            return _SITEURL . '/' . Jojo_Plugin_Jojo_forum::getForumUrl($id, $pagenumber);
        } elseif ($forumaction == 'newtopic') {
            $forum = Jojo::selectRow("SELECT * FROM {forum} WHERE forumid = ?", $id);
            if ($forum) {
                //return _SITEURL . '/' . Jojo::rewrite('forums', $forum['forumid'], $forum['fm_name'], '', '', $pagenumber);
                return _SITEURL . '/forums/new/'.$id.'/';
            }
        }
        return parent::getCorrectUrl();
    }

}

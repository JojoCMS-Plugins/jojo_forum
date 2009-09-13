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
 * did not receive this file, see http:/* www.fsf.org/copyleft/lgpl.html.
 *
 * @author  Harvey Kane <code@ragepank.com>
 * @author  Michael Cochrane <code@gardyneholt.co.nz>
 * @author  Melanie Schulz <mel@gardyneholt.co.nz>
 * @license http:/* www.fsf.org/copyleft/lgpl.html GNU Lesser General Public License
 * @link    http:/* www.jojocms.org JojoCMS
 */



$table = 'forum';
$o = 1;

$default_td[$table]['td_displayfield']     = 'fm_name';
$default_td[$table]['td_parentfield']      = 'fm_parent';
$default_td[$table]['td_rolloverfield']    = 'fm_desc';
$default_td[$table]['td_orderbyfields']    = 'fm_order, fm_name';
$default_td[$table]['td_topsubmit']        = 'yes';
$default_td[$table]['td_filter']           = 'yes';
$default_td[$table]['td_deleteoption']     = 'yes';
$default_td[$table]['td_menutype']         = 'tree';
$default_td[$table]['td_categoryfield']    = '';
$default_td[$table]['td_categorytable']    = '';
$default_td[$table]['td_group1']           = '';
$default_td[$table]['td_help']             = 'Manage the forums. The top level of forums are the categories, which do not contain topics.';

/* Forum ID */
$field = 'forumid';
$default_fd[$table][$field]['fd_order']    = $o++;
$default_fd[$table][$field]['fd_type']     = 'readonly';
$default_fd[$table][$field]['fd_help']     = 'A unique ID, automatically assigned by the system';
$default_fd[$table][$field]['fd_mode']     = 'advanced';

/* Name */
$field = 'fm_name';
$default_fd[$table][$field]['fd_order']     = $o++;
$default_fd[$table][$field]['fd_type']     = 'text';
$default_fd[$table][$field]['fd_required'] = 'yes';
$default_fd[$table][$field]['fd_size']     = '40';
$default_fd[$table][$field]['fd_help']     = 'Title of the forum. This will be used for the filename, titles and search engines. Search Engine ranking may be lost if this field is changed.';
$default_fd[$table][$field]['fd_mode']     = 'basic';

/* SEO Title */
$field = 'fm_seotitle';
$default_fd[$table][$field]['fd_order']    = $o++;
$default_fd[$table][$field]['fd_type']     = 'text';
$default_fd[$table][$field]['fd_required'] = 'no';
$default_fd[$table][$field]['fd_size']     = '50';
$default_fd[$table][$field]['fd_help']     = 'SEO Title of the forum - leave blank for default, or include search phrases for the search engines';
$default_fd[$table][$field]['fd_options']  = 'seotitle';
$default_fd[$table][$field]['fd_mode']     = 'standard';

/* URL */
$field = 'fm_url';
$default_fd[$table][$field]['fd_order']    = $o++;
$default_fd[$table][$field]['fd_type']     = 'internalurl';
$default_fd[$table][$field]['fd_required'] = 'yes';
$default_fd[$table][$field]['fd_size']     = '40';
$default_fd[$table][$field]['fd_help']     = 'A friendly URL for this forum';
$default_fd[$table][$field]['fd_options']  = 'forums';
$default_fd[$table][$field]['fd_mode']     = 'standard';

/* Description */
$field = 'fm_desc';
$default_fd[$table][$field]['fd_order']    = $o++;
$default_fd[$table][$field]['fd_type']     = 'text';
$default_fd[$table][$field]['fd_size']     = '40';
$default_fd[$table][$field]['fd_help']     = 'A one sentence description of the forum';
$default_fd[$table][$field]['fd_mode']     = 'standard';

/* Parent */
$field = 'fm_parent';
$default_fd[$table][$field]['fd_order']    = $o++;
$default_fd[$table][$field]['fd_type']     = 'dblist';
$default_fd[$table][$field]['fd_options']  = 'forum';
$default_fd[$table][$field]['fd_name']     = 'Parent Forum';
$default_fd[$table][$field]['fd_help']     = '';
$default_fd[$table][$field]['fd_mode']     = 'basic';

/* BB Body */
$field = 'fm_bbbody';
$default_fd[$table][$field]['fd_order']    = $o++;
$default_fd[$table][$field]['fd_type']     = 'texteditor';
$default_fd[$table][$field]['fd_options']  = 'fm_body';
$default_fd[$table][$field]['fd_rows']     = '10';
$default_fd[$table][$field]['fd_cols']     = '50';
$default_fd[$table][$field]['fd_help']     = 'The body of the forum';
$default_fd[$table][$field]['fd_mode']     = 'advanced';

/* Body */
$field = 'fm_body';
$default_fd[$table][$field]['fd_order']    = $o++;
$default_fd[$table][$field]['fd_type']     = 'hidden';
$default_fd[$table][$field]['fd_rows']     = '10';
$default_fd[$table][$field]['fd_cols']     = '50';
$default_fd[$table][$field]['fd_help']     = '';
$default_fd[$table][$field]['fd_mode']     = 'advanced';

/* Status */
$field = 'fm_status';
$default_fd[$table][$field]['fd_order']    = $o++;
$default_fd[$table][$field]['fd_mode']     = 'standard';

/* Order */
$field = 'fm_order';
$default_fd[$table][$field]['fd_order']    = $o++;
$default_fd[$table][$field]['fd_mode']     = 'standard';

/* Permissions */
$field = 'fm_permissions';
$default_fd[$table][$field]['fd_order']    = $o++;
$default_fd[$table][$field]['fd_mode']     = 'standard';
$default_fd[$table][$field]['fd_tab']      = 'Permissions';

/* Last Post ID */
$field = 'fm_lastpostid';
$default_fd[$table][$field]['fd_order']    = $o++;
$default_fd[$table][$field]['fd_mode']     = 'standard';
$default_fd[$table][$field]['fd_type']     = 'hidden';

/* Image */
$field = 'fm_image1';
$default_fd[$table][$field]['fd_order']    = $o++;
$default_fd[$table][$field]['fd_type']     = 'fileupload';
$default_fd[$table][$field]['fd_help']     = 'An image for the forum, if available';
$default_fd[$table][$field]['fd_mode']     = 'standard';

/* Auth Reply */
$field = 'auth_reply';
$default_fd[$table][$field]['fd_order']    = $o++;
$default_fd[$table][$field]['fd_mode']     = 'advanced';
$default_fd[$table][$field]['fd_type']     = 'hidden';
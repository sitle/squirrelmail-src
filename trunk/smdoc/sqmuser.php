<?php
/*
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 * 
 * This file is an addition to the
 * Framework for Object Orientated Web Development (Foowd).
 */

/**
 * Alternate entry point providing list of registered users.
 *
 * $Id$
 *
 * Lists users of system,
 * Provides stats based on user choices for selected elements.
 * Shows IRC ids.
 * 
 * Values set in template:
 *  + userlist      - below
 *  + usercount     - number of registered users
 *  + user_smver    - array containing stats for each SM Version
 *  + user_smtp     - array containing stats for each SMTP Server
 *  + user_imap     - array containing stats for each IMAP Server
 *  + body_template - specific filename (will be relative to TEMPLATE PATH)
 *  + method        - empty string
 *  + title         - 'User List'
 *
 * Sample contents of $t['userlist']:
 * <pre>
 * array (
 *   0 => array ( 
 *          'title' => 'Username'
 *          'objectid' => 1287432
 *          'IRC' => ''
 *        )
 * )
 * </pre>
 * 
 * @package smdoc
 * @subpackage extern
 */

/** 
 * Initial configuration, start session
 * @see config.default.php
 */
require('config.php');

/* 
 * Initialize smdoc/FOOWD environment
 */
$foowd_parameters['debug']['debug_enabled'] = TRUE;
$foowd = new smdoc($foowd_parameters);

/**
 * Reference global var containing user database
 * @global array $USER_SOURCE
 */
global $USER_SOURCE;
 
/*
 * No special indices, use user source, no special where clause,
 * order by title, no limit,
 * don't objects, and don't restrict to certain workspace
 */
$indices = array('objectid','title','IRC');
$objects =& $foowd->getObjList($indices, $USER_SOURCE, NULL,
                               array('title'), NULL, 
                               FALSE, FALSE );

$foowd->template->assign_by_ref('user_list', $objects);

$num_users = $foowd->database->count($USER_SOURCE);
$foowd->template->assign('user_count', $num_users);

$smver[] = $foowd->database->count($USER_SOURCE, array('SM_version' => 0));
$smver[] = $foowd->database->count($USER_SOURCE, array('SM_version' => 1));
$smver[] = $foowd->database->count($USER_SOURCE, array('SM_version' => 2));
$smver[] = $foowd->database->count($USER_SOURCE, array('SM_version' => 3));
$smver[] = $foowd->database->count($USER_SOURCE, array('SM_version' => 4));
$smver[] = $foowd->database->count($USER_SOURCE, array('SM_version' => 5));
$smver[] = $foowd->database->count($USER_SOURCE, array('SM_version' => 6));
$foowd->template->assign_by_ref('user_smver', $smver);

$imap[] = $foowd->database->count($USER_SOURCE, array('IMAP_server' => 0));
$imap[] = $foowd->database->count($USER_SOURCE, array('IMAP_server' => 1));
$imap[] = $foowd->database->count($USER_SOURCE, array('IMAP_server' => 2));
$imap[] = $foowd->database->count($USER_SOURCE, array('IMAP_server' => 3));
$imap[] = $foowd->database->count($USER_SOURCE, array('IMAP_server' => 4));
$imap[] = $foowd->database->count($USER_SOURCE, array('IMAP_server' => 5));
$imap[] = $foowd->database->count($USER_SOURCE, array('IMAP_server' => 6));
$imap[] = $foowd->database->count($USER_SOURCE, array('IMAP_server' => 7));
$foowd->template->assign_by_ref('user_imap', $imap);

$smtp[] = $foowd->database->count($USER_SOURCE, array('SMTP_server' => 0));
$smtp[] = $foowd->database->count($USER_SOURCE, array('SMTP_server' => 1));
$smtp[] = $foowd->database->count($USER_SOURCE, array('SMTP_server' => 2));
$smtp[] = $foowd->database->count($USER_SOURCE, array('SMTP_server' => 3));
$smtp[] = $foowd->database->count($USER_SOURCE, array('SMTP_server' => 4));
$smtp[] = $foowd->database->count($USER_SOURCE, array('SMTP_server' => 5));
$smtp[] = $foowd->database->count($USER_SOURCE, array('SMTP_server' => 6));
$smtp[] = $foowd->database->count($USER_SOURCE, array('SMTP_server' => 7));
$smtp[] = $foowd->database->count($USER_SOURCE, array('SMTP_server' => 8));
$foowd->template->assign_by_ref('user_smtp', $smtp);


$foowd->template->assign('title', _("User List"));
$foowd->template->assign('method', '');
$foowd->template->assign('body_template', 'smdoc_external.user.tpl');

$foowd->template->display();

/*
 * destroy Foowd - triggers cleanup of database object and 
 * display of debug information.
 */
$foowd->__destruct();


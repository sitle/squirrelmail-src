<?php
/*
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 * 
 * This file is an addition to the
 * Framework for Object Orientated Web Development (Foowd).
 */

/**
 * External Resource providing list of registered users.
 *
 * $Id$
 * 
 * @package smdoc
 * @subpackage extern
 */

/** Objectid for smdoc user list. */
define('SQMUSER_ID',1307013381);

/** Additions to the list of external resources */
$EXTERNAL_RESOURCES['sqmuser'] = SQMUSER_ID;
$EXTERNAL_RESOURCES[SQMUSER_ID]['func'] = 'sqmuser';
$EXTERNAL_RESOURCES[SQMUSER_ID]['title'] = 'Users';

/**
 * Lists users of system,
 * Provides stats based on user choices for selected elements.
 * Shows IRC ids.
 * 
 * Fills in $t['userlist'] (see below),
 *          $t['user_count'] with the total number of users,
 *          $t['user_smver'] with an array containing the 
 * total number choosing each available SM version,
 *          $t['user_imap'] and $t['user_smtp'] are similar to
 * $t['user_smver'] regarding choices for imap and smtp servers.
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
 * @param smdoc foowd Reference to the foowd environment object.
 */
function sqmuser(&$foowd) 
{
  $foowd->track('sqmuser');
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

  $foowd->template->assign('body_template', 'smdoc_external.user.tpl');
  $foowd->track();
}
?>

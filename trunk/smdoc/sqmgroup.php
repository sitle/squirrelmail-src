<?php
/*
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 * 
 * This file is an addition to the
 * Framework for Object Orientated Web Development (Foowd).
 */

/**
 * Alternate entry point providing list of groups.
 *
 * $Id$
 * 
 * Lists groups of system, allows groups to be added and removed.
 * Also allows group members to be fetched and manipulated.
 * 
 * Sample contents of $t['grouplist']:
 * <pre>
 * array (
 *   0 => array ( 
 *          'title' => 'Group Name'
 *          'count' => 'number of members'
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
 * @global $USER_GROUP_SOURCE
 */
global $USER_GROUP_SOURCE;

/*
 * Get list of groups that users can be assigned to
 */
$groups = $foowd->getUserGroups(FALSE);

/*
 * Accrue number of users in each group, 
 * along with group display name, and links for group delete 
 * and group member edit.
 */
$grouplist = array();
foreach ( $groups as $id => $name )
{
  $group = array();
  $group['name'] = $name;
  $group['count'] = $foowd->database->count($USER_GROUP_SOURCE, 
                                            array('title' => $id));
  $grouplist['id'] = $group;
}

$foowd->template->assign('title', _("Group List"));
$foowd->template->assign('method', '');
$foowd->template->assign('body_template', 'smdoc_external.group.tpl');

$foowd->template->display();

/*
 * destroy Foowd - triggers cleanup of database object and 
 * display of debug information.
 */
$foowd->destroy();


<?php
/*
 * Copyright (c) 2003-2004 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 * 
 * This file is an addition to the
 * Framework for Object Orientated Web Development (Foowd).
 */

/**
 * Alternate entry point providing links to various tools
 *
 * $Id$
 *
 * Values set in template:
 *  + body_template - specific filename (will be relative to TEMPLATE PATH)
 *  + method        - empty string
 *  + title         - 'Site Index'
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
$foowd = new smdoc($foowd_parameters);
$loc_url = getURI();

/* 
 * Links for classes user has permission to create instance of.. 
 * Special 
 */
$classes = getFoowdClassNames();

foreach ( $classes as $classid => $className )
{
  if ( strpos($className, 'user') === false  &&   // NOT user classes
       $foowd->hasPermission($className, 'create', 'CLASS') )
  {
    $create_list[$className] = getClassDescription($classid);
  }
}
$foowd->template->assign_by_ref('create_list', $create_list);


/*
 * Admin links
 */
$admin_link = array();
if ( $foowd->hasPermission('smdoc_group_user','list','CLASS') )
  $admin_link[] = '<a href="'.$loc_url.'?class=smdoc_group_user&method=list">'._("User Groups").'</a>';
if ( $foowd->hasPermission('smdoc_name_lookup','list','CLASS') )
  $admin_link[] = '<a href="'.$loc_url.'?class=smdoc_name_lookup&method=list">'._("Short Names").'</a>';
if ( $foowd->user->inGroup('Gods') )
  $admin_link[] = '<a href="sqmindex.php?p=1">'._("Full Document Index").'</a>';

$foowd->template->assign_by_ref('admin_list', $admin_link);


/*
 * Assign remaining template variables
 * Invoke display
 */
$foowd->template->assign('title', _("Tools"));
$foowd->template->assign('method', '');
$foowd->template->assign('body_template', 'smdoc_external.tools.tpl');
$foowd->template->display();

/*
 * destroy Foowd - triggers cleanup of database object and 
 * display of debug information.
 */
$foowd->__destruct();


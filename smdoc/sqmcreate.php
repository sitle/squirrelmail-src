<?php
/*
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 * 
 * This file is an addition to the
 * Framework for Object Orientated Web Development (Foowd).
 */


/**
 * Alternate entry point providing links for document creation
 *
 * $Id$
 *
 * Values set in template:
 *  + classlist     - below
 *  + body_template - specific filename (will be relative to TEMPLATE PATH)
 *  + method        - empty string
 *  + title         - 'Create New Resource'
 *
 * Sample contents of $t['classlist']:
 * <pre>
 * array (
 *   classname => <description>
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
$foowd = new smdoc($foowd_parameters);

$obj = NULL;
$class_list = array();
$classes = getFoowdClassNames();

foreach ( $classes as $classid => $className )
{
  if ( $foowd->hasPermission($className, 'create', 'class', $obj) &&
       strpos($className, 'user') === false )
  {
    $class_list[$className] = getClassDescription($classid);
  }
}

$foowd->template->assign('title',  _("Create New Resource"));
$foowd->template->assign('method', '');
$foowd->template->assign_by_ref('classlist', $class_list);
$foowd->template->assign('body_template', 'smdoc_external.create.tpl');

$foowd->template->display();

/*
 * destroy Foowd - triggers cleanup of database object and 
 * display of debug information.
 */
$foowd->__destruct();


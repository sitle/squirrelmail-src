<?php
/*
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition/modification to the 
 * Framework for Object Orientated Web Development (Foowd).
 */

/** 
 * Alternate entry point for viewing phpdoc-generated
 * documentation for smdoc framework. 
 *
 * Modified by SquirrelMail Development
 * $Id$
 * 
 * @package smdoc
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

$foowd->template->assign('title', 'SquirrelMail Documentation Framework');

$string = 'Packages: ' . 
          '<a href="docs/li_smdoc.html" target="left_bottom">smdoc</a> | ' .
          '<a href="docs/li_Foowd.html" target="left_bottom">Foowd</a>';

$foowd->template->assign('method', $string);
$foowd->template->assign('body_template','smdoc_external.docs.tpl');

$foowd->template->display();

/*
 * destroy Foowd - triggers cleanup of database object and 
 * display of debug information.
 */
$foowd->__destruct();

?>

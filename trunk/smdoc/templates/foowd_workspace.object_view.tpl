<?php
/*
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition/modification to the 
 * Framework for Object Orientated Web Development (Foowd).
 */

/** 
 * Template for workspace view
 *
 * Modified by SquirrelMail Development
 * $Id$
 * 
 * @package smdoc
 * @subpackage template
 */
$t['body_function'] = 'workspace_view_body';

/** Include base template */
include_once(TEMPLATE_PATH.'index.tpl');

/**
 * Base template will call back to this function
 *
 * @param smdoc $foowd Reference to the foowd environment object.
 * @param string $className String containing invoked className.
 * @param string $method String containing called method name.
 * @param smdoc_user $user Reference to active user.
 * @param object $object Reference to object being invoked.
 * @param mixed $t Reference to array filled with template parameters.
 */
function workspace_view_body(&$foowd, $className, $method, &$user, &$object, &$t)
{
show($t);
}
?>

<?php
/*
 * Copyright (c) 2003-2004 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition/modification to the 
 * Framework for Object Orientated Web Development (Foowd).
 */

/** 
 * Template for default content
 *
 * Modified by SquirrelMail Development
 * $Id$
 * 
 * @package smdoc
 * @subpackage template
 */

$t['body_function'] = 'default_body';

/** Include base template */
include_once(TEMPLATE_PATH.'index.tpl');

/** 
 * Base template will call back to this function
 * 
 * @param smdoc $foowd Reference to the foowd environment object.
 * @param string className String containing invoked className.
 * @param string method String containing called method name.
 * @param smdoc_user user Reference to active user.
 * @param object object Reference to object being invoked.
 * @param mixed t Reference to array filled with template parameters.
 */
function default_body(&$foowd, $className, $method, &$user, &$object, &$t)
{
    echo '<h3>Default Object Display</h3>';

    if ( isset($object) )
    {
      echo '<pre>Class: ', $className, "\n", 'Method: ',  $method, '</pre>';
      echo '<div class="debug_output_heading">', _("Contents of Current Object"), '</div>';
      show($object);
    }
}

?>

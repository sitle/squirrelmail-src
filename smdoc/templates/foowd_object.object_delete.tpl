<?php
/*
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition/modification to the 
 * Framework for Object Orientated Web Development (Foowd).
 */

/** 
 * Template for object delete
 *
 * Modified by SquirrelMail Development
 * $Id$
 * 
 * @package smdoc
 * @subpackage template
 */

$t['body_function'] = 'object_delete_body';

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
function object_delete_body(&$foowd, $className, $method, &$user, &$object, &$t)
{
  $t['form']->display_start('smdoc_form');

  echo '<p>'. _("Are you sure you want to delete <b>all versions</b> of this object?") . '</p>';

  echo "\n" . '<div class="form_submit">';
  $t['form']->display_buttons();
  echo '</div>'."\n";

  $t['form']->display_end();
}

?>

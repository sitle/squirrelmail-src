<?php
/*
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition/modification to the 
 * Framework for Object Orientated Web Development (Foowd).
 */

/** 
 * Template for object clone
 *
 * Modified by SquirrelMail Development
 * $Id$
 * 
 * @package smdoc
 * @subpackage template
 */

$t['body_function'] = 'object_clone_body';

/** Include base template */
include(TEMPLATE_PATH.'index.tpl');

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
function object_clone_body(&$foowd, $className, $method, &$user, &$object, &$t)
{
  echo '<h1>' . _("Clone object: ") . $t['title'], '</h1>';

  $t['form']->display_start('smdoc_form');

  echo '<p>'. _("New Title for Clone") . ': ';
  $t['form']->objects['cloneTitle']->display();

  echo "</p>\n<p>". _("Target Translation/Workspace") . ': ';
  $t['form']->objects['workspaceDropdown']->display();

  echo "\n" . '<div class="form_submit">';
  $t['form']->display_buttons();
  echo '</div>'."\n";
  $t['form']->display_end();
}
?>

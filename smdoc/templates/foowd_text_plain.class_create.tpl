<?php
/*
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition/modification to the
 * Framework for Object Orientated Web Development (Foowd).
 */

/**
 * Template for user creation
 *
 * Modified by SquirrelMail Development
 * $Id$
 *
 * @package smdoc
 * @subpackage template
 */
$t['title'] = _("Create") . ': ' . $t['className'];
$t['method'] = 'create';
$t['body_function'] = 'text_plain_create_body';

/** Include base template */
include(TEMPLATE_PATH.'index.tpl');

/**
 * Base template will call back to this function
 *
 * @param smdoc foowd Reference to the foowd environment object.
 * @param string className String containing invoked className.
 * @param string method String containing called method name.
 * @param smdoc_user user Reference to active user.
 * @param object object Reference to object being invoked.
 * @param mixed t Reference to array filled with template parameters.
 */
function text_plain_create_body(&$foowd, $className, $method, $user, &$object, &$t)
{
  $t['form']->display_start('smdoc_form');
  $obj = $t['form']->objects;
?>

<p align="center">
<span class="label"><b><?php echo _("Object Title"); ?>:</b></span>
<span class="value"><?php echo $obj['createTitle']->display(); ?></span>
</p>

<table cellspacing="0" cellpadding="0" class="smdoc_table" style="width: 100%">
<tr><td><div class="separator"><?php echo _("Body Text"); ?></div></td></tr>
<tr><td align="center"><?php echo $obj['createBody']->display('smdoc_textarea'); ?></td></tr>
</table>
<?php
  echo "\n" . '<div class="form_submit">';
  $t['form']->display_buttons();
  echo '</div>'."\n";

  $t['form']->display_end();
}

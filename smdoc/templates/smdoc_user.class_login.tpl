<?php
/*
 * Copyright (c) 2003-2004 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition/modification to the
 * Framework for Object Orientated Web Development (Foowd).
 */

/**
 * Template for user login
 *
 * Modified by SquirrelMail Development
 * $Id$
 *
 * @package smdoc
 * @subpackage template
 */

$t['title'] = _("User Login");
$t['method'] = 'login';
$t['body_function'] = 'user_login_body';

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
function user_login_body(&$foowd, $className, $method, &$user, &$object, &$t)
{
  $t['form']->display_start('smdoc_form');
  $obj = $t['form']->objects;
?>

<table cellspacing="1" cellpadding="0" class="smdoc_table">
<tr><th class="label"><?php echo _("Login"); ?>:</td>
    <td class="value"><?php echo $obj['loginUsername']->display(); ?></td></tr>
<tr><th class="label"><?php echo _("Password"); ?>:</td>
    <td class="value"><?php echo $obj['loginPassword']->display(); ?></td></tr>
</table>

<?php
  echo "\n" . '<div class="form_submit">';
  $t['form']->display_buttons();
  echo '</div>'."\n";

  $url = getURI(array('class' => $className));
?>
<div class="formlinks">
<a href="<?php echo $url; ?>&method=create"><?php echo _("Create new account"); ?></a>
<a href="<?php echo $url; ?>&method=lostpassword"><?php echo _("Forgot your password?"); ?></a>
</div>
<?php
  $t['form']->display_end();
}



<?php
/*
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
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
function user_login_body(&$foowd, $className, $method, &$user, &$object, &$t)
{
  $t['form']->display_start('smdoc_form');
  $obj = $t['form']->objects;
?>

<table cellspacing="0" cellpadding="0" class="smdoc_table">
<tr><td class="label"><b><?php echo _("Login"); ?>:</b></td>
    <td class="value"><?php echo $obj['loginUsername']->display(); ?></td></tr>
<tr><td class="label"><b><?php echo _("Password"); ?>:</b></td>
    <td class="value"><?php echo $obj['loginPassword']->display(); ?></td></tr>
</table>

<?php
  echo "\n" . '<div class="form_submit">';
  $t['form']->display_buttons();
  echo '</div>'."\n";

  $url = getURI(array('class' => $className));
  echo '<p class="small_center"><a href="'.$url.'&method=create">'
       . _("Create new account.")
       . '</a><br />'
       . '<a href="'.$url.'&method=lostpassword">'
       . _("Forgot your password?")
       . '</a></p>';

  $t['form']->display_end();
}

?>

<?php
/*
 * Copyright (c) 2003-2004 The SquirrelMail Project Team
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
$t['title'] = _("Create New User");
$t['method'] = 'create';
$t['body_function'] = 'user_create_body';

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
function user_create_body(&$foowd, $className, $method, &$user, &$object, &$t)
{
  $t['form']->display_start('smdoc_form');
  $obj = $t['form']->objects;

?>

<table cellspacing="0" cellpadding="0" class="smdoc_table">
<tr><td class="label"><b><?php echo _("UserName"); ?>:</b></td>
    <td class="value"><?php echo $obj['createUsername']->display(); ?></td></tr>
<tr><td colspan="2">&nbsp;</td></tr>

<tr><td class="label"><b><?php echo _("Password"); ?>:</b></td>
    <td class="value"><?php echo $obj['createPassword']->display(); ?></td></tr>
<tr><td class="label"><b><?php echo _("Verify"); ?>:</b></td>
    <td class="value"><?php echo $obj['verifyPassword']->display(); ?></td></tr>
<tr><td colspan="2">&nbsp;</td></tr>

<tr><td class="label"><b><?php echo _("Email"); ?>:</b></td>
    <td class="value"><?php echo $obj['createEmail']->display(); ?>
                      <span class="subtext">(<a href="#email">privacy</a>)</span></td></tr>

</table>

<?php
  echo "\n" . '<div class="form_submit">';
  $t['form']->display_buttons();
  echo '</div>'."\n";

  $uri_arr['class'] = $className;
  $uri_arr['method'] = 'login';
  echo '<p class="small"><a href="'.getURI($uri_arr).'">'
       . _("Login with existing user.")
       . '</a></p>';

  $t['form']->display_end();
?>
<p class="subtext_center"><a id="email" name="email"></a>
<?php echo _("Your email address is not required, it is used for password recovery."); ?><br />
<?php echo sprintf(_("See our <a href=\"%s\">Privacy Policy</a>"),
                   getURI(array('object' => 'privacy'))); ?>
</p>
<?php
}

// vim: syntax=php

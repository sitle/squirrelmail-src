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
<tr><th class="label"><?php echo _("UserName"); ?>:</th>
    <td class="value"><?php echo $obj['createUsername']->display(); ?></td></tr>
<tr><td colspan="2">&nbsp;</td></tr>

<tr><th class="label"><?php echo _("Password"); ?>:</th>
    <td class="value"><?php echo $obj['createPassword']->display(); ?></td></tr>
<tr><th class="label"><?php echo _("Verify"); ?>:</th>
    <td class="value"><?php echo $obj['verifyPassword']->display(); ?></td></tr>
<tr><td colspan="2">&nbsp;</td></tr>

<tr><th class="label"><?php echo _("Email"); ?>:</th>
    <td class="value"><?php echo $obj['createEmail']->display(); ?><br />
    <span class="subtext">
    <?php echo _("Used only for password recovery."); ?> 
    <?php echo sprintf(_("See our <a href=\"%s\">Privacy Policy</a>."), getURI(array('object' => 'privacy'))); ?>
    </span>
    </td></tr>

</table>

<?php
  echo "\n" . '<div class="form_submit">';
  $t['form']->display_buttons();
  echo '</div>'."\n";

  $uri_arr['class'] = $className;
  $uri_arr['method'] = 'login';
?>
<div class="formlinks">
  <a href="<?php echo getURI($uri_arr); ?>"><?php echo _("Login as an existing user."); ?></a>
</div>

<?php
  $t['form']->display_end();
?>
</p>
<?php
}



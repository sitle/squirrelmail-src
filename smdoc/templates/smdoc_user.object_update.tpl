<?php
/*
 * Copyright (c) 2003-2004 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition/modification to the
 * Framework for Object Orientated Web Development (Foowd).
 */

/**
 * Template for performing object updates
 *
 * Modified by SquirrelMail Development
 * $Id$
 *
 * @package smdoc
 * @subpackage template
 */
$t['body_function'] = 'user_update_body';

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
function user_update_body(&$foowd, $className, $method, &$user, &$object, &$t)
{
  $t['form']->display_start('smdoc_form');
  $obj =& $t['form']->objects;
?>
<table cellspacing="0" cellpadding="0" class="smdoc_table">

<tr class="separator"><th colspan="2"><?php echo _("Private Attributes"); ?></th></tr>

<tr><th class="label"><?php echo _("User Name");?>:</th>
    <td class="value"><?php echo $obj['title']->display(); ?></td></tr>
<tr><td colspan="2">&nbsp;</td></tr>
<tr><th class="label"><?php echo _("Change Password");?>:</th>
    <td class="value"><?php echo $obj['password']->display(); ?></td></tr>
<tr><th class="label"><?php echo _("Verify New Password");?>:</th>
    <td class="value"><?php echo $obj['verify']->display(); ?></td></tr>
<tr><td colspan="2">&nbsp;</td></tr>

<tr><th class="label"><?php echo _("Email");?>:</th>
    <td class="value"><?php echo $obj['email']->display(); ?><br />
    <span class="subtext">
       <?php echo $obj['show_email']->display()
                  . _("Share email with public contact information?"); ?>
    </td></tr>

<tr class="separator"><th colspan="2"><?php echo _("Public Contact Information"); ?></th></tr>

<?php ksort($obj['nick']);
      foreach ( $obj['nick'] as $box )
      { ?>
<tr><th class="label"><?php echo $box->caption; ?>:</th>
    <td class="value">
<?php   echo $box->display();
        if ( $box->name == 'IRC' )
        { ?>
         <span class="subtext"> - #squirrelmail (<a href="http://freenode.net">irc.freenode.net</a>)</span>
<?php   } ?>
    </td></tr>
<?php } ?>

<tr class="separator"><th colspan="2"><?php echo _("Server/Version Statistics"); ?></th></tr>

<?php foreach ( $obj['stat'] as $box )
      { ?>
<tr><th class="label"><?php echo $box->caption; ?>:</td>
    <td class="value"><?php echo $box->display(); ?></td></tr>
<?php } ?>

</table>

<div class="form_submit"><?php $t['form']->display_buttons(); ?></div>

<?php 
    $t['form']->display_end();
}



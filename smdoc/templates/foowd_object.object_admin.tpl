<?php
/*
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition/modification to the 
 * Framework for Object Orientated Web Development (Foowd).
 */

/** 
 * Template for object admin
 *
 * Modified by SquirrelMail Development
 * $Id$
 * 
 * @package smdoc
 * @subpackage template
 */

$t['body_function'] = 'object_admin_body';

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
function object_admin_body(&$foowd, $className, $method, &$user, &$object, &$t)
{
  echo '<h1>' . _("Object Administration") . '</h1>' . "\n";
  $t['form']->display_start('smdoc_form');

  $obj =& $t['form']->objects;
?>
<table cellspacing="0" cellpadding="0" class="smdoc_table">

<tr><td colspan="2"><div class="separator"><?php echo _("Object Attributes"); ?></div></td></tr>
<tr><td class="label"><?php echo _("Title"); ?>:</td>
    <td class="value"><?php
        echo $obj['title']->display();
        echo '&nbsp;[' . $object->objectid . ']';
    ?></td></tr>
<tr><td class="label"><?php echo _("Version"); ?>:</td>
    <td class="value"><?php echo $obj['version']->display(NULL, 10); ?></td></tr>
<tr><td class="label"><?php echo _("Class"); ?>:</td>
    <td class="value"><?php echo $obj['classid']->display(); ?></td></tr>
<tr><td colspan="2">&nbsp;</td></tr>
<tr><td class="label"><?php echo _("Translation"); ?>:</td>
    <td class="value"><?php echo $obj['workspaceid']->display(); ?></td></tr>


<tr><td colspan="2">&nbsp;</td></tr>
<tr><td colspan="2"><div class="form_submit"><?php echo $t['form']->display_buttons(); ?></div></td></tr>

<tr><td colspan="2"><div class="separator"><?php echo _("Method Permissions"); ?></div></td></tr>
<?php foreach ( $obj['permissions'] as $method => $auth )
      { ?>
<tr><td class="label"><?php echo $method; ?>: </td>
    <td class="value"><?php echo $auth->display(); ?></td></tr>
<?php } ?>

</table>
<div class="form_submit"><?php $t['form']->display_buttons(); ?></div>
<?php
  $t['form']->display_end();
  $t['shortform']->display_start();
  $obj =& $t['shortform']->objects;
?>
<table cellspacing="0" cellpadding="0" class="smdoc_table">
<tr><td colspan="2"><div class="separator"><?php echo _("URL Modifier"); ?></div></td></tr>
<tr><td class="label"><?php echo _("URL Shortname"); ?>:</td>
    <td class="value"><?php echo $obj['shortname']->display(); ?>
        <span class="subtext">(privacy, faq, ...)</td></tr>
</tr>
</table>
<div class="form_submit"><?php $t['shortform']->display_buttons(); ?></div>
<?php
  $t['shortform']->display_end();
}

?>

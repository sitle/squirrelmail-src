<?php
/*
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition/modification to the
 * Framework for Object Orientated Web Development (Foowd).
 */

/**
 * Template for Textile document creation
 *
 * Modified by SquirrelMail Development
 * $Id$
 *
 * @package smdoc
 * @subpackage template
 */
$t['title'] = _("Create") . ': ' . $t['className'];
$t['method'] = 'create';
$t['body_function'] = 'text_textile_create_body';

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
function text_textile_create_body(&$foowd, $className, $method, $user, &$object, &$t)
{
  $obj = $t['form']->objects;
?>

<table cellspacing="0" cellpadding="0" class="smdoc_table" style="width: 100%">
<tr>
  <td class="col_top" align="center">
    <?php $t['form']->display_start('smdoc_form'); ?>
    <p>
      <span class="label"><?php echo _("Title"); ?>:</span>
      <span class="value"><?php echo $obj['createTitle']->display(); ?></span>
    </p>

    <div class="form_submit"><?php echo $t['form']->display_buttons(); ?></div>

    <p><?php echo $obj['createBody']->display('textile'); ?></p>

    <div class="form_submit"><?php echo $t['form']->display_buttons(); ?></div>

    <?php $t['form']->display_end(); ?>
  </td>
  <td class="textile_howto">
    <?php include(TEMPLATE_PATH.'smdoc_text_textile.howto.tpl'); ?>
  </td>
</tr>
</table>
<?php
}

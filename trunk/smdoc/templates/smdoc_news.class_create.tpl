<?php
/*
 * Copyright (c) 2003-2004 The SquirrelMail Project Team
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
 * @see index.tpl
 */
function text_textile_create_body(&$foowd, $className, $method, &$user, &$object, &$t)
{
  $obj = $t['form']->objects;
?>
<script language="JavaScript" type="text/javascript" src="templates/toggleNone.js"></script>
<table cellspacing="0" cellpadding="0" class="smdoc_table" style="width: 100%">
<tr>
<td class="col_top" align="center">
  <p><?php $t['form']->display_start('smdoc_form'); ?></p>
  <table cellspacing="0" cellpadding="0" class="smdoc_table">
  <tr><td class="label"><b><?php echo _("Headline"); ?>:</b></td>
      <td class="value"><?php echo $obj['createTitle']->display(); ?></td></tr>
  <tr><td colspan="2">&nbsp;</td></tr>
  <tr><td class="label"><b><?php echo _("Category"); ?>:</b></td>
      <td class="value"><?php echo $obj['createCategory']->display(); ?></td></tr>
  </table>
</td>
<td class="textile_howto" rowspan="2">
  <?php include_once(TEMPLATE_PATH.'smdoc_text_textile.howto.tpl'); ?>
</td>
</tr>
<tr>
  <td class="col_top" align="center">
    <span class="label"><?php echo _("Short Summary"); ?>:</span><br />
    <?php echo $obj['createSummary']->display('textile',NULL,5); ?><br />
    <span class="smalldate">[<?php echo _("255 Characters"); ?>]</span>

  <div class="form_submit"><?php echo $t['form']->display_buttons(); ?></div>

  <p>
    <span class="label"><?php echo _("Extended News Entry"); ?>:</span><br />
    <?php echo $obj['createBody']->display('textile', NULL,25); ?>
  </p>

  <div class="form_submit"><?php echo $t['form']->display_buttons(); ?></div>

  <?php $t['form']->display_end(); ?>
  </td>
</tr>
</table>
<?php
}

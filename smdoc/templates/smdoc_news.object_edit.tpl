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
$t['method'] = 'edit';
$t['body_function'] = 'news_edit_body';

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
function news_edit_body(&$foowd, $className, $method, &$user, &$object, &$t)
{
  $obj = $t['form']->objects;
  include_once(TEMPLATE_PATH.'smdoc_text_textile.howto.tpl'); 
?>
<!-- begin create body -->
<div id="textileform">
<script language="JavaScript" type="text/javascript" src="templates/toggleNone.js"></script>
<?php $t['form']->display_start('smdoc_form'); ?>

<table cellspacing="2" cellpadding="0" class="smdoc_table">
  <tr><th class="label"><b><?php echo _("Headline"); ?>:</th>
      <td class="value"><?php echo $obj['editTitle']->display(); ?></td></tr>
  <tr><th class="label"><b><?php echo _("Category"); ?>:</th>
      <td class="value"><?php echo $obj['editCategory']->display(); ?></td></tr>
  <tr>
</table>

<h3><?php echo _("Short Summary"); ?></h3>
<?php echo $obj['editSummary']->display('smdoc_textarea',NULL,5); ?>
<div class="subtext">[<?php echo _("255 Characters"); ?>]</div>

<div class="form_submit"><?php echo $t['form']->display_buttons(); ?></div>

<h3><?php echo _("Extended News Entry"); ?>:</h3>
<?php echo $obj['editArea']->display('smdoc_textarea', NULL,25); ?>

<div class="form_submit"><?php echo $t['form']->display_buttons(); ?></div>
  
<?php $t['form']->display_end(); ?>
</div>
<!-- end create body -->
<div class="float-clear">&nbsp;</div>
<?php
}



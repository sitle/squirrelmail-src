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
  include_once(TEMPLATE_PATH.'smdoc_text_textile.howto.tpl'); 
?>
<!-- begin create body -->
<div id="textileform">
<?php $t['form']->display_start('smdoc_form'); ?>

<table cellspacing="0" cellpadding="0" class="smdoc_table">
  <tr><th class="label"><?php echo _("Title"); ?>:</th>
      <td class="value"><?php echo $obj['createTitle']->display(); ?></td></tr>
  <tr>
</table>

<?php echo $obj['createBody']->display('smdoc_textarea'); ?>

<div class="form_submit"><?php echo $t['form']->display_buttons(); ?></div>
<?php $t['form']->display_end(); ?>

</div>
<!-- end create body -->

<div class="float-clear">&nbsp;</div>
<?php
}

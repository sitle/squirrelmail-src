<?php
/*
 * Copyright (c) 2003-2004 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition/modification to the
 * Framework for Object Orientated Web Development (Foowd).
 */

/**
 * Template for administration of user groups
 *
 * Modified by SquirrelMail Development
 * $Id$
 *
 * @package smdoc
 * @subpackage template
 */
$t['body_function'] = 'user_groups_body';

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
function user_groups_body(&$foowd, $className, $method, &$user, &$object, &$t)
{
  echo '<h1>' . _("User Permissions") . '</h1>' . "\n";
  $obj = $t['form']->objects;

  // First, add some javaScript to toggle on/off other 
  // elements if None is selected.
?>
<script language="JavaScript" type="text/javascript" src="templates/toggleNone.js"></script>
<?php
  // Start form display
  $t['form']->display_start('smdoc_form');
?>
<table cellspacing="0" cellpadding="0" class="smdoc_table">
<tr><td class="label"><b><?php echo _("Group Membership"); ?>:</b></td>
    <td class="value"><?php echo $obj['groups']->display(NULL, 6, ' onChange="toggleNone();"'); ?></td></tr>
</table>

<?php
  echo "\n" . '<div class="form_submit">';
  $t['form']->display_buttons();
  echo '</div>'."\n";

  $t['form']->display_end();
}

// vim: syntax=php

<?php
/*
 * Copyright (c) 2003-2004 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition/modification to the
 * Framework for Object Orientated Web Development (Foowd).
 */

/**
 * Template for user list.
 *
 * Modified by SquirrelMail Development
 * $Id$
 *
 * @package smdoc
 * @subpackage template
 */

$t['title'] = _("Group Index");
$t['body_function'] = 'group_list_body';

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
function group_list_body(&$foowd, $className, $method, &$user, &$object, &$t)
{
  $t['addForm']->display_start('smdoc_form');
?>
    <table class="smdoc_table">
      <tr>
        <td class="label"><?php echo _("New Group Name"); ?></td>
        <td class="value"><?php echo $t['addForm']->objects['newGroup']->display(); ?></td>
      </tr>
    </table>

<?php
  echo "\n" . '<div class="form_submit">';
  $t['addForm']->display_buttons();
  echo '</div>'."\n";  
  $t['addForm']->display_end();

  // Display All groups, with member count, and checkboxes for delete
  $t['deleteForm']->display_start('smdoc_form');
?>
    <table class="smdoc_table">
      <tr><td colspan="5"><div class="separator"><?php echo _("Current Groups"); ?></div></td></tr>
      </tr>
      <tr>
        <th><?php echo _("Group") ?></th>
        <th></th>
        <th><?php echo _("Members") ?></th>
        <th></th>
        <th><?php echo _("Delete") ?></th>
      </tr>
<?php 
      $row = 0;
      foreach ( $t['grouplist'] as $id => $arr )
      {
?>
      <tr class="<?php echo ($row ? 'row_odd' : 'row_even'); ?>">
        <td><?php echo $arr['group_name']; ?></td>
        <td class="subtext">&nbsp;[<?php echo $id; ?>]&nbsp;</td>
        <td align="center"><?php echo $arr['group_count']; ?></td>
        <td class="subtext">&nbsp;
<?php   $methods = array();
        if ( $arr['group_count'] > 0 )
          $methods[] = '<a href="'.getURI().'?class=smdoc_group_user&amp;method=edit&amp;id='.$id.'">Edit</a> ';
        print_arr($methods);
      ?> 
        </td>
        <td>&nbsp;<?php echo empty($arr['group_delete']) ? 
                                   $arr['group_delete'] : 
                                   $arr['group_delete']->display(); ?>&nbsp;</td>
      </tr>
<?php    $row = !$row;
      } // end foreach user in list
?>
    </table>
<?php
  echo "\n" . '<div class="form_submit">';
  $t['deleteForm']->display_buttons();
  echo '</div>'."\n";
  
  $t['deleteForm']->display_end();
} // end user_list_body



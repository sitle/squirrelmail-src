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

$t['title'] = _("Group Membership") .': '.$t['groupname'];
$t['body_function'] = 'group_edit_body';

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
function group_edit_body(&$foowd, $className, $method, &$user, &$object, &$t)
{
  // Display Group membership, and checkboxes for delete
  $t['deleteForm']->display_start('smdoc_form');
?>
    <table class="smdoc_table">
      <tr><td colspan="5"><div class="separator"><?php echo _("Current Members"); ?></div></td></tr>
      </tr>
      <tr>
        <th><?php echo _("User") ?></th>
        <th></th>
        <th><?php echo _("Delete") ?></th>
      </tr>
<?php 
      $row = 0;
      foreach ( $t['memberlist'] as $id => $arr )
      {
        $uri_arr['objectid'] = $arr['objectid'];
        $uri_arr['classid']  = USER_CLASS_ID;
        $url = getURI($uri_arr);
?>
      <tr class="<?php echo ($row ? 'row_odd' : 'row_even'); ?>">
        <td class="value"><a href="<?php echo $url; ?>"><?php echo $arr['title']; ?></a></td>
        <td class="subtext">&nbsp;[<?php echo $arr['objectid']; ?>]&nbsp;</td>
        <td>&nbsp;<?php echo empty($arr['member_delete']) ? 
                                   $arr['member_delete'] : 
                                   $arr['member_delete']->display(); ?>&nbsp;</td>
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

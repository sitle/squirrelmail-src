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

$t['title'] = _("User Index");
$t['method'] = 'list';
$t['body_function'] = 'user_list_body';

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
function user_list_body(&$foowd, $className, $method, &$user, &$object, &$t)
{
  $smver_string = smdoc_user::smver_to_string(TRUE);
  $smtp_servers = smdoc_user::smtp_to_string(TRUE);
  $imap_servers = smdoc_user::imap_to_string(TRUE);
  $dummy = NULL;
?>

<table class="smdoc_table" width="100%">
 <tr>
<!-- Left column containing user statistics -->
  <td class="col_left">
    <table class="smdoc_table">
      <tr>
        <td class="heading"><?php echo _("Registered Users"); ?>:</td>
        <td class="value"><?php echo $t['user_count']; ?></td>
      </tr>
      <tr>
        <td colspan="2">
          <div class="separator"><?php echo _("SquirrelMail Versions"); ?></div>
        </td>
      </tr>
<?php  foreach ( $t['user_smver'] as $key => $number )
       {
         if ( $number == 0 )
          continue;
?>
      <tr>
        <td class="heading"><?php echo $smver_string[$key]; ?>:</td>
        <td class="value"><?php printf("%.2f", ( $number / $t['user_count'] ) * 100); ?>%</td>
      </tr>
<?php  } // end foreach SM Version 
?>
      <tr>
        <td colspan="2">
          <div class="separator"><a href="object=smtp"><?php echo _("SMTP Servers"); ?></a></div>
        </td>
      </tr>
<?php  foreach ( $t['user_smtp'] as $key => $number )
       {
         if ( $number == 0 )
          continue;
?>
      <tr>
        <td class="heading"><?php echo $smtp_servers[$key]; ?>:</td>
        <td class="value"><?php printf("%.2f", ( $number / $t['user_count'] ) * 100); ?>%</td>
      </tr>
<?php  } // end foreach SMTP server 
?>
      <tr>
        <td colspan="2">
          <div class="separator"><a href="object=imap"><?php echo _("IMAP Servers"); ?></a></div>
        </td>
      </tr>
<?php  foreach ( $t['user_imap'] as $key => $number )
       {
         if ( $number == 0 )
          continue;
?>
      <tr>
        <td class="heading"><?php echo $imap_servers[$key]; ?>:</td>
        <td class="value"><?php printf("%.2f", ( $number / $t['user_count'] ) * 100); ?>%</td>
      </tr>
<?php  } // end foreach IMAP server ?>
    </table>
  </td>

<!-- Right column with list of users -->
  <td class="col_right">
    <table class="smdoc_table">
      <tr>
        <th><?php echo _("Username") ?></th>
        <th></th>
        <th></th>
        <th><?php echo _("IRC") ?></th>
      </tr>
<?php 
      $row = 0;
      foreach ( $t['user_list'] as $arr )
      {
        $uri_arr['objectid'] = $arr['objectid'];
        $uri_arr['classid']  = USER_CLASS_ID;
        $url = getURI($uri_arr);
        if ( empty($arr['IRC']) )
          $arr['IRC'] = '';
?>
      <tr class="<?php echo ($row ? 'row_odd' : 'row_even'); ?>">
        <td><a href="<?php echo $url; ?>"><?php echo $arr['title']; ?></a></td>
        <td class="subtext">[<?php echo $arr['objectid']; ?>]&nbsp;</td>
        <td class="menu_subtext">&nbsp;
<?php   $methods = array();
        if ( $foowd->hasPermission(USER_CLASS_NAME,'groups','OBJECT',$dummy) )
          $methods[] = '<a href="'.$url.'&method=groups">Groups</a> ';
        if ( $foowd->user->inGroup('Author',$arr['objectid']) )
          $methods[] = '<a href="'.$url.'&method=update">Update</a> ';
        if ( $foowd->hasPermission(USER_CLASS_NAME,'delete','OBJECT',$dummy) )
          $methods[] = '<a href="'.$url.'&method=delete">Delete</a> ';

        if ( !empty($methods) )
        {
          echo '( ';
          foreach ( $methods as $i => $method )
          {
            if ( $i != 0 )
              echo ' | ';
            echo $method;
          }
          echo ' )&nbsp;';
        }
      ?> 
        </td>
        <td align="left"><?php echo $arr['IRC']; ?></td>
      </tr>
<?php    $row = !$row;
       } // end foreach user in list
?>
    </table>
  </td>
 </tr>
</table>
<?php
} // end user_list_body


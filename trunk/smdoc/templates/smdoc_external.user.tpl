<?php
/*
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition/modification to the
 * Framework for Object Orientated Web Development (Foowd).
 */

/**
 * Template for external user list.
 *
 * Modified by SquirrelMail Development
 * $Id$
 *
 * @package smdoc
 * @subpackage template
 */
?>
<table class="smdoc_table" width="100%">
<tr>
<td class="userstats">
 <table class="smdoc_table">
  <tr>
   <td class="heading"><?php echo _("Registered Users"); ?>:</td>
   <td class="value"><?php echo $t['user_count']; ?></td>
  </tr>
  <tr><td colspan="2"><div class="separator"><?php echo _("SquirrelMail Versions"); ?></div></td></tr>
<?php  $smver_string = smdoc_user::smver_to_string(TRUE);
       foreach ( $t['user_smver'] as $key => $number )
       {
         if ( $number == 0 )
          continue;
?>
  <tr>
   <td class="heading"><?php echo $smver_string[$key]; ?>:</td>
   <td class="value"><?php printf("%.2f", ( $number / $t['user_count'] ) * 100); ?>%</td>
  </tr>
<?php  } ?>

<tr><td colspan="2">
    <div class="separator">
      <a href="object=smtp"><?php echo _("SMTP Servers"); ?></a>
    </div></td></tr>
<?php  $smtp_servers = smdoc_user::smtp_to_string(TRUE);
       foreach ( $t['user_smtp'] as $key => $number )
       {
         if ( $number == 0 )
          continue;
?>
  <tr>
   <td class="heading"><?php echo $smtp_servers[$key]; ?>:</td>
   <td class="value"><?php printf("%.2f", ( $number / $t['user_count'] ) * 100); ?>%</td>
  </tr>
<?php  } ?>

<tr><td colspan="2">
    <div class="separator">
      <a href="object=imap"><?php echo _("IMAP Servers"); ?></a>
    </div></td></tr>
<?php  $imap_servers = smdoc_user::imap_to_string(TRUE);
       foreach ( $t['user_imap'] as $key => $number )
       {
         if ( $number == 0 )
          continue;
?>
  <tr>
   <td class="heading"><?php echo $imap_servers[$key]; ?>:</td>
   <td class="value"><?php printf("%.2f", ( $number / $t['user_count'] ) * 100); ?>%</td>
  </tr>
<?php  } ?>
 </table>
</td>
<td class="userlist">
  <table class="smdoc_table">
  <tr>
    <th><?php echo _("Username") ?></th>
    <th></th>
    <th></th>
    <th><?php echo _("IRC") ?></th>
  </tr>
<?php $row = 0;
      $isAdmin = $foowd->user->inGroup('Gods');
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
    <td class="subtext">&nbsp;
<?php $methods = '';
      if ( $isAdmin )
        $methods .= '<a href="'.$url.'&method=groups">Groups</a> ';
      if ( $foowd->user->inGroup('Author',$arr['objectid']) )
        $methods .= '<a href="'.$url.'&method=update">Update</a> ';
      if ( !empty($methods) )
        echo '( '.$methods.' )';
      ?>&nbsp;</td>
    <td align="left"><?php echo $arr['IRC']; ?></td>
  </tr>
<?php    $row = !$row;
       }
?>
  </table>
</td>
</tr>
</table>
</div>

<?php
/*
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition/modification to the 
 * Framework for Object Orientated Web Development (Foowd).
 */

/** 
 * Template for list of recent changes
 *
 * Modified by SquirrelMail Development
 * $Id$
 * 
 * @package smdoc
 * @subpackage template
 */
?>
<table width="100%" cellspacing="2">
  <tr>
    <th><?php echo _("Title") ?></th>
    <th></th>
    <th></th>
    <th><?php echo _("Updated") ?></th>
    <th><?php echo _("Author") ?></th>
    <th align="left"><?php echo _("Object Type") ?></th>
  </tr>
<?php  $row = 0;
       foreach ( $t['changeList'] as $arr )
       {
?>
  <tr class="<?php echo ($row ? 'row_odd' : 'row_even'); ?>">
    <td><a href="<?php echo $arr['url']; ?>"><?php echo $arr['title']; ?></a></td>
    <td class="small" align="left"> v. <?php echo $arr['ver']; ?> </td>
    <td class="small" align="center"><?php echo $arr['langid']; ?></td>
    <td class="smalldate" align="center"><?php echo $arr['updated']; ?></td>
    <td class="small" align="center"><?php echo $arr['updated_by']; ?></td>
    <td class="small" align="left"><?php echo $arr['desc']; ?></td>
  </tr>
<?php    $row = !$row;
       }
?>
</table>

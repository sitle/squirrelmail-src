<?php
/*
 * Copyright (c) 2003-2004 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition/modification to the
 * Framework for Object Orientated Web Development (Foowd).
 */

/**
 * Template for external site index
 *
 * Modified by SquirrelMail Development
 * $Id$
 *
 * @package smdoc
 * @subpackage template
 */

$temp_index = array('A','B','C','D','E','F','G','H','I','J','K','L','M',
                    'N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
$i = 0;
$index = array();

$object_arr =& $t['objectList'];
$max = count($object_arr);

$prev = 0;
foreach ( $temp_index as $char ) 
{
  $ord_char = ord($char);
  if ( $i < $max )
    $ord_title = ord(ucfirst($object_arr[$i]['title']));

  if ( $i >= $max || $ord_title > $ord_char )  
  {
    // character in title is higher in alphabet than character in index
    $index[] = $char;
    continue;
  } 
  elseif ( $ord_title == $ord_char ) 
  {
    // character in index is equal to first character of current object title
    $index[] = '<a href="#index_' . $char . '">'. $char . '</a>';
    $object_arr[$i]['index'] = '<a name="#index_' . $char . '"></a>';
    $prev = $ord_title;

    // skip over duplicates
    $i++;
    while ( $i < $max )
    {
      $ord_title = ord(ucfirst($object_arr[$i]['title']));
      if ( $ord_title != $ord_char )
        break;

      $i++;
    }
  }
}
?>
<div class="index"><?php echo implode(' ', $index); ?></div>

<table width="100%" cellspacing="2">
  <tr>
    <th><?php echo _("Title") ?></th>
    <th></th>
    <th></th>
    <th><?php echo _("Updated") ?></th>
    <th align="left"><?php echo _("Object Type") ?></th>
  </tr>
<?php  $row = 0;
       foreach ( $object_arr as $arr )
       {
         if ( isset($arr['index']) )
         { ?>
  <tr><td colspan="4"><?php echo $arr['index']; ?></td></tr>
<?php    } ?>
  <tr class="<?php echo ($row ? 'row_odd' : 'row_even'); ?>">
    <td><a href="<?php echo $arr['url']; ?>"><?php echo $arr['title']; ?></a></td>
    <td class="small_center"> <?php echo $arr['langid']; ?></td>
    <td class="menu_subtext">&nbsp;
<?php   // Add links for methods user has permission to invoke (convenience)

        $methods = array();

        if ( $foowd->hasPermission(getClassName($arr['classid']),'admin','METHOD') )
          $methods[] = '<a href="'.$arr['url'].'&amp;method=admin">Admin</a> ';
        if ( $foowd->hasPermission(getClassName($arr['classid']),'edit','METHOD') )
          $methods[] = '<a href="'.$arr['url'].'&amp;method=edit">Edit</a> '; 
        if ( $foowd->hasPermission(getClassName($arr['classid']),'delete','METHOD') )
          $methods[] = '<a href="'.$arr['url'].'&amp;method=delete">Delete</a> ';         

        print_arr($methods);
      ?>
    </td>
    <td class="smalldate" align="center">    <?php echo $arr['updated']; ?></td>
    <td class="small">        <?php echo $arr['desc']; ?></td>
  </tr>
<?php    $row = !$row;
       }
?>
</table>

// vim: syntax=php

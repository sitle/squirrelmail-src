<?php
/*
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition/modification to the
 * Framework for Object Orientated Web Development (Foowd).
 */

/**
 * Template for creating new objects
 *
 * Modified by SquirrelMail Development
 * $Id$
 *
 * @package smdoc
 * @subpackage template
 */
$uri = getURI();

      if ( !empty($t['admin_list']) )
      { ?>
<p><span class="heading"><?php echo _("Site Administration"); ?>:</span>&nbsp;
<?php   print_arr($t['admin_list'], FALSE);
      }

// List create new resource links
if ( !empty($t['create_list']) )
{
  $create_list =& $t['create_list'];
?>
<h3><?php echo _("Create New Resource"); ?></h3>
<table>
<?php $glue = ( strpos($uri, '?') === FALSE ) ? '?' : '&amp;';

      foreach ( $create_list as $name => $desc )
      { 
        $url = $uri.$glue.'class='.$name;
?>
  <tr>
    <td class="heading"><a href="<?php echo $url; ?>"><?php echo $name; ?></a></td>
    <td class="value"><?php echo $desc; ?></td>
  </tr>
<?php } ?>
  </table>
<?php
}


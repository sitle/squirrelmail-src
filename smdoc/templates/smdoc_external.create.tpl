<?php
/*
 * Copyright (c) 2003-2004 The SquirrelMail Project Team
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

  $class_list =& $t['classlist'];

  $uri_arr['method'] = 'create';
  $uri = getURI($uri_arr);
?>

<table class="smdoc_table">
  <tr>
    <th><?php echo _("Class"); ?></td>
    <th><?php echo _("Description"); ?></td>
  </tr>
<?php foreach ( $class_list as $name => $desc )
      { 
        $url = $uri.'&amp;class='.$name;
?>
  <tr>
    <td><a href="<?php echo $url; ?>"><?php echo $name; ?></a></td>
    <td><?php echo $desc; ?></td>
  </tr>
<?php } ?>
  </table>

<?php
// vim: syntax=php

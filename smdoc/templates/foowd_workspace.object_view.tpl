<?php
/*
 * Copyright (c) 2003-2004 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition/modification to the 
 * Framework for Object Orientated Web Development (Foowd).
 */

/** 
 * Template for workspace view
 *
 * Modified by SquirrelMail Development
 * $Id$
 * 
 * @package smdoc
 * @subpackage template
 */
$t['body_function'] = 'workspace_view_body';

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
function workspace_view_body(&$foowd, $className, $method, &$user, &$object, &$t)
{
?>
<p>
<table cellspacing="0" cellpadding="0" class="smdoc_table">
<tr><td class="heading"><b><?php echo _("Created"); ?>:</b></td>
    <td class="value"><?php echo $t['created']; ?></td></tr>
<tr><td class="heading"><b><?php echo _("Author"); ?>:</b></td>
    <td class="value"><?php echo htmlentities($t['author']); ?></td></tr>
<tr><td class="heading"><b><?php echo _("Access"); ?>:</b></td>
    <td class="value"><?php echo smdoc_group::getDisplayName($t['access']); ?></td></tr>
</table>
</p>
<p><?php echo $t['description']; ?></p>

<h3 align="center"><?php echo _("Objects Within Workspace"); ?></h3>
<?php if (isset($t['objectList'])) 
      { 
        $row = 0; ?>
<table cellpadding="2" width="100%">
<tr><th class="separator"><?php echo _("Title"); ?></th>
    <th class="separator"><?php echo _("Updated"); ?></th>
    <th class="separator"><?php echo _("Object Type"); ?></th>
</tr>
<?php   foreach( $t['objectList'] as $key => $obj )
        { 
          $uri_arr['classid'] = $obj['classid'];
          $uri_arr['objectid'] = $obj['objectid'];
          ?>
<tr class="<?php echo ($row ? 'row_odd' : 'row_even'); ?>">
    <td><a href="<?php echo getURI($uri_arr); ?>">
        <?php echo htmlentities($obj['title']); ?></a></td>
    <td class="smalldate" align="center"><?php echo $obj['updated']; ?></td>
    <td class="small"><?php echo getClassDescription($obj['classid']); ?></td>
</tr>
<?php     $row = !$row; 
        } ?>
</table>
<?php }
      else
        echo '<p align="center">'._("There are no objects in this workspace.").'</p>';
}



<?php
/*
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition/modification to the 
 * Framework for Object Orientated Web Development (Foowd).
 */

/** 
 * Template for object history
 *
 * Modified by SquirrelMail Development
 * $Id$
 * 
 * @package smdoc
 * @subpackage template
 */

$t['body_function'] = 'error_bad_workspace_body';

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
function error_bad_workspace_body(&$foowd, $className, $method, &$user, &$object, &$t)
{
  $wlist =& $t['workspaceList'];
?>
<p>
<table cellspacing="0" cellpadding="0" class="smdoc_table">
<tr><td class="heading"><b><?php echo _("CurrentWorkspace"); ?>:</b></td>
    <td class="value"><?php echo $wlist[$user->workspaceid]; ?></td></tr>
</table>
</p>

<p align="center">
<?php echo _("The page you requested does not exist in your current workspace.") . '<br />';
      echo _("Please change to one of the workspaces listed below."); ?>
</p>
<p>
<table border="0" cellspacing="5" align="center">
<tr >
    <th class="separator"><?php echo _("Title"); ?></th>
    <th class="separator"><?php echo _("Workspace"); ?></th>
    <th class="separator"><?php echo _("Last Updated"); ?></th>
    <th>&nbsp;</th>
<?php foreach ($t['objectList'] as $key => $obj) 
      {
        $uri_arr['method'] = 'enter';
        $uri_arr['objectid'] = $obj['workspaceid'];
?>
<tr>
    <td><?php echo htmlentities($obj['title']); ?></td>
    <td align="center"><a href="<?php echo getURI($uri_arr); ?>"><?php echo $wlist[$obj['workspaceid']]; ?></a></td>
    <td class="smalldate"><?php echo $obj['updated']; ?></td>
</tr>
<?php }
?>
</table>
</p>
<?php
}
?>

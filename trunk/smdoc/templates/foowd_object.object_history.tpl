<?php
/*
 * Copyright (c) 2003-2004 The SquirrelMail Project Team
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

$t['body_function'] = 'object_history_body';

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
function object_history_body(&$foowd, $className, $method, &$user, &$object, &$t)
{?>
<h1><?php echo _("History"); ?></h1>

<p>
<table cellspacing="0" cellpadding="0" class="smdoc_table">
<tr><td class="heading"><b><?php echo _("Title"); ?>:</b></td>
    <td class="value"><?php echo $t['detailsTitle']; ?></td></tr>
<tr><td class="heading"><b><?php echo _("Created"); ?>:</b></td>
    <td class="value"><?php echo $t['detailsCreated']; ?></td></tr>
<tr><td class="heading"><b><?php echo _("Author"); ?>:</b></td>
    <td class="value"><?php echo $t['detailsAuthor']; ?></td></tr>
<tr><td class="heading"><b><?php echo _("Object Type"); ?>:</b></td>
    <td class="value"><?php echo $t['detailsType']; ?></td></tr>
<?php if (isset($t['detailsWorkspace'])) { ?>
<tr><td class="heading"><b><?php echo _("Workspace"); ?>:</b></td>
    <td class="value"><?php echo $t['detailsWorkspace']; ?></td></tr>
<?php } ?>
</table>
</p>

<p>
<table border="0" cellspacing="5" align="center">
<tr >
    <th class="separator"><?php echo _("Last Updated"); ?></th>
    <th class="separator"><?php echo _("Author"); ?></th>
    <th class="separator"><?php echo _("Version"); ?></th>
    <th>&nbsp;</th>
<?php foreach ($t['versions'] as $version) {
    $link = getURI() . '?objectid=' . $version['objectid']
                     . '&version='  . $version['version'];
?>
</tr>
<tr>
    <td class="smalldate"><?php echo $version['updated']; ?></td>
    <td class="small" align="center"><?php echo $version['author']; ?></td>
    <td class="small" align="center"><a href="<?php echo $link; ?>"><?php echo $version['version']; ?></a></td>
<?php   if (isset($version['revert']) && $foowd->hasPermission($className,'revert','object',$object) ) { ?>
    <td class="small"><a href="<?php echo $link,'&method=revert'; ?>">Revert</a></td>
<?php   }
        if ( isset($version['diff']) && $foowd->hasPermission($className,'diff','object',$object)) { ?>
    <td class="small"><a href="<?php echo $link.'&method=diff'; ?>">Diff</a></td>
<?php   }
      } ?>
    </tr>
</table>
</p>
<?php
}
?>

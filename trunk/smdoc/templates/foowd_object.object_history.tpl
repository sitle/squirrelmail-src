<?php

$t['body_function'] = 'object_history_body';
include(TEMPLATE_PATH.'index.tpl');

function object_history_body(&$foowd, $className, $method, $user, $object, &$t)
{?>
<h1>History of <?php echo $t['title']; ?></h1>

<p>
<table border="0" cellspacing="0" cellpadding="0" align="center">
<tr><td></td><td rowspan="6" width="10"><img src="empty.png" border="0" alt="" /></td><td></td></tr>
<tr><td><b><?php echo _("Title"); ?>:</b>      </td><td><?php echo $t['detailsTitle']; ?></td></tr>
<tr><td><b><?php echo _("Created"); ?>:</b>    </td><td><?php echo $t['detailsCreated']; ?></td></tr>
<tr><td><b><?php echo _("Author"); ?>:</b>     </td><td><?php echo $t['detailsAuthor']; ?></td></tr>
<tr><td><b><?php echo _("Object Type"); ?>:</b></td><td><?php echo $t['detailsType']; ?></td></tr>
<?php if (isset($t['detailsWorkspace'])) { ?>
<tr><td><b><?php echo _("Workspace"); ?>:</b>  </td><td><?php echo $t['detailsWorkspace']; ?></td></tr>
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

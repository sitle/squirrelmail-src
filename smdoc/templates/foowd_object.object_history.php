<?php

$t['body_function'] = 'object_history_body';
include($foowd->template.'/index.php');

function object_history_body(&$foowd, $className, $method, $user, $object, &$t)
{?>
<h1>History of <?php echo $t['title']; ?></h1>

<p>
<table border="0" cellspacing="0" cellpadding="0" align="center">
<tr><td></td><td rowspan="6" width="10"><img src="empty.png" border="0" alt="" /></td><td></td></tr>
<tr><td><b>Title:</b>      </td><td><?php echo $t['detailsTitle']; ?></td></tr>
<tr><td><b>Created:</b>    </td><td><?php echo $t['detailsCreated']; ?></td></tr>
<tr><td><b>Author:</b>     </td><td><?php echo $t['detailsAuthor']; ?></td></tr>
<tr><td><b>Object Type:</b></td><td><?php echo $t['detailsType']; ?></td></tr>
<?php if (isset($t['detailsWorkspace'])) { ?>
<tr><td><b>Workspace:</b>  </td><td><?php echo $t['detailsWorkspace']; ?></td></tr>
<?php } ?>
</table>
</p>

<p>
<table border="0" cellspacing="5" align="center">
<tr >
    <th class="separator">Last Updated</th>
    <th class="separator">Author</th>
    <th class="separator" align="center">Version</th>
    <th>&nbsp;</th>
<?php foreach ($t['versions'] as $version) { ?>
</tr>
<tr>
    <td class="smalldate"><?php echo $version['updated']; ?></td>
    <td class="small" align="center"><?php echo $version['author']; ?></td>
    <td class="small" align="center"><a href="<?php echo $version['link']; ?>"><?php echo $version['version']; ?></a></td>
<?php   if (isset($version['revert'])) { ?>
    <td class="small"><a href="<?php echo $version['revert']; ?>">Revert</a></td>
<?php   }
      } ?>
    </tr>
</table>
</p>
<?php
}
?>

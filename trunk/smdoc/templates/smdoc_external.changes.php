<table width="100%" cellspacing="2">
  <tr>
    <th><?php echo _("Title") ?></th>
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
    <td class="small" align="center">        <?php echo $arr['langid']; ?></td>
    <td class="smalldate" align="center">    <?php echo $arr['updated']; ?></td>
    <td class="small" align="center">        <?php echo $arr['updated_by']; ?></td>
    <td class="small" align="left">          <?php echo $arr['desc']; ?></td>
  </tr>
<?php    $row = !$row;
       } 
?>
</table>

<?php
$t['body_function'] = 'user_admin_body';
include(TEMPLATE_PATH.'index.tpl');

function user_admin_body(&$foowd, $className, $method, $user, &$object, &$t)
{
  echo '<h1>' . _("User Permissions") . '</h1>' . "\n";

  $t['form']->display_start('smdoc_form');
  $obj = $t['form']->objects;
?>

<table cellspacing="0" cellpadding="0" class="smdoc_table">
<tr><td class="label"><b><?php echo _("Group Membership"); ?>:</b></td>
    <td class="value"><?php echo $obj['groups']->display(NULL, 6); ?></td></tr>
</table>

<?php
  echo "\n" . '<div class="form_submit">';
  $t['form']->display_buttons();
  echo '</div>'."\n";

  $t['form']->display_end();
}

?>

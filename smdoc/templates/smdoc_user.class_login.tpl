<?php
$t['title'] = _("User Login");
$t['method'] = 'login';
$t['body_function'] = 'user_login_body';
include(TEMPLATE_PATH.'index.tpl');

function user_login_body(&$foowd, $className, $method, $user, &$object, &$t)
{
  $t['form']->display_start('smdoc_form');
  $obj = $t['form']->objects;
?>

<table cellspacing="0" cellpadding="0" class="smdoc_table">
<tr><td class="label"><b><?php echo _("Login"); ?>:</b></td>
    <td class="value"><?php echo $obj['loginUsername']->display(); ?></td></tr>
<tr><td class="label"><b><?php echo _("Password"); ?>:</b></td>
    <td class="value"><?php echo $obj['loginPassword']->display(); ?></td></tr>
</table>

<?php
  echo "\n" . '<div class="form_submit">';
  $t['form']->display_buttons();
  echo '</div>'."\n";

  $url = getURI(array('class' => $className));
  echo '<p class="small"><a href="'.$url.'&method=create">' 
       . _("Create new account.")
       . '</a><br />' 
       . '<a href="'.$url.'&method=lostpassword">' 
       . _("Forgot your password?")
       . '</a></p>';

  $t['form']->display_end();
}

?>

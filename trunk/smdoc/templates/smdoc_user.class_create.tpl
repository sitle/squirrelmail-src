<?php
$t['title'] = _("Create New User");
$t['method'] = 'create';
$t['body_function'] = 'user_create_body';
include(TEMPLATE_PATH.'index.tpl');

function user_create_body(&$foowd, $className, $method, $user, &$object, &$t)
{
  $t['form']->display_start('smdoc_form');
  $obj = $t['form']->objects;

?>

<table cellspacing="0" cellpadding="0" class="smdoc_table">
<tr><td class="label"><b><?php echo _("UserName"); ?>:</b></td>
    <td class="value"><?php echo $obj['createUsername']->display(); ?></td></tr>
<tr><td colspan="2">&nbsp;</td></tr>

<tr><td class="label"><b><?php echo _("Password"); ?>:</b></td>
    <td class="value"><?php echo $obj['createPassword']->display(); ?></td></tr>
<tr><td class="label"><b><?php echo _("Verify"); ?>:</b></td>
    <td class="value"><?php echo $obj['verifyPassword']->display(); ?></td></tr>
<tr><td colspan="2">&nbsp;</td></tr>

<tr><td class="label"><b><?php echo _("Email"); ?>:</b></td>
    <td class="value"><?php echo $obj['createEmail']->display(); ?>
                      <span class="subtext">(<a href="#email">privacy</a>)</span></td></tr>

</table>

<?php
  echo "\n" . '<div class="form_submit">';
  $t['form']->display_buttons();
  echo '</div>'."\n";

  $url = getURI(array('class' => $className));
  echo '<p class="small"><a href="'.$url.'&method=login">' 
       . _("Login with existing user.")
       . '</a></p>';

  $t['form']->display_end();
?>
<p class="subtext_center"><a id="email" name="email"></a>
Your password is not required, it is used for password recovery.
It will not be shared without your consent.
</p>

<?php
}
?>

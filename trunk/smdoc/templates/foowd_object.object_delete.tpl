<?php

$t['body_function'] = 'object_delete_body';
include(TEMPLATE_PATH.'index.tpl');

function object_delete_body(&$foowd, $className, $method, $user, $object, &$t)
{
  $t['form']->display_start('smdoc_form');

  echo '<p>'. _("Are you sure you want to delete <b>all versions</b> of this object?") . '</p>';

  echo "\n" . '<div class="form_submit">';
  $t['form']->display_buttons();
  echo '</div>'."\n";

  $t['form']->display_end();
}

?>

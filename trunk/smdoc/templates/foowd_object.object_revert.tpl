<?php

$t['body_function'] = 'object_revert_body';
include(TEMPLATE_PATH.'index.tpl');

function object_revert_body(&$foowd, $className, $method, $user, $object, &$t)
{
  $t['form']->display_start('smdoc_form');

  echo '<p>'. _("Revert this object back to version ") . $t['version'] . '?</p>';

  echo "\n" . '<div class="form_submit">';
  $t['form']->display_buttons();
  echo '</div>'."\n";

  echo '<div class="preview">', $object->view(), '</div>'."\n";

  $t['form']->display_end();

}

?>

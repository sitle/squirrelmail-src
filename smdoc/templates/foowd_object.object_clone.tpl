<?php

$t['body_function'] = 'object_clone_body';
include(TEMPLATE_PATH.'index.tpl');

function object_clone_body(&$foowd, $className, $method, $user, $object, &$t)
{
  echo '<h1>' . _("Clone object: ") . $t['title'], '</h1>';
  
  $t['form']->display_start('smdoc_form');

  echo '<p>'. _("New Title for Clone") . ': ';
  $t['form']->objects['cloneTitle']->display();

  echo "</p>\n<p>". _("Target Translation/Workspace") . ': ';
  $t['form']->objects['workspaceDropdown']->display();

  echo "\n" . '<div class="form_submit">';
  $t['form']->display_buttons();
  echo '</div>'."\n";
  $t['form']->display_end();
}
?>

<?php

$t['body_function'] = 'text_plain_edit_body';
include(TEMPLATE_PATH.'index.tpl');

function text_plain_edit_body(&$foowd, $className, $method, $user, &$object, &$t)
{
  if ( isset($t['preview']) )
    echo '<div class="preview">', $t['preview'], '</div>'."\n";

  $objects =& $t['form']->objects;
  
  $t['form']->display_start('smdoc_form');
  echo '<div class="form_submit">';
  $t['form']->display_buttons();
  echo '</div>'."\n";

  if ( isset($objects['noNewVersion']) )
    $objects['noNewVersion']->display();
  $objects['editCollision']->display();
  $objects['editArea']->display(NULL, 80, 20);

  echo '<div class="form_submit">';
  $t['form']->display_buttons();
  echo '</div>'."\n";
  $t['form']->display_end();
}

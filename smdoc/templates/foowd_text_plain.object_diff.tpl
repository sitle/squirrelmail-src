<?php

$t['body_function'] = 'text_plain_view_body';
include(TEMPLATE_PATH.'index.tpl');

function text_plain_view_body(&$foowd, $className, $method, $user, &$object, &$t)
{
  if ( !isset($t['failure']) ) 
  {
    echo '<p>', sprintf(_("Differences between versions %d and %d of \"%s\":"), $t['version1'], $t['version2'], $t['title']), '</p>';
    echo '<table>';
    $i = 1;
    foreach ($t['diff'] as $diff) 
    {
      if ( count($diff) == 0 )
        continue;

      if ( isset($diff['add']) )
      {
        $sym = '+';
        $type = 'add';
      } 
      elseif ( isset($diff['minus']) )
      {
        $sym = '-';
        $type = 'minus';
      }
      else
      {
        $sym = ' ';
        $type = 'same';
      }
      $diff[$type] = rtrim($diff[$type]);
      printf("<tr class=\"diff_%s\"><td>%04d %s %s</td></tr>\n", $type, $i++, $sym, $diff[$type]);
    }
    echo '</table>';
  } else {
    echo $object->view();
  }
}

?>

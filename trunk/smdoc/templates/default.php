<?php

function default_body($foowd, $className, $method, $user, $object, $t)
{
    echo '<h3>Default Object Display</h3>';
    if ( isset($object) )
    {
      echo '<pre>Class: ', $className, "\n", 'Method: ',  $method, '</pre>';
      echo '<div class="debug_output_heading">', _("Contents of Current Object"), '</div>';
      show($object);
    }
}

$t['body_function'] = 'default_body';
include($foowd->template.'/index.php');
?>

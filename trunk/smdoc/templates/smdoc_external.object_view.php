<?php

if ( isset($t['body_template']) )
{
    ob_start();
    include($foowd->template.'/'.$t['body_template']);
    $t['body'] = ob_get_contents();
    ob_end_clean();
}

include($foowd->template.'/index.php');
?>

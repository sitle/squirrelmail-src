<?php
ob_start();
	if ( isset($t['form']) ) {
        $table = new input_table();
        $table->grabObjects($t['form']);
        $string = '<span class="xsmall">' 
                  . _("Used for password recovery. It will not be disclosed without your permission.")
                  . '</span>';
        $table->addObject($string);

        $table->insertSpace(0);
        $table->insertSpace(2);
        $table->insertSpace(5);
        $table->addSpace();
        ?><center><?php
        $t['form']->display_start();
        $table->display();
        $t['form']->display_end();
        
        $url = getURI(array('class' => $className));
        echo '<p class="small"><a href="'.$url.'&method=login">' 
             . _("Login with existing user.")
             . '</a></p>';

        ?></center><br /><?php
    }
    $result = ob_get_contents();
ob_end_clean();

$t['title'] = _("Create New User");
$t['body'] =& $result;
include($foowd->template.'/index.php');

?>

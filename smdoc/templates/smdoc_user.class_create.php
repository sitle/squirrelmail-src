<?php
$t['title'] = _("Create New User");
$t['body_function'] = 'user_create_body';
include($foowd->template.'/index.php');

function user_create_body($foowd, $className, $method, $user, $object, $t)
{
	if ( isset($t['form']) ) {
        $table = new input_table();
        $table->grabObjects($t['form']);
        $string = sprintf(_("<a href=\"%s\">Private attribute</a> used for password recovery."),
                          getURI(array('object' => 'privacy')));
        $string = '<span class="subtext">' . $string . '</span>';
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
}
?>

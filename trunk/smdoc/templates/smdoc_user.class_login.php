<?php
$t['title'] = _("User Login");
$t['body_function'] = 'user_login_body';
include($foowd->template.'/index.php');

function user_login_body($foowd, $className, $method, $user, $object, $t)
{
  if ( isset($t['form']) ) 
  {
    $table = new input_table();
    $table->grabObjects($t['form']);
    $table->insertSpace(0);
    $table->insertSpace(3);
    $table->setOption(4, 'onecell', true); // set colspan on cookie checkbox
    $table->addSpace();

    ?><center><?php
    $t['form']->display_start();
    $table->display();
    $t['form']->display_end();

    $url = getURI(array('class' => $className));
    echo '<p class="small"><a href="'.$url.'&method=create">' 
         . _("Create new account.")
         . '</a><br />' 
         . '<a href="'.$url.'&method=lostpassword">' 
         . _("Forgot your password?")
         . '</a></p>';
    ?></center><?php
  }
}

?>

<?php
$t['body_function'] = 'user_update_body';
include($foowd->template.'/index.php');

function user_update_body(&$foowd, $className, $method, $user, $object, &$t)
{
  if ( isset($t['form']) ) 
  {
    $table = new input_table();
    $table->grabObjects($t['form']);

    // add public header
    $table->insertObject(0, _("Public Contact Information"), array('class' => 'separator', 'onecell' => true));
    $table->insertSpace(1);
    $table->insertObject(3, _("Nick used on irc.freenode.net #squirrelmail channel"), array('value_class' => 'subtext'));
    $table->insertSpace(4);

    // add private header
    $table->insertObject(10, _("Private Attributes"), array('class' => 'separator', 'onecell' => true));
    $string = sprintf(_("<a href=\"%s\">Private attributes</a> are not shared with third parties."),
                      getURI(array('object' => 'privacy')));
    $table->insertObject(11, $string, array('class' => 'subtext_center', 'onecell' => true));
    $table->insertSpace(12);
    $table->insertSpace(14);
    $table->insertSpace(17);
    $table->addSpace();

    ?><center><?php
    $t['form']->display_start();
    $table->display();
    $t['form']->display_end();
    ?></center><?php    
  }
}

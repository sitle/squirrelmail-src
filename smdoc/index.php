<?php

//define('PATH', substr($HTTP_SERVER_VARS["PATH_TRANSLATED"], 0 ,strrpos($HTTP_SERVER_VARS['PATH_TRANSLATED'], '/') + 1));
define('PATH', 'C:/Documents and Settings/Paul James/My Documents/Docs/wtf/');

include(PATH.'wtf.config.php');

$conn = databaseOpen(DBHOST, DBUSER, DBPASS, DBNAME); // open database

$wtf = new wtf();

$wtf->loadThing();

echo '<p>';

$wtf->doOp();

echo '</p>';

wikiMenu($wtf->thing);

$wtf->display();

databaseClose($conn);
?>
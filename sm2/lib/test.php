<?php

include('classes.php');

echo "auto include <BR>";

sm_include('Messages');

$msg =& new messages('imap',array('username' => 'username',
                                 'host'=> 'localhost',
                                 'password'=>'password',
                                 'port' => 143,
                                 'type'=>'imap'));
echo '<pre>';                                 
if ($msg->Connect()) {
        $msg->login();
} else {
        echo "boeeeeeee";
}
exit;


?>

<?php

include('classes.php');
include('config.php');
sm_include('Messages');
$msg =& new messages('imap',array('username' => $username,
                                 'host'=> $host,
                                 'password'=>$password,
                                 'port' => $port,
                                 'type'=>'imap'));
echo '<pre>';
if ($msg->Connect()) {
        $msg->login($username,$password,$host);
} else {
        echo "LOGIN FAILED";
}

$msg->getMailBoxTree();

exit;


?>

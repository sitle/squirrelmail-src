<?php
/*
**  SquirrelMail Configuration File
**
**  This file contains the variables that configure SquirrelMail for
**  your particular site.  This is a PHP file so we can include it 
**  rather than spend time parsing.
*/

/* Choose the APIs to implement */
$mail_api    = 'general';
$pref_api    = 'filesystem';
$abook_api   = 'filesystem';
$log_api     = 'filesystem';
$send_api    = 'sendmail';
$auth_api    = 'imap';
$session_api = 'php';

/* General organization configuration */
$domain      = 'squirrelmail.org';
$org_name    = 'CommNav';
$org_logo    = './images/sm_logo.jpg';

/* Options for the PREF_API */
$pref_api_filesystem_data_dir = './data';

$auth_api_imap_host           = 'localhost';
$auth_api_imap_port           = 143;
	
$mail_api_host                = 'localhost';
$mail_api_port                = 25;

?>

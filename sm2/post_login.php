<?php

	/*
	**  SquirrelMail 2
	**  Copyright (c) 1999-2001 The SquirrelMail Foundation
	**  Licensed under the GNU GPL.  For full terms see the file COPYING.
	**
	**  Login
	**  $id$
    **
    **  This takes three arguments:
    **    $login_username
    **    $login_password
    **    $domain
    ** 
    **  These three arguments should be posted from the main login page.
    **  When execuated, a session will be initialized, and cookies will
    **  be stored on the client's browser.
	*/

	include ('./etc/sm_config.php');
	include ('./lib/string.inc');
	include ('./lib/security.inc');

	include("./lib/api_auth/sm_auth_$auth_api.inc");
	$sm_auth = new sm_api_auth();
	if (! $sm_auth->is_valid($login_username, $login_password, $domain)) {
		header ("Location: login.php?st=invalid");
		exit;
	}

	include("./lib/api_session/sm_session_$session_api.inc");
	$sm_session = new sm_api_session($SID);
	
	$otp = sm_otp_create(strlen($login_password));
	$username = $login_username;
	$sm_session->set("otp", $otp);
	$sm_session->set("username", $username);

	setcookie("key", sm_otp_encrypt($login_password, $otp), 0);

	header("Location: main.php");

?>

<?php

	/*
	**  SquirrelMail 2
	**  Copyright (c) 1999-2001 The SquirrelMail Foundation
	**  Licensed under the GNU GPL.  For full terms see the file COPYING.
	**
	**  Standard -- This gets included on every page and validates the
	**  user, loads prefs, initializes the session, and all sorts of good
	**  stuff.
	**
	**  $id$
	*/

	define ("SORT_NONE",         1);
	define ("SORT_DATE_UP",      2);
	define ("SORT_DATE_DOWN",    3);
	
	include ('./etc/sm_config.php');
	include ('./lib/string.inc');
	include ('./lib/security.inc');
	include ('./lib/i18n.inc');
	include ('./lib/helper.inc');

	/*
	**  Initialize the session.
	*/
	include("./lib/api_session/sm_session_$session_api.inc");
	$sm_session = new sm_api_session($SID);
	if ($sm_session->isset_var("username") && $sm_session->isset_var('otp') && $key) {
		$username = $sm_session->get('username');
		$password = sm_otp_decrypt($key, $sm_session->get('otp'));
        if (isset($cfold)) {
            $current_folder = $cfold;
            $sm_session->set('current_folder', $cfold);
        } else if (!isset($current_folder) && 
                   !$current_folder = $sm_session->get('current_folder')) {
			$current_folder = 'INBOX';
		}
		global $username, $password, $current_folder;
	} else {
		header ("Location: login.php?st=expired");
		exit;
	}

	/*  The auth API _must_ be loaded because this is what we authenticate
	**  the user from.
	*/
	include("./lib/api_auth/sm_auth_$auth_api.inc");
	$sm_auth = new sm_api_auth();
	if (! $sm_auth->is_valid($username, $password, $domain)) {
		header ("Location: login.php?st=invalid");
		exit;
	}
	
	/*
	**  Load the preferences after they have been validated.
	**  This also loads the default prefs and overlays the user
	**  prefs on top of it using array_merge.
	*/
	include ("./lib/api_pref/sm_pref_$pref_api.inc");
	include ("./etc/default_prefs.php");
	$sm_pref = new sm_api_pref($username, $password);
	$pref = $sm_pref->get("*");
	$pref = array_merge($default_pref, $pref);
	
	global $pref, $sm_session, $sm_auth, $sm_pref;

	include("./lib/api_mail/sm_mail_$mail_api.inc");
#	include("./lib/api_abook/sm_abook_$abook_api.inc");
#	include("./lib/api_log/sm_log_$log_api.inc");
#	include("./lib/api_send/sm_send_$send_api.inc");

?>

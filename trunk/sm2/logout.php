<?php

	/*
	**  SquirrelMail 2
	**  Copyright (c) 1999-2001 The SquirrelMail Foundation
	**  Licensed under the GNU GPL.  For full terms see the file COPYING.
	**
	**  Logout
	**  $id$
	*/

	include ('./etc/sm_config.php');
	include ('./lib/security.inc');
	include ('./lib/i18n.inc');

	include("./lib/api_session/sm_session_$session_api.inc");
	$sm_session = new sm_api_session($SID);
	
	$sm_session->destroy();
	setcookie("key", '', 0);

	if (!$default_front_end) {
		$default_front_end = 'classic';
	}
	include("./front_ends/$default_front_end/logout.php");

?>

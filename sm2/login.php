<?php

	/*
	**  SquirrelMail 2
	**  Copyright (c) 1999-2001 The SquirrelMail Foundation
	**  Licensed under the GNU GPL.  For full terms see the file COPYING.
	**
	**  Login
	**  $id$
	*/

	include ('./etc/sm_config.php');
	include ('./lib/string.inc');
	include ('./lib/i18n.inc');

	if (!$default_front_end) {
		$default_front_end = 'classic';
	}
	include ("./front_ends/$default_front_end/login.php");
	
?>

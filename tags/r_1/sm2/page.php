<?php

	/*
	**  SquirrelMail 2
	**  Copyright (c) 1999-2001 The SquirrelMail Foundation
	**  Licensed under the GNU GPL.  For full terms see the file COPYING.
	**
	**  Login
	**  $id$
	*/

	include ("./lib/standard.inc");

	$file = "./front_ends/" . $pref['front_end'] . "/$page.php";
	if (file_exists($file)) {
		include($file);
	} else {
		include("./front_ends/" . $pref['front_end'] . "/message_list.php");
	}
	
?>

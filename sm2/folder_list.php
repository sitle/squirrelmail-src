<?php

	/*
	**  SquirrelMail 2
	**  Copyright (c) 1999-2001 The SquirrelMail Foundation
	**  Licensed under the GNU GPL.  For full terms see the file COPYING.
	**
	**  Folder List 
	**  $id$
	*/

	include ("./lib/standard.inc");
	$sm_mail = new sm_mail($username, $password, $mail_api_host, $mail_api_port, $current_folder);
	include ("./front_ends/" . $pref['front_end'] . "/folder_list.php");

?>

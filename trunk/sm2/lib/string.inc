<?php

	/*
	**  SquirrelMail 2
	**  Copyright (c) 1999-2001 The SquirrelMail Foundation
	**  Licensed under the GNU GPL.  For full terms see the file COPYING.
	**
	**  Utility Functions -- String Manipulation
	**  $id$
	*/

	function quoteIMAP($str) {
		return ereg_replace('(["\\])', '\\\\1', $str);
	}

	$sm_version = '1.9.0 [cvs]';

?>

<?php

	/*
	**  SquirrelMail 2
	**  Copyright (c) 1999-2001 The SquirrelMail Foundation
	**  Licensed under the GNU GPL.  For full terms see the file COPYING.
	**
	**  Utility Functions -- Helper Functions 
	**  $id$
	*/

	function sm_helper_get_headers(&$sm_mail, $start, $end, $sort_type=SORT_NONE) {
		return $sm_mail->fetch_brief_headers($start, $end);
	}

	function sm_helper_get_formatted_date($timestamp) {
		$now = time();
		$timezone = date('Z', $now);
		$midnight = $now - ($now % 86400) - $timezone;
		
		// Today
		if ($midnight < $timestamp) {
			return date('g:i a', $timestamp);
		// This Week
		} else if ($midnight - 518400 < $timestamp) {
			return date('D, g:i a', $timestamp);
		// Before This Week
		} else {
			return date('M j, Y', $timestamp);
		}
	}

	function sm_helper_get_formatted_name($string) {
	}

?>

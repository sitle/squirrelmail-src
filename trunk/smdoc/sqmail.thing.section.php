<?php
/*
 * Modified page index for SquirrelMail
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * $Id$
 */

/* print crc32('createsqmsection'); */

$HARDTHING[SECTIONCLASSID]['func'] = 'section';
$HARDTHING[SECTIONCLASSID]['title'] = 'Create New Section';
$HARDTHING[SECTIONCLASSID]['lastmodified'] = '$Date$';

function section() {
	track('section');

	section::method_create('section');

	track();
}

?>

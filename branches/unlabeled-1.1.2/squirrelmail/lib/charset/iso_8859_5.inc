<?php

/**
 * iso_8859_5.inc
 *
 * Copyright (c) 1999-2002 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file contains functions that are needed to do decode iso_8859_5 
 * charsets for SquirrelMail.
 *
 * $Id$
 */

/* ISO-8859-5 is Cyrillic */
function charset_decode_iso_8859_5 ($string, $default_charset) {
    // Convert to KOI8-R, then return this decoded.
    $string = convert_cyr_string($string, 'i', 'k');
    require_once (SM_LIB_PATH .'charset/'. 'koi8_r.inc');
    return charset_decode_koi8_r($string, $default_charset);
}


?>

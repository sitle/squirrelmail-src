<?php

/**
 * windows_1251.inc
 *
 * Copyright (c) 1999-2002 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file contains functions that are needed to do decode windows-1251
 * charsets for SquirrelMail.
 *
 * $Id$
 */

/* windows-1251 is koi8_r */
function charset_decode_windows_1251($string, $default_charset) {
    // Convert to KOI8-R, then return this decoded.
    $string = convert_cyr_string($string, 'i', 'k');
    require_once (SM_LIB_PATH .'charset/'. 'koi8_r.inc');
    return charset_decode_koi8_r($string, $default_charset);
}


?>


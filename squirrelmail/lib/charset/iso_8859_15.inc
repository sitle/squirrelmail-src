<?php

/**
 * iso_8859_15.inc
 *
 * Copyright (c) 1999-2002 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file contains functions that are needed to do decode iso_8859_15 
 * charsets for SquirrelMail.
 *
 * $Id$
 */

/*
 * iso-8859-15 is Latin 9 and has very much the same use as Latin 1
 * but has the Euro symbol and some characters needed for French.
 */
function charset_decode_iso_8859_15 ($string, $default_charset) {
    // Euro sign
    $string = str_replace ("\244", '&#8364;', $string);
    // Latin capital letter S with caron
    $string = str_replace ("\246", '&#352;', $string);
    // Latin small letter s with caron
    $string = str_replace ("\250", '&#353;', $string);
    // Latin capital letter Z with caron
    $string = str_replace ("\264", '&#381;', $string);
    // Latin small letter z with caron
    $string = str_replace ("\270", '&#382;', $string);
    // Latin capital ligature OE
    $string = str_replace ("\274", '&#338;', $string);
    // Latin small ligature oe
    $string = str_replace ("\275", '&#339;', $string);
    // Latin capital letter Y with diaeresis
    $string = str_replace ("\276", '&#376;', $string);

    return (charset_decode_iso_8859_1($string, $default_charset));
}

?>

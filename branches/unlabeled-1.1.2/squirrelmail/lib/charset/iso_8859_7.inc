<?php

/**
 * iso_8859_7.inc
 *
 * Copyright (c) 1999-2002 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file contains functions that are needed to do decode iso_8859_7 
 * charsets for SquirrelMail.
 *
 * $Id$
 */

/* iso-8859-7 is Greek. */
function charset_decode_iso_8859_7 ($string, $default_charset) {

    if (strtolower($default_charset) == 'iso-8859-7') {
        return $string;
    }

    /* Only do the slow convert if there are 8-bit characters */
    if (!ereg("[\200-\377]", $string)) {
        return $string;
    }

    /* Some diverse characters in the beginning */
    $string = str_replace("\240", '&#160;', $string);
    $string = str_replace("\241", '&#8216;', $string);
    $string = str_replace("\242", '&#8217;', $string);
    $string = str_replace("\243", '&#163;', $string);
    $string = str_replace("\246", '&#166;', $string);
    $string = str_replace("\247", '&#167;', $string);
    $string = str_replace("\250", '&#168;', $string);
    $string = str_replace("\251", '&#169;', $string);
    $string = str_replace("\253", '&#171;', $string);
    $string = str_replace("\254", '&#172;', $string);
    $string = str_replace("\255", '&#173;', $string);
    $string = str_replace("\257", '&#8213;', $string);
    $string = str_replace("\260", '&#176;', $string);
    $string = str_replace("\261", '&#177;', $string);
    $string = str_replace("\262", '&#178;', $string);
    $string = str_replace("\263", '&#179;', $string);

    /* Horizontal bar (parentheki pavla) */
    $string = str_replace ("\257", '&#8213;', $string);

    /*
     * ISO-8859-7 characters from 11/04 (0xB4) to 11/06 (0xB6)
     * These are Unicode 900-902
     */
    $string = preg_replace("/([\264-\266])/e","'&#' . (ord('\\1')+720);",$string);
    
    /* 11/07 (0xB7) Middle dot is the same in iso-8859-1 */
    $string = str_replace("\267", '&#183;', $string);

    /*
     * ISO-8859-7 characters from 11/08 (0xB8) to 11/10 (0xBA)
     * These are Unicode 900-902
     */
    $string = preg_replace("/([\270-\272])/e","'&#' . (ord('\\1')+720);",$string);

    /*
     * 11/11 (0xBB) Right angle quotation mark is the same as in
     * iso-8859-1
     */
    $string = str_replace("\273", '&#187;', $string);

    /* And now the rest of the charset */
    $string = preg_replace("/([\274-\376])/e","'&#'.(ord('\\1')+720);",$string);

    return $string;
}


?>
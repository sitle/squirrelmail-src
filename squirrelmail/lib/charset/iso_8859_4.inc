<?php

/**
 * iso_8859_4.inc
 *
 * Copyright (c) 1999-2002 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file contains functions that are needed to do decode iso_8859_4 
 * charsets for SquirrelMail.
 *
 * $Id$
 */

/* 
   iso-8859-4 is Baltic codeset used in some email clients 
   instead of iso-8859-13 in Lithuania 
   only Lithuanian charactes are added.
*/

function charset_decode_iso_8859_4 ($string, $default_charset) {
    // latin capital a with ogonek
    $string = str_replace ("\241", '&#0260;', $string);
    // latin capital c with caron
    $string = str_replace ("\310", '&#0268;', $string);
    // latin capital e with ogonek
    $string = str_replace ("\312", '&#0280;', $string);
    // latin capital e with dot above
    $string = str_replace ("\314", '&#0278;', $string);
    // latin capital i with ogonek
    $string = str_replace ("\307", '&#0302;', $string);
    // latin capital s with caron
    $string = str_replace ("\251", '&#0352;', $string);
    // latin capital u with ogonek
    $string = str_replace ("\331", '&#0370;', $string);
    // latin capital u with macron
    $string = str_replace ("\336", '&#0362;', $string);
    // latin capital z with caron
    $string = str_replace ("\256", '&#0381;', $string);
    // latin small a with ogonek
    $string = str_replace ("\261", '&#0261;', $string);
    // latin small c with caron
    $string = str_replace ("\350", '&#0269;', $string);
    // latin small e with ogonek
    $string = str_replace ("\352", '&#0281;', $string);
    // latin small e with dot above
    $string = str_replace ("\354", '&#0279;', $string);
    // latin small i with ogonek
    $string = str_replace ("\347", '&#0303;', $string);
    // latin small s with caron
    $string = str_replace ("\271", '&#0353;', $string);
    // latin small u with ogonek
    $string = str_replace ("\371", '&#0371;', $string);
    // latin small u with macron
    $string = str_replace ("\376", '&#0363;', $string);
    // latin small z with caron
    $string = str_replace ("\276", '&#0382;', $string);

    require_once(SM_LIB_PATH .'charset/'. 'iso_8859_1');
    return (charset_decode_iso_8859_1($string, $default_charset));
}

?>
<?php

/**
 * iso_8859_2.inc
 *
 * Copyright (c) 1999-2002 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file contains functions that are needed to do decode iso_8859_2 
 * charsets for SquirrelMail.
 *
 * $Id$
 */
/* iso-8859-2 is used for some eastern European languages */

function charset_decode_iso_8859_2 ($string, $default_charset) {

    if (strtolower($default_charset) == 'iso-8859-2')
        return $string;

    /* Only do the slow convert if there are 8-bit characters */
    if (! ereg("[\200-\377]", $string))
        return $string;

    /* NO-BREAK SPACE */
    $string = str_replace("\240", '&#160;', $string);
    /* LATIN CAPITAL LETTER A WITH OGONEK */
    $string = str_replace("\241", '&#260;', $string);
    /* BREVE */
    $string = str_replace("\242", '&#728;', $string);
    // LATIN CAPITAL LETTER L WITH STROKE
    $string = str_replace("\243", '&#321;', $string);
    // CURRENCY SIGN
    $string = str_replace("\244", '&#164;', $string);
    // LATIN CAPITAL LETTER L WITH CARON
    $string = str_replace("\245", '&#317;', $string);
    // LATIN CAPITAL LETTER S WITH ACUTE
    $string = str_replace("\246", '&#346;', $string);
    // SECTION SIGN
    $string = str_replace("\247", '&#167;', $string);
    // DIAERESIS
    $string = str_replace("\250", '&#168;', $string);
    // LATIN CAPITAL LETTER S WITH CARON
    $string = str_replace("\251", '&#352;', $string);
    // LATIN CAPITAL LETTER S WITH CEDILLA
    $string = str_replace("\252", '&#350;', $string);
    // LATIN CAPITAL LETTER T WITH CARON
    $string = str_replace("\253", '&#356;', $string);
    // LATIN CAPITAL LETTER Z WITH ACUTE
    $string = str_replace("\254", '&#377;', $string);
    // SOFT HYPHEN
    $string = str_replace("\255", '&#173;', $string);
    // LATIN CAPITAL LETTER Z WITH CARON
    $string = str_replace("\256", '&#381;', $string);
    // LATIN CAPITAL LETTER Z WITH DOT ABOVE
    $string = str_replace("\257", '&#379;', $string);
    // DEGREE SIGN
    $string = str_replace("\260", '&#176;', $string);
    // LATIN SMALL LETTER A WITH OGONEK
    $string = str_replace("\261", '&#261;', $string);
    // OGONEK
    $string = str_replace("\262", '&#731;', $string);
    // LATIN SMALL LETTER L WITH STROKE
    $string = str_replace("\263", '&#322;', $string);
    // ACUTE ACCENT
    $string = str_replace("\264", '&#180;', $string);
    // LATIN SMALL LETTER L WITH CARON
    $string = str_replace("\265", '&#318;', $string);
    // LATIN SMALL LETTER S WITH ACUTE
    $string = str_replace("\266", '&#347;', $string);
    // CARON
    $string = str_replace("\267", '&#711;', $string);
    // CEDILLA
    $string = str_replace("\270", '&#184;', $string);
    // LATIN SMALL LETTER S WITH CARON
    $string = str_replace("\271", '&#353;', $string);
    // LATIN SMALL LETTER S WITH CEDILLA
    $string = str_replace("\272", '&#351;', $string);
    // LATIN SMALL LETTER T WITH CARON
    $string = str_replace("\273", '&#357;', $string);
    // LATIN SMALL LETTER Z WITH ACUTE
    $string = str_replace("\274", '&#378;', $string);
    // DOUBLE ACUTE ACCENT
    $string = str_replace("\275", '&#733;', $string);
    // LATIN SMALL LETTER Z WITH CARON
    $string = str_replace("\276", '&#382;', $string);
    // LATIN SMALL LETTER Z WITH DOT ABOVE
    $string = str_replace("\277", '&#380;', $string);
    // LATIN CAPITAL LETTER R WITH ACUTE
    $string = str_replace("\300", '&#340;', $string);
    // LATIN CAPITAL LETTER A WITH ACUTE
    $string = str_replace("\301", '&#193;', $string);
    // LATIN CAPITAL LETTER A WITH CIRCUMFLEX
    $string = str_replace("\302", '&#194;', $string);
    // LATIN CAPITAL LETTER A WITH BREVE
    $string = str_replace("\303", '&#258;', $string);
    // LATIN CAPITAL LETTER A WITH DIAERESIS
    $string = str_replace("\304", '&#196;', $string);
    // LATIN CAPITAL LETTER L WITH ACUTE
    $string = str_replace("\305", '&#313;', $string);
    // LATIN CAPITAL LETTER C WITH ACUTE
    $string = str_replace("\306", '&#262;', $string);
    // LATIN CAPITAL LETTER C WITH CEDILLA
    $string = str_replace("\307", '&#199;', $string);
    // LATIN CAPITAL LETTER C WITH CARON
    $string = str_replace("\310", '&#268;', $string);
    // LATIN CAPITAL LETTER E WITH ACUTE
    $string = str_replace("\311", '&#201;', $string);
    // LATIN CAPITAL LETTER E WITH OGONEK
    $string = str_replace("\312", '&#280;', $string);
    // LATIN CAPITAL LETTER E WITH DIAERESIS
    $string = str_replace("\313", '&#203;', $string);
    // LATIN CAPITAL LETTER E WITH CARON
    $string = str_replace("\314", '&#282;', $string);
    // LATIN CAPITAL LETTER I WITH ACUTE
    $string = str_replace("\315", '&#205;', $string);
    // LATIN CAPITAL LETTER I WITH CIRCUMFLEX
    $string = str_replace("\316", '&#206;', $string);
    // LATIN CAPITAL LETTER D WITH CARON
    $string = str_replace("\317", '&#270;', $string);
    // LATIN CAPITAL LETTER D WITH STROKE
    $string = str_replace("\320", '&#272;', $string);
    // LATIN CAPITAL LETTER N WITH ACUTE
    $string = str_replace("\321", '&#323;', $string);
    // LATIN CAPITAL LETTER N WITH CARON
    $string = str_replace("\322", '&#327;', $string);
    // LATIN CAPITAL LETTER O WITH ACUTE
    $string = str_replace("\323", '&#211;', $string);
    // LATIN CAPITAL LETTER O WITH CIRCUMFLEX
    $string = str_replace("\324", '&#212;', $string);
    // LATIN CAPITAL LETTER O WITH DOUBLE ACUTE
    $string = str_replace("\325", '&#336;', $string);
    // LATIN CAPITAL LETTER O WITH DIAERESIS
    $string = str_replace("\326", '&#214;', $string);
    // MULTIPLICATION SIGN
    $string = str_replace("\327", '&#215;', $string);
    // LATIN CAPITAL LETTER R WITH CARON
    $string = str_replace("\330", '&#344;', $string);
    // LATIN CAPITAL LETTER U WITH RING ABOVE
    $string = str_replace("\331", '&#366;', $string);
    // LATIN CAPITAL LETTER U WITH ACUTE
    $string = str_replace("\332", '&#218;', $string);
    // LATIN CAPITAL LETTER U WITH DOUBLE ACUTE
    $string = str_replace("\333", '&#368;', $string);
    // LATIN CAPITAL LETTER U WITH DIAERESIS
    $string = str_replace("\334", '&#220;', $string);
    // LATIN CAPITAL LETTER Y WITH ACUTE
    $string = str_replace("\335", '&#221;', $string);
    // LATIN CAPITAL LETTER T WITH CEDILLA
    $string = str_replace("\336", '&#354;', $string);
    // LATIN SMALL LETTER SHARP S
    $string = str_replace("\337", '&#223;', $string);
    // LATIN SMALL LETTER R WITH ACUTE
    $string = str_replace("\340", '&#341;', $string);
    // LATIN SMALL LETTER A WITH ACUTE
    $string = str_replace("\341", '&#225;', $string);
    // LATIN SMALL LETTER A WITH CIRCUMFLEX
    $string = str_replace("\342", '&#226;', $string);
    // LATIN SMALL LETTER A WITH BREVE
    $string = str_replace("\343", '&#259;', $string);
    // LATIN SMALL LETTER A WITH DIAERESIS
    $string = str_replace("\344", '&#228;', $string);
    // LATIN SMALL LETTER L WITH ACUTE
    $string = str_replace("\345", '&#314;', $string);
    // LATIN SMALL LETTER C WITH ACUTE
    $string = str_replace("\346", '&#263;', $string);
    // LATIN SMALL LETTER C WITH CEDILLA
    $string = str_replace("\347", '&#231;', $string);
    // LATIN SMALL LETTER C WITH CARON
    $string = str_replace("\350", '&#269;', $string);
    // LATIN SMALL LETTER E WITH ACUTE
    $string = str_replace("\351", '&#233;', $string);
    // LATIN SMALL LETTER E WITH OGONEK
    $string = str_replace("\352", '&#281;', $string);
    // LATIN SMALL LETTER E WITH DIAERESIS
    $string = str_replace("\353", '&#235;', $string);
    // LATIN SMALL LETTER E WITH CARON
    $string = str_replace("\354", '&#283;', $string);
    // LATIN SMALL LETTER I WITH ACUTE
    $string = str_replace("\355", '&#237;', $string);
    // LATIN SMALL LETTER I WITH CIRCUMFLEX
    $string = str_replace("\356", '&#238;', $string);
    // LATIN SMALL LETTER D WITH CARON
    $string = str_replace("\357", '&#271;', $string);
    // LATIN SMALL LETTER D WITH STROKE
    $string = str_replace("\360", '&#273;', $string);
    // LATIN SMALL LETTER N WITH ACUTE
    $string = str_replace("\361", '&#324;', $string);
    // LATIN SMALL LETTER N WITH CARON
    $string = str_replace("\362", '&#328;', $string);
    // LATIN SMALL LETTER O WITH ACUTE
    $string = str_replace("\363", '&#243;', $string);
    // LATIN SMALL LETTER O WITH CIRCUMFLEX
    $string = str_replace("\364", '&#244;', $string);
    // LATIN SMALL LETTER O WITH DOUBLE ACUTE
    $string = str_replace("\365", '&#337;', $string);
    // LATIN SMALL LETTER O WITH DIAERESIS
    $string = str_replace("\366", '&#246;', $string);
    // DIVISION SIGN
    $string = str_replace("\367", '&#247;', $string);
    // LATIN SMALL LETTER R WITH CARON
    $string = str_replace("\370", '&#345;', $string);
    // LATIN SMALL LETTER U WITH RING ABOVE
    $string = str_replace("\371", '&#367;', $string);
    // LATIN SMALL LETTER U WITH ACUTE
    $string = str_replace("\372", '&#250;', $string);
    // LATIN SMALL LETTER U WITH DOUBLE ACUTE
    $string = str_replace("\373", '&#369;', $string);
    // LATIN SMALL LETTER U WITH DIAERESIS
    $string = str_replace("\374", '&#252;', $string);
    // LATIN SMALL LETTER Y WITH ACUTE
    $string = str_replace("\375", '&#253;', $string);
    // LATIN SMALL LETTER T WITH CEDILLA
    $string = str_replace("\376", '&#355;', $string);
    // DOT ABOVE
    $string = str_replace("\377", '&#729;', $string);

    return $string;
}

?>
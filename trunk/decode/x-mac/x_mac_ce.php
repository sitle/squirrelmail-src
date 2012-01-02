<?php
/**
 * functions/decode/x-mac-ce.php
 * $Id$
 *
 * Copyright (c) 2003-2012 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * Original data taken from:
 *  ftp://ftp.unicode.org/Public/MAPPINGS/VENDORS/APPLE/CENTEURO.TXT
 * 
 * Contents:
 * Map (external version) from Mac OS Central European
 * character set to Unicode 2.1 through Unicode 3.2.
 * 
 * Copyright:  (c) 1995-2002 by Apple Computer, Inc., all rights reserved.
 *
 * Contact:    charsets@apple.com
 *
 * Standard header:
 * Apple, the Apple logo, and Macintosh are trademarks of Apple
 * Computer, Inc., registered in the United States and other countries.
 * Unicode is a trademark of Unicode Inc. For the sake of brevity,
 * throughout this document, ""Macintosh"" can be used to refer to
 * Macintosh computers and ""Unicode"" can be used to refer to the
 * Unicode standard.
 *
 * Apple makes no warranty or representation, either express or
 * implied, with respect to these tables, their quality, accuracy, or
 * fitness for a particular purpose. In no event will Apple be liable
 * for direct, indirect, special, incidental, or consequential damages 
 * resulting from any defect or inaccuracy in this document or the
 * accompanying tables.
 * 
 * These mapping tables and character lists are subject to change.
 * The latest tables should be available from the following:
 * 
 * <http://www.unicode.org/Public/MAPPINGS/VENDORS/APPLE/>
 *
 * @package decode
 * @subpackage x-mac
 */

/**
 * Decode x-mac-ce string
 * @param string $string String to decode
 * @return string $string Html formated string
 */
function charset_decode_x_mac_ce ($string) {
    // don't do decoding when there are no 8bit symbols
    if (! sq_is8bit($string,'x-mac-ce'))
        return $string;

    $mac_ce = array(
        "\x80" => '&#196;',
        "\x81" => '&#256;',
        "\x82" => '&#257;',
        "\x83" => '&#201;',
        "\x84" => '&#260;',
        "\x85" => '&#214;',
        "\x86" => '&#220;',
        "\x87" => '&#225;',
        "\x88" => '&#261;',
        "\x89" => '&#268;',
        "\x8A" => '&#228;',
        "\x8B" => '&#269;',
        "\x8C" => '&#262;',
        "\x8D" => '&#263;',
        "\x8E" => '&#233;',
        "\x8F" => '&#377;',
        "\x90" => '&#378;',
        "\x91" => '&#270;',
        "\x92" => '&#237;',
        "\x93" => '&#271;',
        "\x94" => '&#274;',
        "\x95" => '&#275;',
        "\x96" => '&#278;',
        "\x97" => '&#243;',
        "\x98" => '&#279;',
        "\x99" => '&#244;',
        "\x9A" => '&#246;',
        "\x9B" => '&#245;',
        "\x9C" => '&#250;',
        "\x9D" => '&#282;',
        "\x9E" => '&#283;',
        "\x9F" => '&#252;',
        "\xA0" => '&#8224;',
        "\xA1" => '&#176;',
        "\xA2" => '&#280;',
        "\xA3" => '&#163;',
        "\xA4" => '&#167;',
        "\xA5" => '&#8226;',
        "\xA6" => '&#182;',
        "\xA7" => '&#223;',
        "\xA8" => '&#174;',
        "\xA9" => '&#169;',
        "\xAA" => '&#8482;',
        "\xAB" => '&#281;',
        "\xAC" => '&#168;',
        "\xAD" => '&#8800;',
        "\xAE" => '&#291;',
        "\xAF" => '&#302;',
        "\xB0" => '&#303;',
        "\xB1" => '&#298;',
        "\xB2" => '&#8804;',
        "\xB3" => '&#8805;',
        "\xB4" => '&#299;',
        "\xB5" => '&#310;',
        "\xB6" => '&#8706;',
        "\xB7" => '&#8721;',
        "\xB8" => '&#322;',
        "\xB9" => '&#315;',
        "\xBA" => '&#316;',
        "\xBB" => '&#317;',
        "\xBC" => '&#318;',
        "\xBD" => '&#313;',
        "\xBE" => '&#314;',
        "\xBF" => '&#325;',
        "\xC0" => '&#326;',
        "\xC1" => '&#323;',
        "\xC2" => '&#172;',
        "\xC3" => '&#8730;',
        "\xC4" => '&#324;',
        "\xC5" => '&#327;',
        "\xC6" => '&#8710;',
        "\xC7" => '&#171;',
        "\xC8" => '&#187;',
        "\xC9" => '&#8230;',
        "\xCA" => '&#160;',
        "\xCB" => '&#328;',
        "\xCC" => '&#336;',
        "\xCD" => '&#213;',
        "\xCE" => '&#337;',
        "\xCF" => '&#332;',
        "\xD0" => '&#8211;',
        "\xD1" => '&#8212;',
        "\xD2" => '&#8220;',
        "\xD3" => '&#8221;',
        "\xD4" => '&#8216;',
        "\xD5" => '&#8217;',
        "\xD6" => '&#247;',
        "\xD7" => '&#9674;',
        "\xD8" => '&#333;',
        "\xD9" => '&#340;',
        "\xDA" => '&#341;',
        "\xDB" => '&#344;',
        "\xDC" => '&#8249;',
        "\xDD" => '&#8250;',
        "\xDE" => '&#345;',
        "\xDF" => '&#342;',
        "\xE0" => '&#343;',
        "\xE1" => '&#352;',
        "\xE2" => '&#8218;',
        "\xE3" => '&#8222;',
        "\xE4" => '&#353;',
        "\xE5" => '&#346;',
        "\xE6" => '&#347;',
        "\xE7" => '&#193;',
        "\xE8" => '&#356;',
        "\xE9" => '&#357;',
        "\xEA" => '&#205;',
        "\xEB" => '&#381;',
        "\xEC" => '&#382;',
        "\xED" => '&#362;',
        "\xEE" => '&#211;',
        "\xEF" => '&#212;',
        "\xF0" => '&#363;',
        "\xF1" => '&#366;',
        "\xF2" => '&#218;',
        "\xF3" => '&#367;',
        "\xF4" => '&#368;',
        "\xF5" => '&#369;',
        "\xF6" => '&#370;',
        "\xF7" => '&#371;',
        "\xF8" => '&#221;',
        "\xF9" => '&#253;',
        "\xFA" => '&#311;',
        "\xFB" => '&#379;',
        "\xFC" => '&#321;',
        "\xFD" => '&#380;',
        "\xFE" => '&#290;',
        "\xFF" => '&#711;');

    $string = str_replace(array_keys($mac_ce), array_values($mac_ce), $string);

    return $string;
}

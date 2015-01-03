<?php
/**
 * functions/decode/x-mac-greek.php
 * $Id$
 *
 * Copyright (c) 2003-2015 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * Original data taken from:
 *  ftp://ftp.unicode.org/Public/MAPPINGS/VENDORS/APPLE/GREEK.TXT
 * 
 * Contents:   Map (external version) from Mac OS Greek
 *             character set to Unicode 2.1 through Unicode 3.2
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
 * Decode x-mac-greek string
 * @param string $string String to decode
 * @return string $string Html formated string
 */
function charset_decode_x_mac_greek ($string) {
    // don't do decoding when there are no 8bit symbols
    if (! sq_is8bit($string,'x-mac-greek'))
        return $string;

    $mac_greek = array(
        "\x80" => '&#196;',
        "\x81" => '&#185;',
        "\x82" => '&#178;',
        "\x83" => '&#201;',
        "\x84" => '&#179;',
        "\x85" => '&#214;',
        "\x86" => '&#220;',
        "\x87" => '&#901;',
        "\x88" => '&#224;',
        "\x89" => '&#226;',
        "\x8A" => '&#228;',
        "\x8B" => '&#900;',
        "\x8C" => '&#168;',
        "\x8D" => '&#231;',
        "\x8E" => '&#233;',
        "\x8F" => '&#232;',
        "\x90" => '&#234;',
        "\x91" => '&#235;',
        "\x92" => '&#163;',
        "\x93" => '&#8482;',
        "\x94" => '&#238;',
        "\x95" => '&#239;',
        "\x96" => '&#8226;',
        "\x97" => '&#189;',
        "\x98" => '&#8240;',
        "\x99" => '&#244;',
        "\x9A" => '&#246;',
        "\x9B" => '&#166;',
        "\x9C" => '&#8364;',
        "\x9D" => '&#249;',
        "\x9E" => '&#251;',
        "\x9F" => '&#252;',
        "\xA0" => '&#8224;',
        "\xA1" => '&#915;',
        "\xA2" => '&#916;',
        "\xA3" => '&#920;',
        "\xA4" => '&#923;',
        "\xA5" => '&#926;',
        "\xA6" => '&#928;',
        "\xA7" => '&#223;',
        "\xA8" => '&#174;',
        "\xA9" => '&#169;',
        "\xAA" => '&#931;',
        "\xAB" => '&#938;',
        "\xAC" => '&#167;',
        "\xAD" => '&#8800;',
        "\xAE" => '&#176;',
        "\xAF" => '&#183;',
        "\xB0" => '&#913;',
        "\xB1" => '&#177;',
        "\xB2" => '&#8804;',
        "\xB3" => '&#8805;',
        "\xB4" => '&#165;',
        "\xB5" => '&#914;',
        "\xB6" => '&#917;',
        "\xB7" => '&#918;',
        "\xB8" => '&#919;',
        "\xB9" => '&#921;',
        "\xBA" => '&#922;',
        "\xBB" => '&#924;',
        "\xBC" => '&#934;',
        "\xBD" => '&#939;',
        "\xBE" => '&#936;',
        "\xBF" => '&#937;',
        "\xC0" => '&#940;',
        "\xC1" => '&#925;',
        "\xC2" => '&#172;',
        "\xC3" => '&#927;',
        "\xC4" => '&#929;',
        "\xC5" => '&#8776;',
        "\xC6" => '&#932;',
        "\xC7" => '&#171;',
        "\xC8" => '&#187;',
        "\xC9" => '&#8230;',
        "\xCA" => '&#160;',
        "\xCB" => '&#933;',
        "\xCC" => '&#935;',
        "\xCD" => '&#902;',
        "\xCE" => '&#904;',
        "\xCF" => '&#339;',
        "\xD0" => '&#8211;',
        "\xD1" => '&#8213;',
        "\xD2" => '&#8220;',
        "\xD3" => '&#8221;',
        "\xD4" => '&#8216;',
        "\xD5" => '&#8217;',
        "\xD6" => '&#247;',
        "\xD7" => '&#905;',
        "\xD8" => '&#906;',
        "\xD9" => '&#908;',
        "\xDA" => '&#910;',
        "\xDB" => '&#941;',
        "\xDC" => '&#942;',
        "\xDD" => '&#943;',
        "\xDE" => '&#972;',
        "\xDF" => '&#911;',
        "\xE0" => '&#973;',
        "\xE1" => '&#945;',
        "\xE2" => '&#946;',
        "\xE3" => '&#968;',
        "\xE4" => '&#948;',
        "\xE5" => '&#949;',
        "\xE6" => '&#966;',
        "\xE7" => '&#947;',
        "\xE8" => '&#951;',
        "\xE9" => '&#953;',
        "\xEA" => '&#958;',
        "\xEB" => '&#954;',
        "\xEC" => '&#955;',
        "\xED" => '&#956;',
        "\xEE" => '&#957;',
        "\xEF" => '&#959;',
        "\xF0" => '&#960;',
        "\xF1" => '&#974;',
        "\xF2" => '&#961;',
        "\xF3" => '&#963;',
        "\xF4" => '&#964;',
        "\xF5" => '&#952;',
        "\xF6" => '&#969;',
        "\xF7" => '&#962;',
        "\xF8" => '&#967;',
        "\xF9" => '&#965;',
        "\xFA" => '&#950;',
        "\xFB" => '&#970;',
        "\xFC" => '&#971;',
        "\xFD" => '&#912;',
        "\xFE" => '&#944;',
        "\xFF" => '&#173;');

    $string = str_replace(array_keys($mac_greek), array_values($mac_greek), $string);

    return $string;
}

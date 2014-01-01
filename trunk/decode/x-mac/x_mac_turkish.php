<?php
/**
 * functions/decode/x-mac-turkish.php
 * $Id$
 *
 * Copyright (c) 2003-2014 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * Original data taken from:
 *  ftp://ftp.unicode.org/Public/MAPPINGS/VENDORS/APPLE/TURKISH.TXT
 * 
 * Contents:   Map (external version) from Mac OS Turkish
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
 * Decode x-mac-turkish string
 * @param string $string String to decode
 * @return string $string Html formated string
 */
function charset_decode_x_mac_turkish ($string) {
    // don't do decoding when there are no 8bit symbols
    if (! sq_is8bit($string,'x-mac-turkish'))
        return $string;

    $mac_turkish = array(
        "\x80" => '&#196;',
        "\x81" => '&#197;',
        "\x82" => '&#199;',
        "\x83" => '&#201;',
        "\x84" => '&#209;',
        "\x85" => '&#214;',
        "\x86" => '&#220;',
        "\x87" => '&#225;',
        "\x88" => '&#224;',
        "\x89" => '&#226;',
        "\x8A" => '&#228;',
        "\x8B" => '&#227;',
        "\x8C" => '&#229;',
        "\x8D" => '&#231;',
        "\x8E" => '&#233;',
        "\x8F" => '&#232;',
        "\x90" => '&#234;',
        "\x91" => '&#235;',
        "\x92" => '&#237;',
        "\x93" => '&#236;',
        "\x94" => '&#238;',
        "\x95" => '&#239;',
        "\x96" => '&#241;',
        "\x97" => '&#243;',
        "\x98" => '&#242;',
        "\x99" => '&#244;',
        "\x9A" => '&#246;',
        "\x9B" => '&#245;',
        "\x9C" => '&#250;',
        "\x9D" => '&#249;',
        "\x9E" => '&#251;',
        "\x9F" => '&#252;',
        "\xA0" => '&#8224;',
        "\xA1" => '&#176;',
        "\xA2" => '&#162;',
        "\xA3" => '&#163;',
        "\xA4" => '&#167;',
        "\xA5" => '&#8226;',
        "\xA6" => '&#182;',
        "\xA7" => '&#223;',
        "\xA8" => '&#174;',
        "\xA9" => '&#169;',
        "\xAA" => '&#8482;',
        "\xAB" => '&#180;',
        "\xAC" => '&#168;',
        "\xAD" => '&#8800;',
        "\xAE" => '&#198;',
        "\xAF" => '&#216;',
        "\xB0" => '&#8734;',
        "\xB1" => '&#177;',
        "\xB2" => '&#8804;',
        "\xB3" => '&#8805;',
        "\xB4" => '&#165;',
        "\xB5" => '&#181;',
        "\xB6" => '&#8706;',
        "\xB7" => '&#8721;',
        "\xB8" => '&#8719;',
        "\xB9" => '&#960;',
        "\xBA" => '&#8747;',
        "\xBB" => '&#170;',
        "\xBC" => '&#186;',
        "\xBD" => '&#937;',
        "\xBE" => '&#230;',
        "\xBF" => '&#248;',
        "\xC0" => '&#191;',
        "\xC1" => '&#161;',
        "\xC2" => '&#172;',
        "\xC3" => '&#8730;',
        "\xC4" => '&#402;',
        "\xC5" => '&#8776;',
        "\xC6" => '&#8710;',
        "\xC7" => '&#171;',
        "\xC8" => '&#187;',
        "\xC9" => '&#8230;',
        "\xCA" => '&#160;',
        "\xCB" => '&#192;',
        "\xCC" => '&#195;',
        "\xCD" => '&#213;',
        "\xCE" => '&#338;',
        "\xCF" => '&#339;',
        "\xD0" => '&#8211;',
        "\xD1" => '&#8212;',
        "\xD2" => '&#8220;',
        "\xD3" => '&#8221;',
        "\xD4" => '&#8216;',
        "\xD5" => '&#8217;',
        "\xD6" => '&#247;',
        "\xD7" => '&#9674;',
        "\xD8" => '&#255;',
        "\xD9" => '&#376;',
        "\xDA" => '&#286;',
        "\xDB" => '&#287;',
        "\xDC" => '&#304;',
        "\xDD" => '&#305;',
        "\xDE" => '&#350;',
        "\xDF" => '&#351;',
        "\xE0" => '&#8225;',
        "\xE1" => '&#183;',
        "\xE2" => '&#8218;',
        "\xE3" => '&#8222;',
        "\xE4" => '&#8240;',
        "\xE5" => '&#194;',
        "\xE6" => '&#202;',
        "\xE7" => '&#193;',
        "\xE8" => '&#203;',
        "\xE9" => '&#200;',
        "\xEA" => '&#205;',
        "\xEB" => '&#206;',
        "\xEC" => '&#207;',
        "\xED" => '&#204;',
        "\xEE" => '&#211;',
        "\xEF" => '&#212;',
        "\xF0" => '&#63743;',
        "\xF1" => '&#210;',
        "\xF2" => '&#218;',
        "\xF3" => '&#219;',
        "\xF4" => '&#217;',
        "\xF5" => '&#63648;',
        "\xF6" => '&#710;',
        "\xF7" => '&#732;',
        "\xF8" => '&#175;',
        "\xF9" => '&#728;',
        "\xFA" => '&#729;',
        "\xFB" => '&#730;',
        "\xFC" => '&#184;',
        "\xFD" => '&#733;',
        "\xFE" => '&#731;',
        "\xFF" => '&#711;');

    $string = str_replace(array_keys($mac_turkish), array_values($mac_turkish), $string);

    return $string;
}

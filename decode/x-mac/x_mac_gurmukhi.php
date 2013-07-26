<?php
/**
 * functions/decode/x_mac_gurmukhi.php
 * $Id$
 *
 * Copyright (c) 2003-2013 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * Original data taken from:
 *  ftp://ftp.unicode.org/Public/MAPPINGS/VENDORS/APPLE/GURMUKHI.TXT
 * 
 * Contents:
 * Map (external version) from Mac OS Gurmukhi
 * encoding to Unicode 2.1 through Unicode 3.2
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
 * Decode x-mac-gurmukhi string
 * @param string $string String to decode
 * @return string $string Html formated string
 */
function charset_decode_x_mac_gurmukhi ($string) {
    // don't do decoding when there are no 8bit symbols
    if (! sq_is8bit($string,'x-mac-gurmukhi'))
        return $string;

    // ZERO WIDTH NON-JOINER and ZERO WIDTH JOINER
    $string=str_replace("\xE8\xE8",'&#2637;&#8204;',$string);
    $string=str_replace("\xE8\xE9",'&#2637;&#8205;',$string);

    // Main replace array
    $mac_gurmukhi = array(
        "\x80" => '&#215;',
        "\x81" => '&#8722;',
        "\x82" => '&#8211;',
        "\x83" => '&#8212;',
        "\x84" => '&#8216;',
        "\x85" => '&#8217;',
        "\x86" => '&#8230;',
        "\x87" => '&#8226;',
        "\x88" => '&#169;',
        "\x89" => '&#174;',
        "\x8A" => '&#8482;',
        "\x90" => '&#2673;',
        "\x91" => '&#2652;',
        "\x92" => '&#2675;',
        "\x93" => '&#2674;',
        "\x94" => '&#2676;',
        "\xA2" => '&#2562;',
        "\xA4" => '&#2565;',
        "\xA5" => '&#2566;',
        "\xA6" => '&#2567;',
        "\xA7" => '&#2568;',
        "\xA8" => '&#2569;',
        "\xA9" => '&#2570;',
        "\xAC" => '&#2575;',
        "\xAD" => '&#2576;',
        "\xB0" => '&#2579;',
        "\xB1" => '&#2580;',
        "\xB3" => '&#2581;',
        "\xB4" => '&#2582;',
        "\xB5" => '&#2583;',
        "\xB6" => '&#2584;',
        "\xB7" => '&#2585;',
        "\xB8" => '&#2586;',
        "\xB9" => '&#2587;',
        "\xBA" => '&#2588;',
        "\xBB" => '&#2589;',
        "\xBC" => '&#2590;',
        "\xBD" => '&#2591;',
        "\xBE" => '&#2592;',
        "\xBF" => '&#2593;',
        "\xC0" => '&#2594;',
        "\xC1" => '&#2595;',
        "\xC2" => '&#2596;',
        "\xC3" => '&#2597;',
        "\xC4" => '&#2598;',
        "\xC5" => '&#2599;',
        "\xC6" => '&#2600;',
        "\xC8" => '&#2602;',
        "\xC9" => '&#2603;',
        "\xCA" => '&#2604;',
        "\xCB" => '&#2605;',
        "\xCC" => '&#2606;',
        "\xCD" => '&#2607;',
        "\xCF" => '&#2608;',
        "\xD1" => '&#2610;',
        "\xD4" => '&#2613;',
        "\xD5" => '&#63584;&#2616;&#2620;',
        "\xD7" => '&#2616;',
        "\xD8" => '&#2617;',
        "\xD9" => '&#8206;',
        "\xDA" => '&#2622;',
        "\xDB" => '&#2623;',
        "\xDC" => '&#2624;',
        "\xDD" => '&#2625;',
        "\xDE" => '&#2626;',
        "\xE1" => '&#2631;',
        "\xE2" => '&#2632;',
        "\xE5" => '&#2635;',
        "\xE6" => '&#2636;',
        "\xE8" => '&#2637;',
        "\xE9" => '&#2620;',
        "\xEA" => '&#2404;',
        "\xF1" => '&#2662;',
        "\xF2" => '&#2663;',
        "\xF3" => '&#2664;',
        "\xF4" => '&#2665;',
        "\xF5" => '&#2666;',
        "\xF6" => '&#2667;',
        "\xF7" => '&#2668;',
        "\xF8" => '&#2669;',
        "\xF9" => '&#2670;',
        "\xFA" => '&#2671;');

    $string = str_replace(array_keys($mac_gurmukhi), array_values($mac_gurmukhi), $string);

    return $string;
}


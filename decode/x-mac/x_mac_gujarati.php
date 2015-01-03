<?php
/**
 * functions/decode/x-mac-gujarati.php
 * $Id$
 *
 * Copyright (c) 2003-2015 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * Original data taken from:
 *  ftp://ftp.unicode.org/Public/MAPPINGS/VENDORS/APPLE/GUJARATI.TXT
 * 
 * Contents:
 * Map (external version) from Mac OS Gujarati
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
 * Decode x-mac-gujarati string
 * @param string $string String to decode
 * @return string $string Html formated string
 */
function charset_decode_x_mac_gujarati ($string) {
    // don't do decoding when there are no 8bit symbols
    if (! sq_is8bit($string,'x-mac-gujarati'))
        return $string;

    // ZERO WIDTH NON-JOINER and ZERO WIDTH JOINER symbols
    $string=str_replace("\xA1\xE9",'&#2768;',$string);
    $string=str_replace("\xAA\xE9",'&#2784;',$string);
    $string=str_replace("\xDF\xE9",'&#2756;',$string);
    $string=str_replace("\xE8\xE8",'&#2765;&#8204;',$string);
    $string=str_replace("\xE8\xE9",'&#2765;&#8205;',$string);

    // Main replace array
    $mac_gujarati = array(
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
        "\x90" => '&#2405;',
        "\xA1" => '&#2689;',
        "\xA2" => '&#2690;',
        "\xA3" => '&#2691;',
        "\xA4" => '&#2693;',
        "\xA5" => '&#2694;',
        "\xA6" => '&#2695;',
        "\xA7" => '&#2696;',
        "\xA8" => '&#2697;',
        "\xA9" => '&#2698;',
        "\xAA" => '&#2699;',
        "\xAC" => '&#2703;',
        "\xAD" => '&#2704;',
        "\xAE" => '&#2701;',
        "\xB0" => '&#2707;',
        "\xB1" => '&#2708;',
        "\xB2" => '&#2705;',
        "\xB3" => '&#2709;',
        "\xB4" => '&#2710;',
        "\xB5" => '&#2711;',
        "\xB6" => '&#2712;',
        "\xB7" => '&#2713;',
        "\xB8" => '&#2714;',
        "\xB9" => '&#2715;',
        "\xBA" => '&#2716;',
        "\xBB" => '&#2717;',
        "\xBC" => '&#2718;',
        "\xBD" => '&#2719;',
        "\xBE" => '&#2720;',
        "\xBF" => '&#2721;',
        "\xC0" => '&#2722;',
        "\xC1" => '&#2723;',
        "\xC2" => '&#2724;',
        "\xC3" => '&#2725;',
        "\xC4" => '&#2726;',
        "\xC5" => '&#2727;',
        "\xC6" => '&#2728;',
        "\xC8" => '&#2730;',
        "\xC9" => '&#2731;',
        "\xCA" => '&#2732;',
        "\xCB" => '&#2733;',
        "\xCC" => '&#2734;',
        "\xCD" => '&#2735;',
        "\xCF" => '&#2736;',
        "\xD1" => '&#2738;',
        "\xD2" => '&#2739;',
        "\xD4" => '&#2741;',
        "\xD5" => '&#2742;',
        "\xD6" => '&#2743;',
        "\xD7" => '&#2744;',
        "\xD8" => '&#2745;',
        "\xD9" => '&#8206;',
        "\xDA" => '&#2750;',
        "\xDB" => '&#2751;',
        "\xDC" => '&#2752;',
        "\xDD" => '&#2753;',
        "\xDE" => '&#2754;',
        "\xDF" => '&#2755;',
        "\xE1" => '&#2759;',
        "\xE2" => '&#2760;',
        "\xE3" => '&#2757;',
        "\xE5" => '&#2763;',
        "\xE6" => '&#2764;',
        "\xE7" => '&#2761;',
        "\xE8" => '&#2765;',
        "\xE9" => '&#2748;',
        "\xEA" => '&#2404;',
        "\xF1" => '&#2790;',
        "\xF2" => '&#2791;',
        "\xF3" => '&#2792;',
        "\xF4" => '&#2793;',
        "\xF5" => '&#2794;',
        "\xF6" => '&#2795;',
        "\xF7" => '&#2796;',
        "\xF8" => '&#2797;',
        "\xF9" => '&#2798;',
        "\xFA" => '&#2799;');

    $string = str_replace(array_keys($mac_gujarati), array_values($mac_gujarati), $string);

    return $string;
}

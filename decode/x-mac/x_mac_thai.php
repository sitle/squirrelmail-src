<?php
/**
 * functions/decode/x-mac-thai.php
 * $Id$
 *
 * Copyright (c) 2003-2013 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * Original data taken from:
 *  ftp://ftp.unicode.org/Public/MAPPINGS/VENDORS/APPLE/THAI.TXT
 * 
 * Contents:   Map (external version) from Mac OS Thai
 *             character set to Unicode 3.2
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
 * Decode x-mac-thai string
 * @param string $string String to decode
 * @return string $string Html formated string
 */
function charset_decode_x_mac_thai ($string) {
    // don't do decoding when there are no 8bit symbols
    if (! sq_is8bit($string,'x-mac-thai'))
        return $string;

    $mac_thai = array(
        "\x80" => '&#171;',
        "\x81" => '&#187;',
        "\x82" => '&#8230;',
        "\x83" => '&#3656;&#63605;',
        "\x84" => '&#3657;&#63605;',
        "\x85" => '&#3658;&#63605;',
        "\x86" => '&#3659;&#63605;',
        "\x87" => '&#3660;&#63605;',
        "\x88" => '&#3656;&#63603;',
        "\x89" => '&#3657;&#63603;',
        "\x8A" => '&#3658;&#63603;',
        "\x8B" => '&#3659;&#63603;',
        "\x8C" => '&#3660;&#63603;',
        "\x8D" => '&#8220;',
        "\x8E" => '&#8221;',
        "\x8F" => '&#3661;&#63604;',
        "\x91" => '&#8226;',
        "\x92" => '&#3633;&#63604;',
        "\x93" => '&#3655;&#63604;',
        "\x94" => '&#3636;&#63604;',
        "\x95" => '&#3637;&#63604;',
        "\x96" => '&#3638;&#63604;',
        "\x97" => '&#3639;&#63604;',
        "\x98" => '&#3656;&#63604;',
        "\x99" => '&#3657;&#63604;',
        "\x9A" => '&#3658;&#63604;',
        "\x9B" => '&#3659;&#63604;',
        "\x9C" => '&#3660;&#63604;',
        "\x9D" => '&#8216;',
        "\x9E" => '&#8217;',
        "\xA0" => '&#160;',
        "\xA1" => '&#3585;',
        "\xA2" => '&#3586;',
        "\xA3" => '&#3587;',
        "\xA4" => '&#3588;',
        "\xA5" => '&#3589;',
        "\xA6" => '&#3590;',
        "\xA7" => '&#3591;',
        "\xA8" => '&#3592;',
        "\xA9" => '&#3593;',
        "\xAA" => '&#3594;',
        "\xAB" => '&#3595;',
        "\xAC" => '&#3596;',
        "\xAD" => '&#3597;',
        "\xAE" => '&#3598;',
        "\xAF" => '&#3599;',
        "\xB0" => '&#3600;',
        "\xB1" => '&#3601;',
        "\xB2" => '&#3602;',
        "\xB3" => '&#3603;',
        "\xB4" => '&#3604;',
        "\xB5" => '&#3605;',
        "\xB6" => '&#3606;',
        "\xB7" => '&#3607;',
        "\xB8" => '&#3608;',
        "\xB9" => '&#3609;',
        "\xBA" => '&#3610;',
        "\xBB" => '&#3611;',
        "\xBC" => '&#3612;',
        "\xBD" => '&#3613;',
        "\xBE" => '&#3614;',
        "\xBF" => '&#3615;',
        "\xC0" => '&#3616;',
        "\xC1" => '&#3617;',
        "\xC2" => '&#3618;',
        "\xC3" => '&#3619;',
        "\xC4" => '&#3620;',
        "\xC5" => '&#3621;',
        "\xC6" => '&#3622;',
        "\xC7" => '&#3623;',
        "\xC8" => '&#3624;',
        "\xC9" => '&#3625;',
        "\xCA" => '&#3626;',
        "\xCB" => '&#3627;',
        "\xCC" => '&#3628;',
        "\xCD" => '&#3629;',
        "\xCE" => '&#3630;',
        "\xCF" => '&#3631;',
        "\xD0" => '&#3632;',
        "\xD1" => '&#3633;',
        "\xD2" => '&#3634;',
        "\xD3" => '&#3635;',
        "\xD4" => '&#3636;',
        "\xD5" => '&#3637;',
        "\xD6" => '&#3638;',
        "\xD7" => '&#3639;',
        "\xD8" => '&#3640;',
        "\xD9" => '&#3641;',
        "\xDA" => '&#3642;',
        "\xDB" => '&#8288;',
        "\xDC" => '&#8203;',
        "\xDD" => '&#8211;',
        "\xDE" => '&#8212;',
        "\xDF" => '&#3647;',
        "\xE0" => '&#3648;',
        "\xE1" => '&#3649;',
        "\xE2" => '&#3650;',
        "\xE3" => '&#3651;',
        "\xE4" => '&#3652;',
        "\xE5" => '&#3653;',
        "\xE6" => '&#3654;',
        "\xE7" => '&#3655;',
        "\xE8" => '&#3656;',
        "\xE9" => '&#3657;',
        "\xEA" => '&#3658;',
        "\xEB" => '&#3659;',
        "\xEC" => '&#3660;',
        "\xED" => '&#3661;',
        "\xEE" => '&#8482;',
        "\xEF" => '&#3663;',
        "\xF0" => '&#3664;',
        "\xF1" => '&#3665;',
        "\xF2" => '&#3666;',
        "\xF3" => '&#3667;',
        "\xF4" => '&#3668;',
        "\xF5" => '&#3669;',
        "\xF6" => '&#3670;',
        "\xF7" => '&#3671;',
        "\xF8" => '&#3672;',
        "\xF9" => '&#3673;',
        "\xFA" => '&#174;',
        "\xFB" => '&#169;');

    $string = str_replace(array_keys($mac_thai), array_values($mac_thai), $string);

    return $string;
}

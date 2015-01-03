<?php
/**
 * decode/cp874.php
 *
 * Copyright (c) 2005-2015 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file contains cp874 decoding function that is needed to read
 * cp874 encoded mails in non-cp874 locale.
 * 
 * Original data taken from:
 *  ftp://ftp.unicode.org/Public/MAPPINGS/VENDORS/MICSFT/
 *
 *   Name:     cp874 to Unicode table
 *    Unicode version: 2.0
 *    Table version: 2.01
 *    Table format:  Format A
 *    Date:          02/28/98
 *
 *    Contact:       cpxlate@microsoft.com
 * 
 * @version $Id$
 * @package decode
 * @subpackage eastasia
 */

/**
 * Decode a cp874-encoded string
 * @param string $string Encoded string
 * @return string $string Decoded string
 */
function charset_decode_cp874 ($string) {
    // don't do decoding when there are no 8bit symbols
    if (! sq_is8bit($string,'cp874'))
        return $string;

    $cp874 = array(
        "\x80" => '&#8364;',
        "\x81" => '?',
        "\x82" => '?',
        "\x83" => '?',
        "\x84" => '?',
        "\x85" => '&#8230;',
        "\x86" => '?',
        "\x87" => '?',
        "\x88" => '?',
        "\x89" => '?',
        "\x8A" => '?',
        "\x8B" => '?',
        "\x8C" => '?',
        "\x8D" => '?',
        "\x8E" => '?',
        "\x8F" => '?',
        "\x90" => '?',
        "\x91" => '&#8216;',
        "\x92" => '&#8217;',
        "\x93" => '&#8220;',
        "\x94" => '&#8221;',
        "\x95" => '&#8226;',
        "\x96" => '&#8211;',
        "\x97" => '&#8212;',
        "\x98" => '?',
        "\x99" => '?',
        "\x9A" => '?',
        "\x9B" => '?',
        "\x9C" => '?',
        "\x9D" => '?',
        "\x9E" => '?',
        "\x9F" => '?',
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
        "\xDB" => '?',
        "\xDC" => '?',
        "\xDD" => '?',
        "\xDE" => '?',
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
        "\xEE" => '&#3662;',
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
        "\xFA" => '&#3674;',
        "\xFB" => '&#3675;',
        "\xFC" => '?',
        "\xFD" => '?',
        "\xFE" => '?',
        "\xFF" => '?'
        );

    $string = str_replace(array_keys($cp874), array_values($cp874), $string);

    return $string;
}

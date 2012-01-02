<?php
/**
 * functions/decode/x_mac_devanagari.php
 * $Id$
 *
 * Copyright (c) 2003-2012 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * Original data taken from:
 *  ftp://ftp.unicode.org/Public/MAPPINGS/VENDORS/APPLE/DEVANAGE.TXT
 * 
 * Contents:
 * Map (external version) from Mac OS Devanagari
 * encoding to Unicode 2.1 through Unicode 3.2.
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
 * Decode x-mac-devanagari string
 * @param string $string String to decode
 * @return string $string Html formated string
 */
function charset_decode_x_mac_devanagari ($string) {
    // don't do decoding when there are no 8bit symbols
    if (! sq_is8bit($string,'x-mac-devanagari'))
        return $string;

    // Zero joint characters
    $string=str_replace("\xA1\xE9",'&#2384;',$string);
    $string=str_replace("\xA6\xE9",'&#2316;',$string);
    $string=str_replace("\xA7\xE9",'&#2401;',$string);
    $string=str_replace("\xAA\xE9",'&#2400;',$string);
    $string=str_replace("\xDB\xE9",'&#2402;',$string);
    $string=str_replace("\xDC\xE9",'&#2403;',$string);
    $string=str_replace("\xDF\xE9",'&#2372;',$string);
    $string=str_replace("\xE8\xE8",'&#2381;&#8204;',$string);
    $string=str_replace("\xE8\xE9",'&#2381;&#8205;',$string);
    $string=str_replace("\xEA\xE9",'&#2365;',$string);

    // Main replace array
    $mac_devanagari = array(
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
        "\x91" => '&#2416;',
        "\xA1" => '&#2305;',
        "\xA2" => '&#2306;',
        "\xA3" => '&#2307;',
        "\xA4" => '&#2309;',
        "\xA5" => '&#2310;',
        "\xA6" => '&#2311;',
        "\xA7" => '&#2312;',
        "\xA8" => '&#2313;',
        "\xA9" => '&#2314;',
        "\xAA" => '&#2315;',
        "\xAB" => '&#2318;',
        "\xAC" => '&#2319;',
        "\xAD" => '&#2320;',
        "\xAE" => '&#2317;',
        "\xAF" => '&#2322;',
        "\xB0" => '&#2323;',
        "\xB1" => '&#2324;',
        "\xB2" => '&#2321;',
        "\xB3" => '&#2325;',
        "\xB4" => '&#2326;',
        "\xB5" => '&#2327;',
        "\xB6" => '&#2328;',
        "\xB7" => '&#2329;',
        "\xB8" => '&#2330;',
        "\xB9" => '&#2331;',
        "\xBA" => '&#2332;',
        "\xBB" => '&#2333;',
        "\xBC" => '&#2334;',
        "\xBD" => '&#2335;',
        "\xBE" => '&#2336;',
        "\xBF" => '&#2337;',
        "\xC0" => '&#2338;',
        "\xC1" => '&#2339;',
        "\xC2" => '&#2340;',
        "\xC3" => '&#2341;',
        "\xC4" => '&#2342;',
        "\xC5" => '&#2343;',
        "\xC6" => '&#2344;',
        "\xC7" => '&#2345;',
        "\xC8" => '&#2346;',
        "\xC9" => '&#2347;',
        "\xCA" => '&#2348;',
        "\xCB" => '&#2349;',
        "\xCC" => '&#2350;',
        "\xCD" => '&#2351;',
        "\xCE" => '&#2399;',
        "\xCF" => '&#2352;',
        "\xD0" => '&#2353;',
        "\xD1" => '&#2354;',
        "\xD2" => '&#2355;',
        "\xD3" => '&#2356;',
        "\xD4" => '&#2357;',
        "\xD5" => '&#2358;',
        "\xD6" => '&#2359;',
        "\xD7" => '&#2360;',
        "\xD8" => '&#2361;',
        "\xD9" => '&#8206;',
        "\xDA" => '&#2366;',
        "\xDB" => '&#2367;',
        "\xDC" => '&#2368;',
        "\xDD" => '&#2369;',
        "\xDE" => '&#2370;',
        "\xDF" => '&#2371;',
        "\xE0" => '&#2374;',
        "\xE1" => '&#2375;',
        "\xE2" => '&#2376;',
        "\xE3" => '&#2373;',
        "\xE4" => '&#2378;',
        "\xE5" => '&#2379;',
        "\xE6" => '&#2380;',
        "\xE7" => '&#2377;',
        "\xE8" => '&#2381;',
        "\xE9" => '&#2364;',
        "\xEA" => '&#2404;',
        "\xF1" => '&#2406;',
        "\xF2" => '&#2407;',
        "\xF3" => '&#2408;',
        "\xF4" => '&#2409;',
        "\xF5" => '&#2410;',
        "\xF6" => '&#2411;',
        "\xF7" => '&#2412;',
        "\xF8" => '&#2413;',
        "\xF9" => '&#2414;',
        "\xFA" => '&#2415;');

    $string = str_replace(array_keys($mac_devanagari), array_values($mac_devanagari), $string);

    return $string;
}


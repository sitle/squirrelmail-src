<?php
/**
 * functions/decode/x-mac-hebrew.php
 * $Id$
 *
 * Copyright (c) 2003-2014 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * Original data taken from:
 *  ftp://ftp.unicode.org/Public/MAPPINGS/VENDORS/APPLE/HEBREW.TXT
 * 
 * Contents:
 * Map (external version) from Mac OS Hebrew
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
 * Decode x-mac-hebrew string
 * @param string $string String to decode
 * @return string $string Html formated string
 */
function charset_decode_x_mac_hebrew ($string) {
    // don't do decoding when there are no 8bit symbols
    if (! sq_is8bit($string,'x-mac-hebrew'))
        return $string;

    $mac_hebrew = array(
        "\x80" => '&#196;',
        "\x81" => '&#1522;&#1463;',
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
        "\xA0" => '&#32;', //<RL>+
        "\xA1" => '&#33;', //<RL>+
        "\xA2" => '&#34;', //<RL>+
        "\xA3" => '&#35;', //<RL>+
        "\xA4" => '&#36;', //<RL>+
        "\xA5" => '&#37;', //<RL>+
        "\xA6" => '&#8362;',
        "\xA7" => '&#39;', //<RL>+
        "\xA8" => '&#41;', //<RL>+
        "\xA9" => '&#40;', //<RL>+
        "\xAA" => '&#42;', //<RL>+
        "\xAB" => '&#43;', //<RL>+
        "\xAC" => '&#44;', //<RL>+
        "\xAD" => '&#45;', //<RL>+
        "\xAE" => '&#46;', //<RL>+
        "\xAF" => '&#47;', //<RL>+
        "\xB0" => '&#48;', //<RL>+
        "\xB1" => '&#49;', //<RL>+
        "\xB2" => '&#50;', //<RL>+
        "\xB3" => '&#51;', //<RL>+
        "\xB4" => '&#52;', //<RL>+
        "\xB5" => '&#53;', //<RL>+
        "\xB6" => '&#54;', //<RL>+
        "\xB7" => '&#55;', //<RL>+
        "\xB8" => '&#56;', //<RL>+
        "\xB9" => '&#57;', //<RL>+
        "\xBA" => '&#58;', //<RL>+
        "\xBB" => '&#59;', //<RL>+
        "\xBC" => '&#60;', //<RL>+
        "\xBD" => '&#61;', //<RL>+
        "\xBE" => '&#62;', //<RL>+
        "\xBF" => '&#63;', //<RL>+
        "\xC0" => '&#63594;&#1500;&#1465;',
        "\xC1" => '&#8222;', //<RL>+
        "\xC2" => '&#63643;',
        "\xC3" => '&#63644;',
        "\xC4" => '&#63645;',
        "\xC5" => '&#63646;',
        "\xC6" => '&#1468;',
        "\xC7" => '&#64331;',
        "\xC8" => '&#64309;',
        "\xC9" => '&#8230;', //<RL>+
        "\xCA" => '&#160;', //<RL>+
        "\xCB" => '&#1464;',
        "\xCC" => '&#1463;',
        "\xCD" => '&#1461;',
        "\xCE" => '&#1462;',
        "\xCF" => '&#1460;',
        "\xD0" => '&#8211;', //<RL>+
        "\xD1" => '&#8212;', //<RL>+
        "\xD2" => '&#8220;', //<RL>+
        "\xD3" => '&#8221;', //<RL>+
        "\xD4" => '&#8216;', //<RL>+
        "\xD5" => '&#8217;', //<RL>+
        "\xD6" => '&#64298;',
        "\xD7" => '&#64299;',
        "\xD8" => '&#1471;',
        "\xD9" => '&#1456;',
        "\xDA" => '&#1458;',
        "\xDB" => '&#1457;',
        "\xDC" => '&#1467;',
        "\xDD" => '&#1465;',
        "\xDE" => '&#1464;&#63615;',
        "\xDF" => '&#1459;',
        "\xE0" => '&#1488;',
        "\xE1" => '&#1489;',
        "\xE2" => '&#1490;',
        "\xE3" => '&#1491;',
        "\xE4" => '&#1492;',
        "\xE5" => '&#1493;',
        "\xE6" => '&#1494;',
        "\xE7" => '&#1495;',
        "\xE8" => '&#1496;',
        "\xE9" => '&#1497;',
        "\xEA" => '&#1498;',
        "\xEB" => '&#1499;',
        "\xEC" => '&#1500;',
        "\xED" => '&#1501;',
        "\xEE" => '&#1502;',
        "\xEF" => '&#1503;',
        "\xF0" => '&#1504;',
        "\xF1" => '&#1505;',
        "\xF2" => '&#1506;',
        "\xF3" => '&#1507;',
        "\xF4" => '&#1508;',
        "\xF5" => '&#1509;',
        "\xF6" => '&#1510;',
        "\xF7" => '&#1511;',
        "\xF8" => '&#1512;',
        "\xF9" => '&#1513;',
        "\xFA" => '&#1514;',
        "\xFB" => '&#125;', //<RL>+
        "\xFC" => '&#93;', //<RL>+
        "\xFD" => '&#123;', //<RL>+
        "\xFE" => '&#91;', //<RL>+
        "\xFF" => '&#124;' //<RL>+
);

    $string = str_replace(array_keys($mac_hebrew), array_values($mac_hebrew), $string);

    return $string;
}

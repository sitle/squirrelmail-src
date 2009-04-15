<?php
/**
 * functions/decode/x-mac-farsi.php
 * $Id$
 *
 * Copyright (c) 2003-2009 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * Original data taken from:
 *  ftp://ftp.unicode.org/Public/MAPPINGS/VENDORS/APPLE/CENTEURO.TXT
 * 
 * Contents:
 * Map (external version) from Mac OS Farsi
 *  character set to Unicode 2.1 through Unicode 3.2.
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
 * Decode x-mac-farsi string
 * @param string $string String to decode
 * @return string $string Html formated string
 */
function charset_decode_x_mac_farsi ($string) {
    // don't do decoding when there are no 8bit symbols
    if (! sq_is8bit($string,'x-mac-farsi'))
        return $string;

    $mac_farsi = array(
        "\x80" => '&#196;',
        "\x81" => '&#160;', //<RL>+
        "\x82" => '&#199;',
        "\x83" => '&#201;',
        "\x84" => '&#209;',
        "\x85" => '&#214;',
        "\x86" => '&#220;',
        "\x87" => '&#225;',
        "\x88" => '&#224;',
        "\x89" => '&#226;',
        "\x8A" => '&#228;',
        "\x8B" => '&#1722;',
        "\x8C" => '&#171;', //<RL>+
        "\x8D" => '&#231;',
        "\x8E" => '&#233;',
        "\x8F" => '&#232;',
        "\x90" => '&#234;',
        "\x91" => '&#235;',
        "\x92" => '&#237;',
        "\x93" => '&#8230;', //<RL>+
        "\x94" => '&#238;',
        "\x95" => '&#239;',
        "\x96" => '&#241;',
        "\x97" => '&#243;',
        "\x98" => '&#187;', //<RL>+
        "\x99" => '&#244;',
        "\x9A" => '&#246;',
        "\x9B" => '&#247;', //<RL>+
        "\x9C" => '&#250;',
        "\x9D" => '&#249;',
        "\x9E" => '&#251;',
        "\x9F" => '&#252;',
        "\xA0" => '&#32;', //<RL>+
        "\xA1" => '&#33;', //<RL>+
        "\xA2" => '&#34;', //<RL>+
        "\xA3" => '&#35;', //<RL>+
        "\xA4" => '&#36;', //<RL>+
        "\xA5" => '&#1642;',
        "\xA6" => '&#38;', //<RL>+
        "\xA7" => '&#39;', //<RL>+
        "\xA8" => '&#40;', //<RL>+
        "\xA9" => '&#41;', //<RL>+
        "\xAA" => '&#42;', //<RL>+
        "\xAB" => '&#43;', //<RL>+
        "\xAC" => '&#1548;',
        "\xAD" => '&#45;', //<RL>+
        "\xAE" => '&#46;', //<RL>+
        "\xAF" => '&#47;', //<RL>+
        "\xB0" => '&#1776;', //<RL>+
        "\xB1" => '&#1777;', //<RL>+
        "\xB2" => '&#1778;', //<RL>+
        "\xB3" => '&#1779;', //<RL>+
        "\xB4" => '&#1780;', //<RL>+
        "\xB5" => '&#1781;', //<RL>+
        "\xB6" => '&#1782;', //<RL>+
        "\xB7" => '&#1783;', //<RL>+
        "\xB8" => '&#1784;', //<RL>+
        "\xB9" => '&#1785;', //<RL>+
        "\xBA" => '&#58;', //<RL>+
        "\xBB" => '&#1563;',
        "\xBC" => '&#60;', //<RL>+
        "\xBD" => '&#61;', //<RL>+
        "\xBE" => '&#62;', //<RL>+
        "\xBF" => '&#1567;',
        "\xC0" => '&#10058;', //<RL>+
        "\xC1" => '&#1569;',
        "\xC2" => '&#1570;',
        "\xC3" => '&#1571;',
        "\xC4" => '&#1572;',
        "\xC5" => '&#1573;',
        "\xC6" => '&#1574;',
        "\xC7" => '&#1575;',
        "\xC8" => '&#1576;',
        "\xC9" => '&#1577;',
        "\xCA" => '&#1578;',
        "\xCB" => '&#1579;',
        "\xCC" => '&#1580;',
        "\xCD" => '&#1581;',
        "\xCE" => '&#1582;',
        "\xCF" => '&#1583;',
        "\xD0" => '&#1584;',
        "\xD1" => '&#1585;',
        "\xD2" => '&#1586;',
        "\xD3" => '&#1587;',
        "\xD4" => '&#1588;',
        "\xD5" => '&#1589;',
        "\xD6" => '&#1590;',
        "\xD7" => '&#1591;',
        "\xD8" => '&#1592;',
        "\xD9" => '&#1593;',
        "\xDA" => '&#1594;',
        "\xDB" => '&#91;', //<RL>+
        "\xDC" => '&#92;', //<RL>+
        "\xDD" => '&#93;', //<RL>+
        "\xDE" => '&#94;', //<RL>+
        "\xDF" => '&#95;', //<RL>+
        "\xE0" => '&#1600;',
        "\xE1" => '&#1601;',
        "\xE2" => '&#1602;',
        "\xE3" => '&#1603;',
        "\xE4" => '&#1604;',
        "\xE5" => '&#1605;',
        "\xE6" => '&#1606;',
        "\xE7" => '&#1607;',
        "\xE8" => '&#1608;',
        "\xE9" => '&#1609;',
        "\xEA" => '&#1610;',
        "\xEB" => '&#1611;',
        "\xEC" => '&#1612;',
        "\xED" => '&#1613;',
        "\xEE" => '&#1614;',
        "\xEF" => '&#1615;',
        "\xF0" => '&#1616;',
        "\xF1" => '&#1617;',
        "\xF2" => '&#1618;',
        "\xF3" => '&#1662;',
        "\xF4" => '&#1657;',
        "\xF5" => '&#1670;',
        "\xF6" => '&#1749;',
        "\xF7" => '&#1700;',
        "\xF8" => '&#1711;',
        "\xF9" => '&#1672;',
        "\xFA" => '&#1681;',
        "\xFB" => '&#123;', //<RL>+
        "\xFC" => '&#124;', //<RL>+
        "\xFD" => '&#125;', //<RL>+
        "\xFE" => '&#1688;',
        "\xFF" => '&#1746;');

    $string = str_replace(array_keys($mac_farsi), array_values($mac_farsi), $string);

    return $string;
}


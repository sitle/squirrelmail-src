<?php

/**
 * iso_8859_1.inc
 *
 * Copyright (c) 1999-2002 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file contains functions that are needed to do decode iso_8859_1 
 * charsets for SquirrelMail.
 *
 * $Id$
 */


/*
 iso-8859-1 is the same as Latin 1 and is normally used
 in western europe.
 */
function charset_decode_iso_8859_1 ($string, $default_charset) {
    if (strtolower($default_charset) <> 'iso-8859-1') {
        /* Only do the slow convert if there are 8-bit characters */
        if (ereg("[\200-\377]", $string)) {
            $string = str_replace("\201", '&#129;', $string);
            $string = str_replace("\202", '&#130;', $string);
            $string = str_replace("\203", '&#131;', $string);
            $string = str_replace("\204", '&#132;', $string);
            $string = str_replace("\205", '&#133;', $string);
            $string = str_replace("\206", '&#134;', $string);
            $string = str_replace("\207", '&#135;', $string);
            $string = str_replace("\210", '&#136;', $string);
            $string = str_replace("\211", '&#137;', $string);
            $string = str_replace("\212", '&#138;', $string);
            $string = str_replace("\213", '&#139;', $string);
            $string = str_replace("\214", '&#140;', $string);
            $string = str_replace("\215", '&#141;', $string);
            $string = str_replace("\216", '&#142;', $string);
            $string = str_replace("\217", '&#143;', $string);
            $string = str_replace("\220", '&#144;', $string);
            $string = str_replace("\221", '&#145;', $string);
            $string = str_replace("\222", '&#146;', $string);
            $string = str_replace("\223", '&#147;', $string);
            $string = str_replace("\224", '&#148;', $string);
            $string = str_replace("\225", '&#149;', $string);
            $string = str_replace("\226", '&#150;', $string);
            $string = str_replace("\227", '&#151;', $string);
            $string = str_replace("\230", '&#152;', $string);
            $string = str_replace("\231", '&#153;', $string);
            $string = str_replace("\232", '&#154;', $string);
            $string = str_replace("\233", '&#155;', $string);
            $string = str_replace("\234", '&#156;', $string);
            $string = str_replace("\235", '&#157;', $string);
            $string = str_replace("\236", '&#158;', $string);
            $string = str_replace("\237", '&#159;', $string);
            $string = str_replace("\240", '&#160;', $string);
            $string = str_replace("\241", '&#161;', $string);
            $string = str_replace("\242", '&#162;', $string);
            $string = str_replace("\243", '&#163;', $string);
            $string = str_replace("\244", '&#164;', $string);
            $string = str_replace("\245", '&#165;', $string);
            $string = str_replace("\246", '&#166;', $string);
            $string = str_replace("\247", '&#167;', $string);
            $string = str_replace("\250", '&#168;', $string);
            $string = str_replace("\251", '&#169;', $string);
            $string = str_replace("\252", '&#170;', $string);
            $string = str_replace("\253", '&#171;', $string);
            $string = str_replace("\254", '&#172;', $string);
            $string = str_replace("\255", '&#173;', $string);
            $string = str_replace("\256", '&#174;', $string);
            $string = str_replace("\257", '&#175;', $string);
            $string = str_replace("\260", '&#176;', $string);
            $string = str_replace("\261", '&#177;', $string);
            $string = str_replace("\262", '&#178;', $string);
            $string = str_replace("\263", '&#179;', $string);
            $string = str_replace("\264", '&#180;', $string);
            $string = str_replace("\265", '&#181;', $string);
            $string = str_replace("\266", '&#182;', $string);
            $string = str_replace("\267", '&#183;', $string);
            $string = str_replace("\270", '&#184;', $string);
            $string = str_replace("\271", '&#185;', $string);
            $string = str_replace("\272", '&#186;', $string);
            $string = str_replace("\273", '&#187;', $string);
            $string = str_replace("\274", '&#188;', $string);
            $string = str_replace("\275", '&#189;', $string);
            $string = str_replace("\276", '&#190;', $string);
            $string = str_replace("\277", '&#191;', $string);
            $string = str_replace("\300", '&#192;', $string);
            $string = str_replace("\301", '&#193;', $string);
            $string = str_replace("\302", '&#194;', $string);
            $string = str_replace("\303", '&#195;', $string);
            $string = str_replace("\304", '&#196;', $string);
            $string = str_replace("\305", '&#197;', $string);
            $string = str_replace("\306", '&#198;', $string);
            $string = str_replace("\307", '&#199;', $string);
            $string = str_replace("\310", '&#200;', $string);
            $string = str_replace("\311", '&#201;', $string);
            $string = str_replace("\312", '&#202;', $string);
            $string = str_replace("\313", '&#203;', $string);
            $string = str_replace("\314", '&#204;', $string);
            $string = str_replace("\315", '&#205;', $string);
            $string = str_replace("\316", '&#206;', $string);
            $string = str_replace("\317", '&#207;', $string);
            $string = str_replace("\320", '&#208;', $string);
            $string = str_replace("\321", '&#209;', $string);
            $string = str_replace("\322", '&#210;', $string);
            $string = str_replace("\323", '&#211;', $string);
            $string = str_replace("\324", '&#212;', $string);
            $string = str_replace("\325", '&#213;', $string);
            $string = str_replace("\326", '&#214;', $string);
            $string = str_replace("\327", '&#215;', $string);
            $string = str_replace("\330", '&#216;', $string);
            $string = str_replace("\331", '&#217;', $string);
            $string = str_replace("\332", '&#218;', $string);
            $string = str_replace("\333", '&#219;', $string);
            $string = str_replace("\334", '&#220;', $string);
            $string = str_replace("\335", '&#221;', $string);
            $string = str_replace("\336", '&#222;', $string);
            $string = str_replace("\337", '&#223;', $string);
            $string = str_replace("\340", '&#224;', $string);
            $string = str_replace("\341", '&#225;', $string);
            $string = str_replace("\342", '&#226;', $string);
            $string = str_replace("\343", '&#227;', $string);
            $string = str_replace("\344", '&#228;', $string);
            $string = str_replace("\345", '&#229;', $string);
            $string = str_replace("\346", '&#230;', $string);
            $string = str_replace("\347", '&#231;', $string);
            $string = str_replace("\350", '&#232;', $string);
            $string = str_replace("\351", '&#233;', $string);
            $string = str_replace("\352", '&#234;', $string);
            $string = str_replace("\353", '&#235;', $string);
            $string = str_replace("\354", '&#236;', $string);
            $string = str_replace("\355", '&#237;', $string);
            $string = str_replace("\356", '&#238;', $string);
            $string = str_replace("\357", '&#239;', $string);
            $string = str_replace("\360", '&#240;', $string);
            $string = str_replace("\361", '&#241;', $string);
            $string = str_replace("\362", '&#242;', $string);
            $string = str_replace("\363", '&#243;', $string);
            $string = str_replace("\364", '&#244;', $string);
            $string = str_replace("\365", '&#245;', $string);
            $string = str_replace("\366", '&#246;', $string);
            $string = str_replace("\367", '&#247;', $string);
            $string = str_replace("\370", '&#248;', $string);
            $string = str_replace("\371", '&#249;', $string);
            $string = str_replace("\372", '&#250;', $string);
            $string = str_replace("\373", '&#251;', $string);
            $string = str_replace("\374", '&#252;', $string);
            $string = str_replace("\375", '&#253;', $string);
            $string = str_replace("\376", '&#254;', $string);
            $string = str_replace("\377", '&#255;', $string);
        }
    }
    return ($string);
}
?>

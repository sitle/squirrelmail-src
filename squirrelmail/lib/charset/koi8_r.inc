<?php

/**
 * koi8_r.inc
 *
 * Copyright (c) 1999-2002 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file contains functions that are needed to do decode koi8_r 
 * charsets for SquirrelMail.
 *
 * $Id$
 */

/*
 * KOI8-R is used to encode Russian mail (Cyrrilic). Defined in RFC
 * 1489.
 */
function charset_decode_koi8_r ($string, $default_charset) {
    if ($default_charset == 'koi8-r') {
        return $string;
    }

    /*
     * Convert to Unicode HTML entities.
     * This code is rather ineffective.
     */
    $string = str_replace("\200", '&#9472;', $string);
    $string = str_replace("\201", '&#9474;', $string);
    $string = str_replace("\202", '&#9484;', $string);
    $string = str_replace("\203", '&#9488;', $string);
    $string = str_replace("\204", '&#9492;', $string);
    $string = str_replace("\205", '&#9496;', $string);
    $string = str_replace("\206", '&#9500;', $string);
    $string = str_replace("\207", '&#9508;', $string);
    $string = str_replace("\210", '&#9516;', $string);
    $string = str_replace("\211", '&#9524;', $string);
    $string = str_replace("\212", '&#9532;', $string);
    $string = str_replace("\213", '&#9600;', $string);
    $string = str_replace("\214", '&#9604;', $string);
    $string = str_replace("\215", '&#9608;', $string);
    $string = str_replace("\216", '&#9612;', $string);
    $string = str_replace("\217", '&#9616;', $string);
    $string = str_replace("\220", '&#9617;', $string);
    $string = str_replace("\221", '&#9618;', $string);
    $string = str_replace("\222", '&#9619;', $string);
    $string = str_replace("\223", '&#8992;', $string);
    $string = str_replace("\224", '&#9632;', $string);
    $string = str_replace("\225", '&#8729;', $string);
    $string = str_replace("\226", '&#8730;', $string);
    $string = str_replace("\227", '&#8776;', $string);
    $string = str_replace("\230", '&#8804;', $string);
    $string = str_replace("\231", '&#8805;', $string);
    $string = str_replace("\232", '&#160;', $string);
    $string = str_replace("\233", '&#8993;', $string);
    $string = str_replace("\234", '&#176;', $string);
    $string = str_replace("\235", '&#178;', $string);
    $string = str_replace("\236", '&#183;', $string);
    $string = str_replace("\237", '&#247;', $string);
    $string = str_replace("\240", '&#9552;', $string);
    $string = str_replace("\241", '&#9553;', $string);
    $string = str_replace("\242", '&#9554;', $string);
    $string = str_replace("\243", '&#1105;', $string);
    $string = str_replace("\244", '&#9555;', $string);
    $string = str_replace("\245", '&#9556;', $string);
    $string = str_replace("\246", '&#9557;', $string);
    $string = str_replace("\247", '&#9558;', $string);
    $string = str_replace("\250", '&#9559;', $string);
    $string = str_replace("\251", '&#9560;', $string);
    $string = str_replace("\252", '&#9561;', $string);
    $string = str_replace("\253", '&#9562;', $string);
    $string = str_replace("\254", '&#9563;', $string);
    $string = str_replace("\255", '&#9564;', $string);
    $string = str_replace("\256", '&#9565;', $string);
    $string = str_replace("\257", '&#9566;', $string);
    $string = str_replace("\260", '&#9567;', $string);
    $string = str_replace("\261", '&#9568;', $string);
    $string = str_replace("\262", '&#9569;', $string);
    $string = str_replace("\263", '&#1025;', $string);
    $string = str_replace("\264", '&#9570;', $string);
    $string = str_replace("\265", '&#9571;', $string);
    $string = str_replace("\266", '&#9572;', $string);
    $string = str_replace("\267", '&#9573;', $string);
    $string = str_replace("\270", '&#9574;', $string);
    $string = str_replace("\271", '&#9575;', $string);
    $string = str_replace("\272", '&#9576;', $string);
    $string = str_replace("\273", '&#9577;', $string);
    $string = str_replace("\274", '&#9578;', $string);
    $string = str_replace("\275", '&#9579;', $string);
    $string = str_replace("\276", '&#9580;', $string);
    $string = str_replace("\277", '&#169;', $string);
    $string = str_replace("\300", '&#1102;', $string);
    $string = str_replace("\301", '&#1072;', $string);
    $string = str_replace("\302", '&#1073;', $string);
    $string = str_replace("\303", '&#1094;', $string);
    $string = str_replace("\304", '&#1076;', $string);
    $string = str_replace("\305", '&#1077;', $string);
    $string = str_replace("\306", '&#1092;', $string);
    $string = str_replace("\307", '&#1075;', $string);
    $string = str_replace("\310", '&#1093;', $string);
    $string = str_replace("\311", '&#1080;', $string);
    $string = str_replace("\312", '&#1081;', $string);
    $string = str_replace("\313", '&#1082;', $string);
    $string = str_replace("\314", '&#1083;', $string);
    $string = str_replace("\315", '&#1084;', $string);
    $string = str_replace("\316", '&#1085;', $string);
    $string = str_replace("\317", '&#1086;', $string);
    $string = str_replace("\320", '&#1087;', $string);
    $string = str_replace("\321", '&#1103;', $string);
    $string = str_replace("\322", '&#1088;', $string);
    $string = str_replace("\323", '&#1089;', $string);
    $string = str_replace("\324", '&#1090;', $string);
    $string = str_replace("\325", '&#1091;', $string);
    $string = str_replace("\326", '&#1078;', $string);
    $string = str_replace("\327", '&#1074;', $string);
    $string = str_replace("\330", '&#1100;', $string);
    $string = str_replace("\331", '&#1099;', $string);
    $string = str_replace("\332", '&#1079;', $string);
    $string = str_replace("\333", '&#1096;', $string);
    $string = str_replace("\334", '&#1101;', $string);
    $string = str_replace("\335", '&#1097;', $string);
    $string = str_replace("\336", '&#1095;', $string);
    $string = str_replace("\337", '&#1098;', $string);
    $string = str_replace("\340", '&#1070;', $string);
    $string = str_replace("\341", '&#1040;', $string);
    $string = str_replace("\342", '&#1041;', $string);
    $string = str_replace("\343", '&#1062;', $string);
    $string = str_replace("\344", '&#1044;', $string);
    $string = str_replace("\345", '&#1045;', $string);
    $string = str_replace("\346", '&#1060;', $string);
    $string = str_replace("\347", '&#1043;', $string);
    $string = str_replace("\350", '&#1061;', $string);
    $string = str_replace("\351", '&#1048;', $string);
    $string = str_replace("\352", '&#1049;', $string);
    $string = str_replace("\353", '&#1050;', $string);
    $string = str_replace("\354", '&#1051;', $string);
    $string = str_replace("\355", '&#1052;', $string);
    $string = str_replace("\356", '&#1053;', $string);
    $string = str_replace("\357", '&#1054;', $string);
    $string = str_replace("\360", '&#1055;', $string);
    $string = str_replace("\361", '&#1071;', $string);
    $string = str_replace("\362", '&#1056;', $string);
    $string = str_replace("\363", '&#1057;', $string);
    $string = str_replace("\364", '&#1058;', $string);
    $string = str_replace("\365", '&#1059;', $string);
    $string = str_replace("\366", '&#1046;', $string);
    $string = str_replace("\367", '&#1042;', $string);
    $string = str_replace("\370", '&#1068;', $string);
    $string = str_replace("\371", '&#1067;', $string);
    $string = str_replace("\372", '&#1047;', $string);
    $string = str_replace("\373", '&#1064;', $string);
    $string = str_replace("\374", '&#1069;', $string);
    $string = str_replace("\375", '&#1065;', $string);
    $string = str_replace("\376", '&#1063;', $string);
    $string = str_replace("\377", '&#1066;', $string);

    return $string;
}

?>
<?php

/**
 * i18n.php
 *
 * Copyright (c) 1999-2002 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file contains variuos functions that are needed to do
 * internationalization of SquirrelMail.
 *
 * Internally the output character set is used. Other characters are
 * encoded using Unicode entities according to HTML 4.0.
 *
 * $Id$
 */

/* Decodes a string to the internal encoding from the given charset */
function charset_decode ($charset, $string) {
    global $languages, $squirrelmail_language, $default_charset;

    if (isset($languages[$squirrelmail_language]['XTRA_CODE']) &&
        function_exists($languages[$squirrelmail_language]['XTRA_CODE'])) {
        $string = $languages[$squirrelmail_language]['XTRA_CODE']('decode', $string);
    }

    /* All HTML special characters are 7 bit and can be replaced first */
    $string = htmlspecialchars ($string);
    
    $charset = strtolower($charset);
    $charset = str_replace('-','_',$charset);
    if (file_exists(SM_LIB_PATH .'charset/'. $charset.'.inc')) {
	require_once(SM_LIB_PATH .'charset/'.$charset.'.inc');
	$sFunctionName = 'charset_decode_'.$charset;
	$ret = $sFunctionName($string, $default_charset);
    } else if (ereg('iso-8859-([[:digit:]]+)', $charset, $res)) {
        if (file_exists('iso_8859_default.inc')) {
	    require_once(SM_LIB_PATH .'charset/'.'iso_8859_default.inc');
	    $sFunctionName = 'charset_decode_default';
	    $ret = $sFunctionName($string, $default_charset);
	} else {
	    $ret = $string;
	}
    } else {
	$ret =  $string;
    }
    return $ret;
}

/*
 * Set up the language to be output
 * if $do_search is true, then scan the browser information
 * for a possible language that we know
 */
function set_up_language($sm_language, $do_search = false) {

    static $SetupAlready = 0;
    global $use_gettext, $languages,
           $squirrelmail_language, $squirrelmail_default_language,
           $sm_notAlias;

    if ($SetupAlready) {
        return;
    }
    $SetupAlready = TRUE;

    if ($do_search && ! $sm_language && isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $sm_language = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
    }
    
    if (!$sm_language && isset($squirrelmail_default_language)) {
        $squirrelmail_language = $squirrelmail_default_language;
        $sm_language = $squirrelmail_default_language;
    }
    $sm_notAlias = $sm_language;
    while (isset($languages[$sm_notAlias]['ALIAS'])) {
        $sm_notAlias = $languages[$sm_notAlias]['ALIAS'];
    }

    if ( isset($sm_language) &&
         $use_gettext &&
         $sm_language != '' &&
         isset($languages[$sm_notAlias]['CHARSET']) ) {
        bindtextdomain( 'squirrelmail', '../locale/' );
        textdomain( 'squirrelmail' );
        if ( !ini_get('safe_mode') &&
             getenv( 'LC_ALL' ) != $sm_notAlias ) {
            putenv( "LC_ALL=$sm_notAlias" );
            putenv( "LANG=$sm_notAlias" );
            putenv( "LANGUAGE=$sm_notAlias" );
        }
        setlocale(LC_ALL, $sm_notAlias);
        $squirrelmail_language = $sm_notAlias;
        if ($squirrelmail_language == 'ja_JP' && function_exists('mb_detect_encoding') ) {
            header ('Content-Type: text/html; charset=EUC-JP');
            if (!function_exists('mb_internal_encoding')) {
                echo _("You need to have php4 installed with the multibyte string function enabled (using configure option --with-mbstring).");
            }
            mb_internal_encoding('EUC-JP');
            mb_http_output('pass');
        } else {
        header( 'Content-Type: text/html; charset=' . $languages[$sm_notAlias]['CHARSET'] );
    }
}
}

function set_my_charset(){

    /*
     * There can be a $default_charset setting in the
     * config.php file, but the user may have a different language
     * selected for a user interface. This function checks the
     * language selected by the user and tags the outgoing messages
     * with the appropriate charset corresponding to the language
     * selection. This is "more right" (tm), than just stamping the
     * message blindly with the system-wide $default_charset.
     */
    global $data_dir, $username, $default_charset, $languages;

    $my_language = getPref($data_dir, $username, 'language');
    if (!$my_language) {
        return;
    }
    while (isset($languages[$my_language]['ALIAS'])) {
        $my_language = $languages[$my_language]['ALIAS'];
    }
    $my_charset = $languages[$my_language]['CHARSET'];
    if ($my_charset) {
        $default_charset = $my_charset;
    }
}

/* ------------------------------ main --------------------------- */

global $squirrelmail_language, $languages, $use_gettext;

if (! isset($squirrelmail_language)) {
    $squirrelmail_language = '';
}

/* This array specifies the available languages. */

// The glibc locale is ca_ES.

$languages['ca_ES']['NAME']    = 'Catalan';
$languages['ca_ES']['CHARSET'] = 'iso-8859-1';
$languages['ca']['ALIAS'] = 'ca_ES';

$languages['cs_CZ']['NAME']    = 'Czech';
$languages['cs_CZ']['CHARSET'] = 'iso-8859-2';
$languages['cs']['ALIAS']      = 'cs_CZ';

// Danish locale is da_DK.

$languages['da_DK']['NAME']    = 'Danish';
$languages['da_DK']['CHARSET'] = 'iso-8859-1';
$languages['da']['ALIAS'] = 'da_DK';

$languages['de_DE']['NAME']    = 'Deutsch';
$languages['de_DE']['CHARSET'] = 'iso-8859-1';
$languages['de']['ALIAS'] = 'de_DE';

// There is no en_EN! There is en_US, en_BR, en_AU, and so forth, 
// but who cares about !US, right? Right? :)

$languages['en_US']['NAME']    = 'English';
$languages['en_US']['CHARSET'] = 'iso-8859-1';
$languages['en']['ALIAS'] = 'en_US';

$languages['es_ES']['NAME']    = 'Spanish';
$languages['es_ES']['CHARSET'] = 'iso-8859-1';
$languages['es']['ALIAS'] = 'es_ES';

$languages['et_EE']['NAME']    = 'Estonian';
$languages['et_EE']['CHARSET'] = 'iso-8859-15';
$languages['et']['ALIAS'] = 'et_EE';

$languages['fi_FI']['NAME']    = 'Finnish';
$languages['fi_FI']['CHARSET'] = 'iso-8859-1';
$languages['fi']['ALIAS'] = 'fi_FI';

$languages['fr_FR']['NAME']    = 'French';
$languages['fr_FR']['CHARSET'] = 'iso-8859-1';
$languages['fr']['ALIAS'] = 'fr_FR';

$languages['hr_HR']['NAME']    = 'Croatian';
$languages['hr_HR']['CHARSET'] = 'iso-8859-2';
$languages['hr']['ALIAS'] = 'hr_HR';

$languages['hu_HU']['NAME']    = 'Hungarian';
$languages['hu_HU']['CHARSET'] = 'iso-8859-2';
$languages['hu']['ALIAS'] = 'hu_HU';

$languages['id_ID']['NAME']    = 'Indonesian';
$languages['id_ID']['CHARSET'] = 'iso-8859-1';
$languages['id']['ALIAS'] = 'id_ID';

$languages['is_IS']['NAME']    = 'Icelandic';
$languages['is_IS']['CHARSET'] = 'iso-8859-1';
$languages['is']['ALIAS'] = 'is_IS';

$languages['it_IT']['NAME']    = 'Italian';
$languages['it_IT']['CHARSET'] = 'iso-8859-1';
$languages['it']['ALIAS'] = 'it_IT';

$languages['ja_JP']['NAME']    = 'Japanese';
$languages['ja_JP']['CHARSET'] = 'iso-2022-jp';
$languages['ja_JP']['XTRA_CODE'] = 'japanese_charset_xtra';
$languages['ja']['ALIAS'] = 'ja_JP';

$languages['ko_KR']['NAME']    = 'Korean';
$languages['ko_KR']['CHARSET'] = 'euc-KR';
$languages['ko_KR']['XTRA_CODE'] = 'korean_charset_xtra';
$languages['ko']['ALIAS'] = 'ko_KR';

$languages['nl_NL']['NAME']    = 'Dutch';
$languages['nl_NL']['CHARSET'] = 'iso-8859-1';
$languages['nl']['ALIAS'] = 'nl_NL';

$languages['no_NO']['NAME']    = 'Norwegian (Bokm&aring;l)';
$languages['no_NO']['CHARSET'] = 'iso-8859-1';
$languages['no']['ALIAS'] = 'no_NO';
$languages['nn_NO']['NAME']    = 'Norwegian (Nynorsk)';
$languages['nn_NO']['CHARSET'] = 'iso-8859-1';

$languages['pl_PL']['NAME']    = 'Polish';
$languages['pl_PL']['CHARSET'] = 'iso-8859-2';
$languages['pl']['ALIAS'] = 'pl_PL';

$languages['pt_PT']['NAME'] = 'Portuguese (Portugal)';
$languages['pt_PT']['CHARSET'] = 'iso-8859-1';
$languages['pt_BR']['NAME']    = 'Portuguese (Brazil)';
$languages['pt_BR']['CHARSET'] = 'iso-8859-1';
$languages['pt']['ALIAS'] = 'pt_PT';

$languages['ru_RU']['NAME']    = 'Russian';
$languages['ru_RU']['CHARSET'] = 'koi8-r';
$languages['ru']['ALIAS'] = 'ru_RU';

$languages['sr_YU']['NAME']    = 'Serbian';
$languages['sr_YU']['CHARSET'] = 'iso-8859-2';
$languages['sr']['ALIAS'] = 'sr_YU';

$languages['sv_SE']['NAME']    = 'Swedish';
$languages['sv_SE']['CHARSET'] = 'iso-8859-1';
$languages['sv']['ALIAS'] = 'sv_SE';

$languages['tr_TR']['NAME']    = 'Turkish';
$languages['tr_TR']['CHARSET'] = 'iso-8859-9';
$languages['tr']['ALIAS'] = 'tr_TR';

$languages['zh_TW']['NAME']    = 'Taiwan';
$languages['zh_TW']['CHARSET'] = 'big5';
$languages['tw']['ALIAS'] = 'zh_TW';

$languages['zh_TW']['NAME']    = 'Chinese';
$languages['zh_TW']['CHARSET'] = 'gb2312';
$languages['tw']['ALIAS'] = 'zh_CN';

$languages['sk_SK']['NAME']     = 'Slovak';
$languages['sk_SK']['CHARSET']  = 'iso-8859-2';
$languages['sk']['ALIAS']       = 'sk_SK';

$languages['ro_RO']['NAME']    = 'Romanian';
$languages['ro_RO']['CHARSET'] = 'iso-8859-2';
$languages['ro']['ALIAS'] = 'ro_RO';

$languages['th_TH']['NAME']    = 'Thai';
$languages['th_TH']['CHARSET'] = 'tis-620';
$languages['th']['ALIAS'] = 'th_TH';

$languages['lt_LT']['NAME']    = 'Lithuanian';
$languages['lt_LT']['CHARSET'] = 'iso-8859-13';
$languages['lt']['ALIAS'] = 'lt_LT';

$languages['sl_SI']['NAME']    = 'Slovenian';
$languages['sl_SI']['CHARSET'] = 'iso-8859-2';
$languages['sl']['ALIAS'] = 'sl_SI';

$languages['bg_BG']['NAME']    = 'Bulgarian';
$languages['bg_BG']['CHARSET'] = 'windows-1251';
$languages['bg']['ALIAS'] = 'bg_BG';

// Right to left languages

$languages['he_HE']['NAME']    = 'Hebrew';
$languages['he_HE']['CHARSET'] = 'windows-1255';
$languages['he_HE']['DIR']     = 'rtl';
$languages['he']['ALIAS']      = 'he_HE';


/* Detect whether gettext is installed. */
$gettext_flags = 0;
if (function_exists('_')) {
    $gettext_flags += 1;
}
if (function_exists('bindtextdomain')) {
    $gettext_flags += 2;
}
if (function_exists('textdomain')) {
    $gettext_flags += 4;
}

/* If gettext is fully loaded, cool */
if ($gettext_flags == 7) {
    $use_gettext = true;
}
/* If we can fake gettext, try that */
elseif ($gettext_flags == 0) {
    $use_gettext = true;
    include_once(SM_PATH . 'functions/gettext.php');
} else {
    /* Uh-ho.  A weird install */
    if (! $gettext_flags & 1) {
        function _($str) {
            return $str;
        }
    }
    if (! $gettext_flags & 2) {
        function bindtextdomain() {
            return;
        }
    }
    if (! $gettext_flags & 4) {
        function textdomain() {
            return;
        }
    }
}


/*
 * Japanese charset extra function
 *
 */
function japanese_charset_xtra() {
    $ret = func_get_arg(1);  /* default return value */
    if (function_exists('mb_detect_encoding')) {
        switch (func_get_arg(0)) { /* action */
        case 'decode':
            $detect_encoding = mb_detect_encoding($ret);
            if ($detect_encoding == 'JIS' ||
                $detect_encoding == 'EUC-JP' ||
                $detect_encoding == 'SJIS') {
                
                $ret = mb_convert_encoding($ret, 'EUC-JP', 'AUTO');
            }
            break;
        case 'encode':
            $detect_encoding = mb_detect_encoding($ret);
            if ($detect_encoding == 'JIS' ||
                $detect_encoding == 'EUC-JP' ||
                $detect_encoding == 'SJIS') {
                
                $ret = mb_convert_encoding($ret, 'JIS', 'AUTO');
            }
            break;
        case 'strimwidth':
            $width = func_get_arg(2);
            $ret = mb_strimwidth($ret, 0, $width, '...'); 
            break;
        case 'encodeheader':
            $result = '';
            if (strlen($ret) > 0) {
                $tmpstr = mb_substr($ret, 0, 1);
                $prevcsize = strlen($tmpstr);
                for ($i = 1; $i < mb_strlen($ret); $i++) {
                    $tmp = mb_substr($ret, $i, 1);
                    if (strlen($tmp) == $prevcsize) {
                        $tmpstr .= $tmp;
                    } else {
                        if ($prevcsize == 1) {
                            $result .= $tmpstr;
                        } else {
                            $result .= mb_encode_mimeheader($tmpstr);
                        }
                        $tmpstr = $tmp;
                        $prevcsize = strlen($tmp);
                    }
                }
                if (strlen($tmpstr)) {
                    if (strlen(mb_substr($tmpstr, 0, 1)) == 1)
                        $result .= $tmpstr;
                    else
                        $result .= mb_encode_mimeheader($tmpstr);
                }
            }
            $ret = $result;
            //$ret = mb_encode_mimeheader($ret);
            break;
        case 'decodeheader':
            $ret = str_replace("\t", "", $ret);
            if (eregi('=\\?([^?]+)\\?(q|b)\\?([^?]+)\\?=', $ret))
                $ret = mb_decode_mimeheader($ret);
            $ret = mb_convert_encoding($ret, 'EUC-JP', 'AUTO');
            break;
        case 'downloadfilename':
            $useragent = func_get_arg(2);
            if (strstr($useragent, 'Windows') !== false ||
                strstr($useragent, 'Mac_') !== false) {
                $ret = mb_convert_encoding($ret, 'SJIS', 'AUTO');
            } else {
                $ret = mb_convert_encoding($ret, 'EUC-JP', 'AUTO');
            }
            break;
        }
    }
    return $ret;
}


/*
 * Korean charset extra function
 * Hangul(Korean Character) Attached File Name Fix.
 */
function korean_charset_xtra() {
    
    $ret = func_get_arg(1);  /* default return value */
    if (func_get_arg(0) == 'downloadfilename') { /* action */
        $ret = str_replace("\x0D\x0A", '', $ret);  /* Hanmail's CR/LF Clear */
        for ($i=0;$i<strlen($ret);$i++) {
            if ($ret[$i] >= "\xA1" && $ret[$i] <= "\xFE") {   /* 0xA1 - 0XFE are Valid */
                $i++;
                continue;
            } else if (($ret[$i] >= 'a' && $ret[$i] <= 'z') || /* From Original ereg_replace in download.php */
                       ($ret[$i] >= 'A' && $ret[$i] <= 'Z') ||
                       ($ret[$i] == '.') || ($ret[$i] == '-')) {
                continue;
            } else {
                $ret[$i] = '_';
            }
        }

    }

    return $ret;
}

?>

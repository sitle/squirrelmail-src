<?php
/**
 * decode/euc_kr.php
 *
 * This file contains euc-kr decoding function that is needed to read
 * euc-kr encoded mails in non-euc-kr locale.
 *
 * @copyright (c) 2005 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id$
 * @package decode
 * @subpackage eastasia
 */

/**
 * Decode euc-kr encoded string
 * @param string $string euc-kr string
 * @return string $string decoded string
 */
function charset_decode_euc_kr ($string) {
    // global $aggressive_decoding;

    // don't do decoding when there are no 8bit symbols
    if (! sq_is8bit($string,'euc-kr'))
        return $string;

    // this is CPU intensive task. Use recode functions if they are available.
    if (function_exists('recode_string')) {
        $string=str_replace(array('&amp;','&quot;','&lt;','&gt;'),array('&','"','<','>'),$string);
        return recode_string("euc-kr..html",$string);
    }

    /*
     * iconv does not support html target, but internal utf-8 decoding is faster 
     * than pure php implementation. 
     */
    if (function_exists('iconv') && file_exists(SM_PATH . 'functions/decode/utf_8.php') ) {
        include_once(SM_PATH . 'functions/decode/utf_8.php');
        $string = iconv('euc-kr','utf-8',$string);
        return charset_decode_utf_8($string);
    }

    // try mbstring
    if (function_exists('mb_convert_encoding') && 
        function_exists('sq_mb_list_encodings') &&
        check_php_version(4,3,0) &&
        in_array('euc-kr',sq_mb_list_encodings())) {
        return mb_convert_encoding($string,'HTML-ENTITIES','EUC-KR');
    }

    return $string;
}
?>
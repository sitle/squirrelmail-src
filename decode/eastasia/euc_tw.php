<?php
/**
 * SquirrelMail EUC-TW decoding functions
 *
 * This file contains euc-tw decoding function that is needed to read
 * euc-tw encoded mails in non-euc-tw locale.
 *
 * @copyright (c) 2005-2013 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id$
 * @package decode
 * @subpackage eastasia
 */

/**
 * Decode euc-tw encoded string
 * @param string $string euc-tw string
 * @param boolean $save_html don't html encode special characters if true
 * @return string $string decoded string
 */
function charset_decode_euc_tw ($string,$save_html=false) {
    // global $aggressive_decoding;

    // don't do decoding when there are no 8bit symbols
    if (! sq_is8bit($string,'euc-tw'))
        return $string;

    // this is CPU intensive task. Use recode functions if they are available.
    if (function_exists('recode_string')) {
        // if string is already sanitized, undo htmlspecial chars
        if (! $save_html) {
            $string=str_replace(array('&quot;','&lt;','&gt;','&amp;'),array('"','<','>','&'),$string);
        }
        $string = recode_string("euc-tw..html",$string);

        // if string sanitizing is not needed, undo htmlspecialchars applied by recode.
        if ($save_html) {
            $string=str_replace(array('&quot;','&lt;','&gt;','&amp;'),array('"','<','>','&'),$string);
        }
        return $string;
    }

    /**
     * iconv does not support html target, but internal utf-8 decoding is 
     * faster than pure php implementation.
     */
    if (function_exists('iconv') && file_exists(SM_PATH . 'functions/decode/utf_8.php') ) {
        include_once(SM_PATH . 'functions/decode/utf_8.php');
        $string = iconv('euc-tw','utf-8',$string);
        return charset_decode_utf_8($string);
    }

    // try mbstring
    if (function_exists('mb_convert_encoding') && 
        function_exists('sq_mb_list_encodings') &&
        check_php_version(4,3,0) &&
        in_array('euc-tw',sq_mb_list_encodings())) {
        return mb_convert_encoding($string,'HTML-ENTITIES','EUC-TW');
    }

    // pure php decoding is not implemented.
    return $string;
}

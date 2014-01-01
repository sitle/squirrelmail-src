<?php
/**
 * SquirrelMail Shift_JIS decoding functions
 *
 * This file contains shift_jis decoding function that is needed to read
 * shift_jis encoded mails in non-shift_jis locale.
 *
 * @copyright (c) 2005-2014 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id$
 * @package decode
 * @subpackage eastasia
 */

/**
 * Decode shift_jis encoded string
 * @param string $string shift_jis string
 * @param boolean $save_html don't html encode special characters if true
 * @return string $string decoded string
 */
function charset_decode_shift_jis ($string,$save_html=false) {
    global $squirrelmail_language;
    // global $aggressive_decoding;

    // ja_JP uses own functions
    if ($squirrelmail_language=='ja_JP')
        return $string;

    // don't do decoding when there are no 8bit symbols
    if (! sq_is8bit($string,'shift_jis'))
        return $string;

    // this is CPU intensive task. Use recode functions if they are available.
    if (function_exists('recode_string')) {
        // if string is already sanitized, undo htmlspecial chars
        if (! $save_html) {
            $string=str_replace(array('&quot;','&lt;','&gt;','&amp;'),array('"','<','>','&'),$string);
        }
        $string = recode_string("shift_jis..html",$string);

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
        $string = iconv('shift_jis','utf-8',$string);
        return charset_decode_utf_8($string);
    }

    // try mbstring
    if (function_exists('mb_convert_encoding') && 
        function_exists('sq_mb_list_encodings') &&
        check_php_version(4,3,0) &&
        in_array('sjis',sq_mb_list_encodings())) {
        return mb_convert_encoding($string,'HTML-ENTITIES','SJIS');
    }

    // pure php decoding is not implemented.
    return $string;
}

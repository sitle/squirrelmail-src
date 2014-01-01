<?php
/**
 * SquirrelMail GB18030 decoding functions
 *
 * This file contains gb18030 decoding function that is needed to read
 * gb18030 encoded mails in non-gb18030 locale.
 *
 * @copyright (c) 2005-2014 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id$
 * @package decode
 * @subpackage eastasia
 */

/**
 * Decode gb18030 encoded string
 * @param string $string gb18030 string
 * @param boolean $save_html don't html encode special characters if true
 * @return string $string decoded string
 */
function charset_decode_gb18030 ($string, $save_html=false) {
    // global $aggressive_decoding;

    // don't do decoding when there are no 8bit symbols
    if (! sq_is8bit($string,'gb18030'))
        return $string;

    // this is CPU intensive task. Use recode functions if they are available.
    if (function_exists('recode_string')) {
        // if string is already sanitized, undo htmlspecial chars
        if (! $save_html) {
            $string=str_replace(array('&quot;','&lt;','&gt;','&amp;'),array('"','<','>','&'),$string);
        }
        $string = recode_string("gb18030..html",$string);
        // if string sanitizing is not needed, undo htmlspecialchars applied by recode.
        if ($save_html) {
            $string=str_replace(array('&quot;','&lt;','&gt;','&amp;'),array('"','<','>','&'),$string);
        }
        return $string;
    }

    /*
     * iconv does not support html target, but internal utf-8 decoding is faster 
     * than pure php implementation. 
     */
    if (function_exists('iconv') && file_exists(SM_PATH . 'functions/decode/utf_8.php') ) {
        include_once(SM_PATH . 'functions/decode/utf_8.php');
        $string = iconv('gb18030','utf-8',$string);
        return charset_decode_utf_8($string);
    }

    // mbstring does not support gb18030

    // pure php decoding is not implemented.
    return $string;
}

<?php
/**
 * iso-2022-jp decoding functions
 *
 * This script provides iso-2022-jp (rfc1468) decoding functions.
 *
 * @copyright (c) 2004-2015 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id$
 * @package decode
 * @subpackage eastasia
 */

/**
 * Converts iso-2022-jp texts
 * @param string $string iso-2022-jp encoded string
 * @param boolean $save_html don't html encode special characters if true
 * @return string html encoded text
 */
function charset_decode_iso_2022_jp ($string, $save_html=false) {
    global $squirrelmail_language, $aggressive_decoding;

    // ja_JP uses own functions
    if ($squirrelmail_language=='ja_JP')
        return $string;

    // undo htmlspecial chars (they can break iso-2022-jp)
    if (! $save_html) {
        $string=str_replace(array('&quot;','&lt;','&gt;','&amp;'),array('"','<','>','&'),$string);
    }
    // recode
    // this is CPU intensive task. Use recode functions if they are available. 
    if (function_exists('recode_string')) {
        // recode includes htmlspecialchars sanitizing
        $string = recode_string("iso-2022-jp..html",$string);

        // if string sanitizing is not needed, undo htmlspecialchars applied by recode.
        if ($save_html) {
            $string=str_replace(array('&quot;','&lt;','&gt;','&amp;'),array('"','<','>','&'),$string);
        }
        return $string;
    }

    // iconv does not support html target, but internal utf-8 decoding is faster than iso-2022-jp. 
    if (function_exists('iconv') && file_exists(SM_PATH . 'functions/decode/utf_8.php') ) {
        include_once(SM_PATH . 'functions/decode/utf_8.php');
        $string = iconv('iso-2022-jp','utf-8',$string);
        // redo htmlspecial chars
        if (! $save_html) $string = htmlspecialchars($string);
        return charset_decode_utf_8($string);
    }

    // aggressive decoding disabled
    if (! isset($aggressive_decoding) || ! $aggressive_decoding )
        return htmlspecialchars($string);

    /**
     * Include common iso-2022-xx functions
     */
    include_once(SM_PATH . 'functions/decode/iso_2022_support.php');

    $index=0;
    $ret='';
    $enc_table='ascii';

    while ( $index < strlen($string)) {
        if (isset($string[$index+2]) && $string[$index]=="\x1B") {
            // table change
            switch ($string[$index].$string[$index+1].$string[$index+2]) {
            case "\x1B\x28\x42":
                $enc_table='ascii';
                $index=$index+3;
                break;
            case "\x1B\x24\x42":
                $enc_table='jis0208-1983';
                $index=$index+3;
                break;
            case "\x1B\x28\x4A":
                $enc_table='jis0201-1976';
                $index=$index+3;
                break;
            case "\x1B\x24\x40":
                $enc_table='jis0208-1978';
                $index=$index+3;
                break;
            default:
                return _("Unsupported ESC sequence.");
            }
        }

        $ret .= get_iso_2022_symbol($string,$index,$enc_table);
        $index=$index+get_iso_2022_symbolsize($enc_table);
    }
    return $ret;
}

<?php
/**
 * iso-2022-jp decoding functions
 *
 * This script provides iso-2022-jp (rfc1468) decoding functions.
 *
 * @copyright Copyright &copy; 2004-2005 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id$
 * @package decode
 * @subpackage eastasia
 */

/** @ignore */
if (! defined('SM_PATH')) define('SM_PATH','../../');

/**
 * Include common iso-2022-xx functions
 */
include_once(SM_PATH . 'functions/decode/iso_2022_support.php');

/**
 *
 */
function charset_decode_iso_2022_jp ($string) {
    global $squirrelmail_language;

    // ja_JP uses own functions
    if ($squirrelmail_language=='ja_JP')
        return $string;

    // recode
    // this is CPU intensive task. Use recode functions if they are available. 
    if (function_exists('recode_string')) {
        $string=str_replace(array('&amp;','&quot;','&lt;','&gt;'),array('&','"','<','>'),$string);
        return recode_string("iso-2022-jp..html",$string);
    }

    // iconv does not support html target, but internal utf-8 decoding is faster than iso-2022-jp. 
    if (function_exists('iconv') && file_exists(SM_PATH . 'functions/decode/utf_8.php') ) {
        include_once(SM_PATH . 'functions/decode/utf_8.php');
        // undo htmlspecial chars (they can break iso-2022-jp)
        $string = str_replace(array('&amp;','&quot;','&lt;','&gt;'),array('&','"','<','>'),$string);
        $string = iconv('iso-2022-jp','utf-8',$string);
        // redo htmlspecial chars
        $string = htmlspecialchars($string);
        return charset_decode_utf_8($string);
    }

    // try mbstring
    // TODO: check sanitizing of html special chars.
    if (function_exists('mbstring_convert_encoding') && 
        check_php_version(4,3,0) &&
        in_array('iso-2022-jp',sq_mb_list_encodings())) {
        return mbstring_convert_encoding($string,'HTML-ENTITIES','ISO-2022-JP');
    }

    // aggressive decoding disabled
    if (! isset($aggressive_decoding) || 
        ! $aggressive_decoding )
        return $string;

    $index=0;
    $ret='';
    $enc_table='ascii';

    // remove html tags (non-japanese charsets are still sanitized or 
    // don't include html tags)
    $string=str_replace(array('&amp;','&quot;','&lt;','&gt;'),array('&','"','<','>'),$string);
  
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
?>
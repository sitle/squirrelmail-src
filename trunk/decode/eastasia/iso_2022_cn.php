<?php
/**
 * iso-2022-cn decoding functions
 *
 * This script provides iso-2022-cn (rfc1922) decoding functions.
 *
 * @copyright (c) 2004-2005 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id$
 * @package decode
 * @subpackage eastasia
 */

/**
 * Converts iso-2022-cn texts
 * @param string $string iso-2022-cn encoded string
 * @return string html encoded text
 */
function charset_decode_iso_2022_cn ($string) {
    global $aggressive_decoding;

    // undo htmlspecial chars (they can break iso-2022-cn)
    $string=str_replace(array('&amp;','&quot;','&lt;','&gt;'),array('&','"','<','>'),$string);

    // recode
    // this is CPU intensive task. Use recode functions if they are available. 
    if (function_exists('recode_string')) {
        // recode includes htmlspecialchars sanitizing
        return recode_string("iso-2022-cn..html",$string);
    }

    // iconv does not support html target, but internal utf-8 decoding is faster than iso-2022-cn.
    if (function_exists('iconv') && file_exists(SM_PATH . 'functions/decode/utf_8.php') ) {
        include_once(SM_PATH . 'functions/decode/utf_8.php');
        $string = iconv('iso-2022-cn','utf-8',$string);
        // redo htmlspecial chars
        $string = htmlspecialchars($string);
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
                return 'Unsupported ESC sequence.';
            }
        }

        $ret .= get_iso_2022_symbol($string,$index,$enc_table);
        $index=$index+get_iso_2022_symbolsize($enc_table);
    }
    return $ret;
}
?>
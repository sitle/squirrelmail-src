<?php
/**
 * iso-2022-cn decoding functions
 *
 * This script provides iso-2022-cn (rfc1922) decoding functions.
 *
 * @copyright Copyright &copy; 2004 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id$
 * @package squirrelmail
 * @subpackage decode_extra
 */

/**
 * Include common iso-2022-xx functions
 */
include_once(SM_PATH . 'functions/decode/iso_2022_support.php');

/**
 *
 */
function charset_decode_iso_2022_cn ($string) {
    global $squirrelmail_language;

    // recode
    // this is CPU intensive task. Use recode functions if they are available. 
    if (function_exists('recode_string')) {
        $string=str_replace(array('&amp;','&quot;','&lt;','&gt;'),array('&','"','<','>'),$string);
        return recode_string("iso-2022-cn..html",$string);
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
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
    global $aggressive_decoding;

    // this is CPU intensive task. Use recode functions if they are available.
    if (function_exists('recode_string')) {
        $string=str_replace(array('&amp;','&quot;','&lt;','&gt;'),array('&','"','<','>'),$string);
        return recode_string("euc-kr..html",$string);
    }


    if (!$aggressive_decoding) return $string;

    // don't do decoding when there are no 8bit symbols
    if (! sq_is8bit($string,'ks_c_5601_1987'))
        return $string;

$cp949=array();

    $index=0;
    $ret='';

    while ( $index < strlen($string)) {
      if ( preg_match('/[\200-\237]|\240|[\241-\375]/', $string[$index])) {
        $ret.= str_replace(array_keys($cp949), array_values($cp949), $string[$index] . $string[$index+1]);
        $index=$index+2;
      } else {
        $ret.= $string[$index];
        $index=$index+1;
      }
    }

    return $ret;
}

?>
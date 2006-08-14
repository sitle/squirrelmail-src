<?php

/**
 * SquirrelMail internationalization functions
 *
 * This file contains variuos functions that are needed to do
 * internationalization of SquirrelMail.
 *
 * Internally the output character set is used. Other characters are
 * encoded using Unicode entities according to HTML 4.0.
 *
 * @copyright &copy; 1999-2006 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id$
 * @package squirrelmail
 * @subpackage i18n
 */

/** @ignore */
if (!defined('SM_PATH')) define('SM_PATH','../');

/** Everything uses global.php... */
require_once(SM_PATH . 'functions/global.php');

/**
 * php setlocale function wrapper
 *
 * From php 4.3.0 it is possible to use arrays in order to set locale.
 * php gettext extension works only when locale is set. This wrapper
 * function allows to use more than one locale name.
 *
 * @param int $category locale category name. Use php named constants
 *     (LC_ALL, LC_COLLATE, LC_CTYPE, LC_MONETARY, LC_NUMERIC, LC_TIME)
 * @param mixed $locale option contains array with possible locales or string with one locale
 * @return string name of set locale or false, if all locales fail.
 * @since 1.5.1 and 1.4.5
 * @see http://www.php.net/setlocale
 */
function sq_setlocale($category,$locale) {
    if (is_string($locale)) {
        // string with only one locale
        $ret = setlocale($category,$locale);
    } elseif (! check_php_version(4,3)) {
        // older php version (second setlocale argument must be string)
        $ret=false;
        $index=0;
        while ( ! $ret && $index<count($locale)) {
            $ret=setlocale($category,$locale[$index]);
            $index++;
        }
    } else {
        // php 4.3.0 or better, use entire array
        $ret=setlocale($category,$locale);
    }

    /* safety checks */
    if (preg_match("/^.*\/.*\/.*\/.*\/.*\/.*$/",$ret)) {
        /**
         * Welcome to We-Don't-Follow-Own-Fine-Manual department
         * OpenBSD 3.8, 3.9-current and maybe later versions 
         * return invalid response to setlocale command.
         * SM bug report #1427512.
         */
        $ret = false;
    }
    return $ret;
}

/**
 * Converts string from given charset to charset, that can be displayed by user translation.
 *
 * Function by default returns html encoded strings, if translation uses different encoding.
 * If $charset is not supported - function returns unconverted string.
 *
 * sanitizing of html tags is also done by this function.
 *
 * @param string $charset
 * @param string $string Text to be decoded
 * @param boolean $force_decode converts string to html without $charset!=$default_charset check.
 * Argument is available since 1.5.1 and 1.4.5.
 * @param boolean $save_html disables htmlspecialchars() in order to preserve 
 *  html formating. Use with care. Available since 1.5.1 and 1.4.6
 * @return string decoded string
 */
function charset_decode ($charset, $string, $force_decode=false, $save_html=false) {
    global $default_charset;

    /* All HTML special characters are 7 bit and can be replaced first */
    if (! $save_html) $string = htmlspecialchars ($string);
    $charset = strtolower($charset);

    set_my_charset() ;

    // Don't do conversion if charset is the same.
    if ( ! $force_decode && $charset == strtolower($default_charset) )
        return $string;

    /* controls cpu and memory intensive decoding cycles */
    global $aggressive_decoding;
    $aggressive_decoding = false;

    $decode=fixcharset($charset);
    $decodefile=SM_PATH . 'functions/decode/' . $decode . '.php';
    if (file_exists($decodefile)) {
      include_once($decodefile);
      $ret = call_user_func('charset_decode_'.$decode, $string, $save_html);
    } else {
      $ret = $string;
    }

    return( $ret );
}

/**
 * Converts html string to given charset
 * @since 1.4.4 and 1.5.1
 * @param string $string
 * @param string $charset
 * @param boolean $htmlencode keep htmlspecialchars encoding
 * @param string
 */
function charset_encode($string,$charset,$htmlencode=true) {
    global $default_charset;

    $encode=fixcharset($charset);
    $encodefile=SM_PATH . 'functions/encode/' . $encode . '.php';
    if (file_exists($encodefile)) {
        include_once($encodefile);
        $ret = call_user_func('charset_encode_'.$encode, $string);
    } elseif(file_exists(SM_PATH . 'functions/encode/us_ascii.php')) {
        // function replaces all 8bit html entities with question marks.
        // it is used when other encoding functions are unavailable
        include_once(SM_PATH . 'functions/encode/us_ascii.php');
        $ret = charset_encode_us_ascii($string);
    } else {
        /**
         * fix for yahoo users that remove all us-ascii related things
         */
        $ret = $string;
    }

    /**
     * Undo html special chars, some places (like compose form) have
     * own sanitizing functions and don't need html symbols.
     * Undo chars only after encoding in order to prevent conversion of 
     * html entities in plain text emails.
     */
    if (! $htmlencode ) {
        $ret = str_replace(array('&amp;','&gt;','&lt;','&quot;'),array('&','>','<','"'),$ret);
    }

    return( $ret );
}

/**
 * Combined decoding and encoding functions
 *
 * If conversion is done to charset different that utf-8, unsupported symbols
 * will be replaced with question marks.
 * @since 1.4.4 and 1.5.1
 * @param string $in_charset initial charset
 * @param string $string string that has to be converted
 * @param string $out_charset final charset
 * @param boolean $htmlencode keep htmlspecialchars encoding
 * @return string converted string
 */
function charset_convert($in_charset,$string,$out_charset,$htmlencode=true) {
    $string=charset_decode($in_charset,$string,true);
    $string=sqi18n_convert_entities($string);
    $string=charset_encode($string,$out_charset,$htmlencode);
    return $string;
}

/**
 * Makes charset name suitable for decoding cycles
 *
 * ks_c_5601_1987, x-euc-* and x-windows-* charsets are supported
 * since 1.5.1/1.4.6
 * @param string $charset Name of charset
 * @return string $charset Adjusted name of charset
 * @since 1.5.1 and 1.4.4
 */
function fixcharset($charset) {
    /* remove minus and characters that might be used in paths from charset
     * name in order to be able to use it in function names and include calls.
     */
    $charset=preg_replace("/[-:.\/\\\]/",'_',$charset);

    // OE ks_c_5601_1987 > cp949 
    $charset=str_replace('ks_c_5601_1987','cp949',$charset);
    // Moz x-euc-tw > euc-tw
    $charset=str_replace('x_euc','euc',$charset);
    // Moz x-windows-949 > cp949
    $charset=str_replace('x_windows_','cp',$charset);
    // windows-125x and cp125x charsets
    $charset=str_replace('windows_','cp',$charset);

    // ibm > cp
    $charset=str_replace('ibm','cp',$charset);

    // iso-8859-8-i -> iso-8859-8
    // use same cycle until I'll find differences
    $charset=str_replace('iso_8859_8_i','iso_8859_8',$charset);

    return $charset;
}

/**
 * Converts html character entities to numeric entities
 *
 * SquirrelMail encoding functions work only with numeric entities.
 * This function fixes issues with decoding functions that might convert
 * some symbols to character entities. Issue is specific to PHP recode
 * extension decoding. Function is used internally in charset_convert()
 * function.
 * @param string $str string that might contain html character entities
 * @return string string with character entities converted to decimals.
 * @since 1.5.2
 */
function sqi18n_convert_entities($str) {

    $entities = array(
        // Latin 1
        '&nbsp;'   => '&#160;',
        '&iexcl;'  => '&#161;',
        '&cent;'   => '&#162;',
        '&pound;'  => '&#163;',
        '&curren;' => '&#164;',
        '&yen;'    => '&#165;',
        '&brvbar;' => '&#166;',
        '&sect;'   => '&#167;',
        '&uml;'    => '&#168;',
        '&copy;'   => '&#169;',
        '&ordf;'   => '&#170;',
        '&laquo;'  => '&#171;',
        '&not;'    => '&#172;',
        '&shy;'    => '&#173;',
        '&reg;'    => '&#174;',
        '&macr;'   => '&#175;',
        '&deg;'    => '&#176;',
        '&plusmn;' => '&#177;',
        '&sup2;'   => '&#178;',
        '&sup3;'   => '&#179;',
        '&acute;'  => '&#180;',
        '&micro;'  => '&#181;',
        '&para;'   => '&#182;',
        '&middot;' => '&#183;',
        '&cedil;'  => '&#184;',
        '&sup1;'   => '&#185;',
        '&ordm;'   => '&#186;',
        '&raquo;'  => '&#187;',
        '&frac14;' => '&#188;',
        '&frac12;' => '&#189;',
        '&frac34;' => '&#190;',
        '&iquest;' => '&#191;',
        '&Agrave;' => '&#192;',
        '&Aacute;' => '&#193;',
        '&Acirc;'  => '&#194;',
        '&Atilde;' => '&#195;',
        '&Auml;'   => '&#196;',
        '&Aring;'  => '&#197;',
        '&AElig;'  => '&#198;',
        '&Ccedil;' => '&#199;',
        '&Egrave;' => '&#200;',
        '&Eacute;' => '&#201;',
        '&Ecirc;'  => '&#202;',
        '&Euml;'   => '&#203;',
        '&Igrave;' => '&#204;',
        '&Iacute;' => '&#205;',
        '&Icirc;'  => '&#206;',
        '&Iuml;'   => '&#207;',
        '&ETH;'    => '&#208;',
        '&Ntilde;' => '&#209;',
        '&Ograve;' => '&#210;',
        '&Oacute;' => '&#211;',
        '&Ocirc;'  => '&#212;',
        '&Otilde;' => '&#213;',
        '&Ouml;'   => '&#214;',
        '&times;'  => '&#215;',
        '&Oslash;' => '&#216;',
        '&Ugrave;' => '&#217;',
        '&Uacute;' => '&#218;',
        '&Ucirc;'  => '&#219;',
        '&Uuml;'   => '&#220;',
        '&Yacute;' => '&#221;',
        '&THORN;'  => '&#222;',
        '&szlig;'  => '&#223;',
        '&agrave;' => '&#224;',
        '&aacute;' => '&#225;',
        '&acirc;'  => '&#226;',
        '&atilde;' => '&#227;',
        '&auml;'   => '&#228;',
        '&aring;'  => '&#229;',
        '&aelig;'  => '&#230;',
        '&ccedil;' => '&#231;',
        '&egrave;' => '&#232;',
        '&eacute;' => '&#233;',
        '&ecirc;'  => '&#234;',
        '&euml;'   => '&#235;',
        '&igrave;' => '&#236;',
        '&iacute;' => '&#237;',
        '&icirc;'  => '&#238;',
        '&iuml;'   => '&#239;',
        '&eth;'    => '&#240;',
        '&ntilde;' => '&#241;',
        '&ograve;' => '&#242;',
        '&oacute;' => '&#243;',
        '&ocirc;'  => '&#244;',
        '&otilde;' => '&#245;',
        '&ouml;'   => '&#246;',
        '&divide;' => '&#247;',
        '&oslash;' => '&#248;',
        '&ugrave;' => '&#249;',
        '&uacute;' => '&#250;',
        '&ucirc;'  => '&#251;',
        '&uuml;'   => '&#252;',
        '&yacute;' => '&#253;',
        '&thorn;'  => '&#254;',
        '&yuml;'   => '&#255;',
        // Latin Extended-A
        '&OElig;'  => '&#338;',
        '&oelig;'  => '&#339;',
        '&Scaron;' => '&#352;',
        '&scaron;' => '&#353;',
        '&Yuml;'   => '&#376;',
        // Spacing Modifier Letters
        '&circ;'   => '&#710;',
        '&tilde;'  => '&#732;',
        // General Punctuation
        '&ensp;'   => '&#8194;',
        '&emsp;'   => '&#8195;',
        '&thinsp;' => '&#8201;',
        '&zwnj;'   => '&#8204;',
        '&zwj;'    => '&#8205;',
        '&lrm;'    => '&#8206;',
        '&rlm;'    => '&#8207;',
        '&ndash;'  => '&#8211;',
        '&mdash;'  => '&#8212;',
        '&lsquo;'  => '&#8216;',
        '&rsquo;'  => '&#8217;',
        '&sbquo;'  => '&#8218;',
        '&ldquo;'  => '&#8220;',
        '&rdquo;'  => '&#8221;',
        '&bdquo;'  => '&#8222;',
        '&dagger;' => '&#8224;',
        '&Dagger;' => '&#8225;',
        '&permil;' => '&#8240;',
        '&lsaquo;' => '&#8249;',
        '&rsaquo;' => '&#8250;',
        '&euro;'   => '&#8364;',
        // Latin Extended-B
        '&fnof;' => '&#402;',
        // Greek
        '&Alpha;'  => '&#913;',
        '&Beta;'   => '&#914;',
        '&Gamma;'  => '&#915;',
        '&Delta;'  => '&#916;',
        '&Epsilon;' => '&#917;',
        '&Zeta;'   => '&#918;',
        '&Eta;'    => '&#919;',
        '&Theta;'  => '&#920;',
        '&Iota;'   => '&#921;',
        '&Kappa;'  => '&#922;',
        '&Lambda;' => '&#923;',
        '&Mu;'     => '&#924;',
        '&Nu;'     => '&#925;',
        '&Xi;'     => '&#926;',
        '&Omicron;' => '&#927;',
        '&Pi;'     => '&#928;',
        '&Rho;'    => '&#929;',
        '&Sigma;'  => '&#931;',
        '&Tau;'    => '&#932;',
        '&Upsilon;' => '&#933;',
        '&Phi;'    => '&#934;',
        '&Chi;'    => '&#935;',
        '&Psi;'    => '&#936;',
        '&Omega;'  => '&#937;',
        '&alpha;'  => '&#945;',
        '&beta;'   => '&#946;',
        '&gamma;'  => '&#947;',
        '&delta;'  => '&#948;',
        '&epsilon;' => '&#949;',
        '&zeta;'   => '&#950;',
        '&eta;'    => '&#951;',
        '&theta;'  => '&#952;',
        '&iota;'   => '&#953;',
        '&kappa;'  => '&#954;',
        '&lambda;' => '&#955;',
        '&mu;'     => '&#956;',
        '&nu;'     => '&#957;',
        '&xi;'     => '&#958;',
        '&omicron;' => '&#959;',
        '&pi;'     => '&#960;',
        '&rho;'    => '&#961;',
        '&sigmaf;' => '&#962;',
        '&sigma;'  => '&#963;',
        '&tau;'    => '&#964;',
        '&upsilon;' => '&#965;',
        '&phi;'    => '&#966;',
        '&chi;'    => '&#967;',
        '&psi;'    => '&#968;',
        '&omega;'  => '&#969;',
        '&thetasym;' => '&#977;',
        '&upsih;'  => '&#978;',
        '&piv;'    => '&#982;',
        // General Punctuation
        '&bull;'   => '&#8226;',
        '&hellip;' => '&#8230;',
        '&prime;'  => '&#8242;',
        '&Prime;'  => '&#8243;',
        '&oline;'  => '&#8254;',
        '&frasl;'  => '&#8260;',
        // Letterlike Symbols
        '&weierp;' => '&#8472;',
        '&image;'  => '&#8465;',
        '&real;'   => '&#8476;',
        '&trade;'  => '&#8482;',
        '&alefsym;' => '&#8501;',
        // Arrows
        '&larr;'   => '&#8592;',
        '&uarr;'   => '&#8593;',
        '&rarr;'   => '&#8594;',
        '&darr;'   => '&#8595;',
        '&harr;'   => '&#8596;',
        '&crarr;'  => '&#8629;',
        '&lArr;'   => '&#8656;',
        '&uArr;'   => '&#8657;',
        '&rArr;'   => '&#8658;',
        '&dArr;'   => '&#8659;',
        '&hArr;'   => '&#8660;',
        // Mathematical Operators
        '&forall;' => '&#8704;',
        '&part;'   => '&#8706;',
        '&exist;'  => '&#8707;',
        '&empty;'  => '&#8709;',
        '&nabla;'  => '&#8711;',
        '&isin;'   => '&#8712;',
        '&notin;'  => '&#8713;',
        '&ni;'     => '&#8715;',
        '&prod;'   => '&#8719;',
        '&sum;'    => '&#8721;',
        '&minus;'  => '&#8722;',
        '&lowast;' => '&#8727;',
        '&radic;'  => '&#8730;',
        '&prop;'   => '&#8733;',
        '&infin;'  => '&#8734;',
        '&ang;'    => '&#8736;',
        '&and;'    => '&#8743;',
        '&or;'     => '&#8744;',
        '&cap;'    => '&#8745;',
        '&cup;'    => '&#8746;',
        '&int;'    => '&#8747;',
        '&there4;' => '&#8756;',
        '&sim;'    => '&#8764;',
        '&cong;'   => '&#8773;',
        '&asymp;'  => '&#8776;',
        '&ne;'     => '&#8800;',
        '&equiv;'  => '&#8801;',
        '&le;'     => '&#8804;',
        '&ge;'     => '&#8805;',
        '&sub;'    => '&#8834;',
        '&sup;'    => '&#8835;',
        '&nsub;'   => '&#8836;',
        '&sube;'   => '&#8838;',
        '&supe;'   => '&#8839;',
        '&oplus;'  => '&#8853;',
        '&otimes;' => '&#8855;',
        '&perp;'   => '&#8869;',
        '&sdot;'   => '&#8901;',
        // Miscellaneous Technical
        '&lceil;'  => '&#8968;',
        '&rceil;'  => '&#8969;',
        '&lfloor;' => '&#8970;',
        '&rfloor;' => '&#8971;',
        '&lang;'   => '&#9001;',
        '&rang;'   => '&#9002;',
        // Geometric Shapes
        '&loz;'    => '&#9674;',
        // Miscellaneous Symbols
        '&spades;' => '&#9824;',
        '&clubs;'  => '&#9827;',
        '&hearts;' => '&#9829;',
        '&diams;'  => '&#9830;');

    $str = str_replace(array_keys($entities), array_values($entities), $str);

    return $str;
}

/*
 * Set up the language to be output
 * if $do_search is true, then scan the browser information
 * for a possible language that we know
 */
function set_up_language($sm_language, $do_search = false, $default = false) {

    static $SetupAlready = 0;
    global $use_gettext, $languages,
           $squirrelmail_language, $squirrelmail_default_language, 
           $default_charset, $sm_notAlias;

    if ($SetupAlready) {
        return;
    }

    $SetupAlready = TRUE;
    sqgetGlobalVar('HTTP_ACCEPT_LANGUAGE',  $accept_lang, SQ_SERVER);

    /**
     * detect preferred language from header when SquirrelMail asks for
     * it or when default language is set to empty string.
     */
    if (($do_search || empty($squirrelmail_default_language)) &&
        ! $sm_language &&
        isset($accept_lang)) {
        $sm_language = substr($accept_lang, 0, 2);
    }

    /**
     * Use default language when it is not detected or when script
     * asks to use default language.
     */
    if ((!$sm_language||$default) && 
        ! empty($squirrelmail_default_language)) {
        $squirrelmail_language = $squirrelmail_default_language;
        $sm_language = $squirrelmail_default_language;
    }

    /** provide failsafe language when detection fails */
    if (! $sm_language) $sm_language='en_US';

    $sm_notAlias = $sm_language;

    /**
     * Catch removed translation
     * System reverts to English translation if user prefs contain translation
     * that is not available in $languages array
     */
    if (!isset($languages[$sm_notAlias])) {
        $sm_notAlias="en_US";
    }

    while (isset($languages[$sm_notAlias]['ALIAS'])) {
        $sm_notAlias = $languages[$sm_notAlias]['ALIAS'];
    }

    if ( isset($sm_language) &&
         $use_gettext &&
         $sm_language != '' &&
         isset($languages[$sm_notAlias]['CHARSET']) ) {
        bindtextdomain( 'squirrelmail', SM_PATH . 'locale/' );
        textdomain( 'squirrelmail' );
        if (function_exists('bind_textdomain_codeset')) {
            bind_textdomain_codeset ("squirrelmail", $languages[$sm_notAlias]['CHARSET'] );
        }

        // Use LOCALE key, if it is set.
        if (isset($languages[$sm_notAlias]['LOCALE'])){
            $longlocale=$languages[$sm_notAlias]['LOCALE'];
        } else {
            $longlocale=$sm_notAlias;
        }

        // try setting locale
        $retlocale=sq_setlocale(LC_ALL, $longlocale);

        // check if locale is set and assign that locale to $longlocale
        // in order to use it in putenv calls.
        if (! is_bool($retlocale)) {
            $longlocale=$retlocale;
        } elseif (is_array($longlocale)) {
            // setting of all locales failed.
            // we need string instead of array used in LOCALE key.
            $longlocale=$sm_notAlias;
        }

        if ( !((bool)ini_get('safe_mode')) &&
             getenv( 'LC_ALL' ) != $longlocale ) {
            putenv( "LC_ALL=$longlocale" );
            putenv( "LANG=$longlocale" );
            putenv( "LANGUAGE=$longlocale" );
            putenv( "LC_NUMERIC=C" );
            if ($sm_notAlias=='tr_TR' || $sm_notAlias=='tr') putenv( "LC_CTYPE=C" );
        }
        // Workaround for plugins that use numbers with floating point
        // It might be removed if plugins use correct decimal delimiters
        // according to locale settings.
        setlocale(LC_NUMERIC, 'C');
        // Workaround for specific Turkish strtolower/strtoupper rules.
        // Many functions expect English conversion rules.
        if ($sm_notAlias=='tr_TR' || $sm_notAlias=='tr') setlocale(LC_CTYPE,'C');

        $squirrelmail_language = $sm_notAlias;
        if ($squirrelmail_language == 'en_US') {
            header( 'Content-Type: text/html; charset=' . $default_charset );
        } else {
            header( 'Content-Type: text/html; charset=' . $languages[$sm_notAlias]['CHARSET'] );
        }
        // mbstring.func_overload<>0 fix. See cvs HEAD comments.
        if (function_exists('mb_internal_encoding') &&
            check_php_version(4,2,0) &&
            (int)ini_get('mbstring.func_overload')!=0) {
            mb_internal_encoding('pass');
        }
    }
}

/**
 * Sets default_charset variable according to the one that is used by user's translations.
 *
 * Function changes global $default_charset variable in order to be sure, that it
 * contains charset used by user's translation. Sanity of $squirrelmail_language
 * and $default_charset combination provided in SquirrelMail config is also tested.
 *
 * There can be a $default_charset setting in the
 * config.php file, but the user may have a different language
 * selected for a user interface. This function checks the
 * language selected by the user and tags the outgoing messages
 * with the appropriate charset corresponding to the language
 * selection. This is "more right" (tm), than just stamping the
 * message blindly with the system-wide $default_charset.
 */
function set_my_charset(){
    global $data_dir, $username, $default_charset, $languages, $squirrelmail_language;

    $my_language = getPref($data_dir, $username, 'language');
    if (!$my_language) {
        $my_language = $squirrelmail_language ;
    }
    // Catch removed translation
    if (!isset($languages[$my_language])) {
        $my_language="en_US";
    }
    while (isset($languages[$my_language]['ALIAS'])) {
        $my_language = $languages[$my_language]['ALIAS'];
    }
    $my_charset = $languages[$my_language]['CHARSET'];
    if ($my_language!='en_US') {
        $default_charset = $my_charset;
    }
}

/**
 * Function informs if it is safe to convert given charset to the one that is used by user.
 *
 * It is safe to use conversion only if user uses utf-8 encoding and when
 * converted charset is similar to the one that is used by user.
 *
 * @param string $input_charset Charset of text that needs to be converted
 * @return bool is it possible to convert to user's charset
 */
function is_conversion_safe($input_charset) {
    global $languages, $sm_notAlias, $default_charset, $lossy_encoding;

    if (isset($lossy_encoding) && $lossy_encoding )
        return true;

    // convert to lower case
    $input_charset = strtolower($input_charset);

    // Is user's locale Unicode based ?
    if ( $default_charset == "utf-8" ) {
        return true;
    }

    // Charsets that are similar
    switch ($default_charset) {
    case "windows-1251":
        if ( $input_charset == "iso-8859-5" ||
           $input_charset == "koi8-r" ||
           $input_charset == "koi8-u" ) {
        return true;
        } else {
            return false;
        }
    case "windows-1257":
        if ( $input_charset == "iso-8859-13" ||
             $input_charset == "iso-8859-4" ) {
            return true;
        } else {
            return false;
        }
    case "iso-8859-4":
        if ( $input_charset == "iso-8859-13" ||
             $input_charset == "windows-1257" ) {
            return true;
        } else {
            return false;
        }
    case "iso-8859-5":
        if ( $input_charset == "windows-1251" ||
             $input_charset == "koi8-r" ||
             $input_charset == "koi8-u" ) {
            return true;
        } else {
            return false;
        }
    case "iso-8859-13":
        if ( $input_charset == "iso-8859-4" ||
             $input_charset == "windows-1257" ) {
            return true;
        } else {
            return false;
        }
    case "koi8-r":
        if ( $input_charset == "windows-1251" ||
             $input_charset == "iso-8859-5" ||
             $input_charset == "koi8-u" ) {
            return true;
        } else {
            return false;
        }
    case "koi8-u":
        if ( $input_charset == "windows-1251" ||
             $input_charset == "iso-8859-5" ||
             $input_charset == "koi8-r" ) {
            return true;
        } else {
            return false;
        }
    default:
        return false;
    }
}

/* ------------------------------ main --------------------------- */

global $squirrelmail_language, $languages, $use_gettext;

if (! sqgetGlobalVar('squirrelmail_language',$squirrelmail_language,SQ_COOKIE)) {
    $squirrelmail_language = '';
}

/* This array specifies the available languages. */
$languages['en_US']['NAME']    = 'English';
$languages['en_US']['CHARSET'] = 'utf-8';
$languages['en_US']['LOCALE']  = 'en_US.UTF8';
$languages['en']['ALIAS']      = 'en_US';

/**
 * Automatic translation loading from setup.php files.
 * Solution for bug. 1240889.
 * setup.php file can contain $languages array entries.
 */
if (is_dir(SM_PATH . 'locale') &&
    is_readable(SM_PATH . 'locale')) {
    $localedir = dir(SM_PATH . 'locale');
    while($lang_dir=$localedir->read()) {
        // remove trailing slash, if present
        if (substr($lang_dir,-1)=='/') {
            $lang_dir = substr($lang_dir,0,-1);
        }
        if ($lang_dir != '..' && $lang_dir != '.' && $lang_dir != 'CVS' &&
            is_dir(SM_PATH.'locale/'.$lang_dir) &&
            file_exists(SM_PATH.'locale/'.$lang_dir.'/setup.php')) {
            include_once(SM_PATH.'locale/'.$lang_dir.'/setup.php');
        }
    }
    $localedir->close();
}

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
?>
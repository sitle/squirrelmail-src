<?php
/**
 * Gettext test script.
 *
 * If script is executed in SquirrelMail top directory, it can output 
 * translated string when locale is working correctly. If you want to test 
 * script in PHP safe_mode = on environment, disable all putenv calls.
 *
 * @copyright 2005-2012 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id$
 * @package locales
 */

/** set gettext domain */
bindtextdomain('squirrelmail','./locale/');
textdomain('squirrelmail');

/** set gettext codeset for php 4.2+ */
if (function_exists('bind_textdomain_codeset')) {
    bind_textdomain_codeset ('squirrelmail', 'utf-8' );
}

/** set locale and show returned value */
var_dump(setlocale(LC_ALL, 'ru_RU.UTF-8'));

/** set environment vars */
putenv('LC_ALL=ru_RU.UTF-8');
//putenv('LANG=ru_RU.UTF-8');
//putenv('LANGUAGE=ru_RU.UTF-8');
putenv('LC_NUMERIC=C');
//putenv('LC_CTYPE=C');

/** float workarounds (SM 1.4.5+) */
setlocale(LC_NUMERIC, 'C');

/** character conversion workarounds for Turkish (SM 1.4.5+) */
//setlocale(LC_CTYPE,'C');

echo _('Addresses')."\n";
?>
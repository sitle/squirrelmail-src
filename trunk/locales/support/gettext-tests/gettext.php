<?php
/**
 * Gettext test script.
 *
 * @copyright &copy; 2005 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id$
 * @package locales
 */

/** set gettext domain */
bindtextdomain('test','./');
textdomain('test');

/** set gettext codeset for php 4.2+ */
if (function_exists('bind_textdomain_codeset')) {
    bind_textdomain_codeset ('test', 'utf-8' );
}

/** set locale */
setlocale(LC_ALL, 'ru_RU.UTF-8');

/** set environment vars */
//putenv('LC_ALL=ru_RU.UTF-8');
//putenv('LANG=ru_RU.UTF-8');
//putenv('LANGUAGE=ru_RU.UTF-8');
//putenv('LC_NUMERIC=C');
//putenv('LC_CTYPE=C');

/** float workarounds */
setlocale(LC_NUMERIC, 'C');

/** character conversion workarounds for Turkish */
//setlocale(LC_CTYPE,'C');

echo _('Test')."\n";
?>
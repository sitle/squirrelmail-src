<?php

/**
* validate.php
*
* Copyright (c) 1999-2002 The SquirrelMail Project Team
* Licensed under the GNU GPL. For full terms see the file COPYING.
*
* $Id$
*/

/*
 * Set a different session name to stop session conflicts on same server
 * with software such as Gallery.  This *must* be set above all the rest
 * of the code to stop other items launching a session first
 */

ini_set('session.name' , 'SQMSESSID');

session_start();

require_once('../functions/i18n.php');
require_once('../functions/auth.php');
require_once('../functions/strings.php');
require_once('../src/global.php');

is_logged_in();

/**
* Auto-detection
*
* if $send (the form button's name) contains "\n" as the first char
* and the script is compose.php, then trim everything. Otherwise, we
* don't have to worry.
*
* This is for a RedHat package bug and a Konqueror (pre 2.1.1?) bug
*/

$PHP_SELF = $_SERVER['PHP_SELF'];

if (isset($_POST['send'])) {
    $send = $_POST['send'];
}
elseif (isset($_GET['send'])) {
    $send = $_GET['send'];
}

if (isset($send)
    && (substr($send, 0, 1) == "\n")
    && (substr($PHP_SELF, -12) == '/compose.php')) {
    if ($REQUEST_METHOD == 'POST') {
        TrimArray($_POST);
    } else {
        TrimArray($_GET);
    }
}

/**
* Everyone needs stuff from config, and config needs stuff from
* strings.php, so include them both here. Actually, strings is
* included at the top now as the string array functions have
* been moved into it.
*
* Include them down here instead of at the top so that all config
* variables overwrite any passed in variables (for security).
*/

/**
 * Reset the $theme() array in case a value was passed via a cookie.
 * This is until theming is rewritten.
 */

unset($theme);
$theme=array();

require_once('../config/config.php');
require_once('../src/load_prefs.php');
require_once('../functions/page_header.php');
require_once('../functions/prefs.php');

/* Set up the language (i18n.php was included by auth.php). */
set_up_language(getPref($data_dir, $username, 'language'));

$timeZone = getPref($data_dir, $username, 'timezone');

/* Check to see if we are allowed to set the TZ environment variable.
 * We are able to do this if ... 
 *   safe_mode is disabled OR
 *   safe_mode_allowed_env_vars is empty (you are allowed to set any) OR
 *   safe_mode_allowed_env_vars contains TZ 
 */
$tzChangeAllowed = (!ini_get('safe_mode')) ||
		   !strcmp(ini_get('safe_mode_allowed_env_vars'),'') || 
		   preg_match('/^([\w_]+,)*TZ/', ini_get('safe_mode_allowed_env_vars')); 

if ( $timeZone != SMPREF_NONE && ($timeZone != "") 
    && $tzChangeAllowed ) {
    putenv("TZ=".$timeZone);
}
?>

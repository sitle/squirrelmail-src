<?php
/*
 * Copyright (c) 2003-2004 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition/modification to the
 * Framework for Object Orientated Web Development (Foowd).
 *
 * $Id$
 */

/**
 * Include config.php, or issue message.
 */
if ( file_exists('config.php') )
    require_once('config.php');
else {
?>
    <p>The file 'config.php' does not exist. Please create one
    using one of the following mechanisms:</p>
    <ul>
    <li>Copy config.default.php to config.php and modify config.php
    as necessary.</li>
    <li>Create a new file config.php, include config.default.php, and
    over-ride the default settings as required.</li>
    </ul>
<?php
    exit;
}

if ( isset($foowd_parameters) && !isset($smdoc_parameters) )
    $smdoc_parameters =& $foowd_parameters;

/**
 * Session initialization
 * -------------------------------------------------------------
 */
ini_set('magic_quotes_runtime','0');
ini_set('session.name' , 'SMDOC_SESSID');
session_start();

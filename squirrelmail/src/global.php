<?php

/**
 * globals.php
 *
 * Copyright (c) 1999-2002 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This includes code to update < 4.1.0 globals to the newer format 
 * It also has some (only one so far) handy-dandy global variable functions
 *
 */


/* convert old-style superglobals to current method
 * this is executed if you are running PHP 4.0.x.
 * it is run via a require_once directive in validate.php 
 * and redirect.php. Patch submitted by Ray Black.
 */ 

if ( (float)substr(PHP_VERSION,0,3) < 4.1 ) {
  global $_COOKIE, $_ENV, $_FILES, $_GET, $_POST, $_SERVER, $_SESSION;
  global $HTTP_COOKIE_VARS, $HTTP_ENV_VARS, $HTTP_POST_FILES, $HTTP_GET_VARS,
         $HTTP_POST_VARS, $HTTP_SERVER_VARS, $HTTP_SESSION_VARS;
  $_COOKIE  =& $HTTP_COOKIE_VARS;
  $_ENV     =& $HTTP_ENV_VARS;
  $_FILES   =& $HTTP_POST_FILES;
  $_GET     =& $HTTP_GET_VARS;
  $_POST    =& $HTTP_POST_VARS;
  $_SERVER  =& $HTTP_SERVER_VARS;
  $_SESSION =& $HTTP_SESSION_VARS;
}


/* function to make superglobals act just like register_globals
 * = On for the duration of the script. it maps a global group's
 * key => value pairs to variable_name => value like so:
 *
 * $_GET['mailbox'] => "INBOX" 
 *
 *  changes to:
 *
 * $mailbox = "INBOX";
 *
 * default is SQUIRREL. other options are:
 *
 * GET          Variables supplied by an HTTP GET
 * POST         Variables supplied by an HTTP POST
 * SESSION      Variables registered in the session
 * COOKIE       Variables supplied by an HTTP cookie
 * ENV          Variables supplied by the environment
 * SERVER       Variables set by the web server
 * FILES        Variables set by HTTP POST file uploads
 * SQUIRREL     POST, GET, COOKIE, and SESSION vars
 * ALL          The whole GLOBALS array     
 *
 */


function extract_globals( $group ) {
    switch ($group) {
        case 'GET':
            if (!empty($_GET)) {
                extract($_GET);
            }
            break;
        case 'POST':
            if (!empty($_POST)) {
                extract($_POST);
            }
            break;
        case 'SESSION':
            if (!empty($_SESSION)) {
                extract($_SESSION);
            }
            break;
        case 'COOKIE':
            if (!empty($_COOKIE)) {
                extract($_COOKIE);
            }
            break;
        case 'ENV':
            if (!empty($_ENV)) {
                extract($_ENV);
            }
            break;
        case 'SERVER':
            if (!empty($_SERVER)) {
                extract($_SERVER);
            }
            break;
        case 'FILES':
            if (!empty($_FILES)) {
                extract($_FILES);
            }
            break;
        case 'ALL':
            if (!empty($GLOBALS)) {
                extract($GLOBALS);
            }
            break;
        case 'SQUIRREL':
        default:
            if (!empty($_GET)) {
                extract($_GET);
            }
            if (!empty($_POST)) {
                extract($_POST);
            }
            if (!empty($_SESSION)) {
                extract($_SESSION);
            }
            if (!empty($_COOKIE)) {
                extract($_COOKIE);
            }
            break;
    }
}               


            

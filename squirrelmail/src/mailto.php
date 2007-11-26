<?php

/**
 * mailto.php -- mailto: url handler
 *
 * This checks to see if we're logged in.  If we are we open up a new
 * compose window for this email, otherwise we go to login.php
 * (the above functionality has been disabled, by default you are required to
 *  login first)
 *
 * Use the following url to use mailto:
 * http://<your server>/<squirrelmail base dir>/src/mailto.php?emailaddress=%1
 * see ../contrib/squirrelmail.mailto.reg for a Windows Registry file
 * @copyright &copy; 1999-2007 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id$
 * @package squirrelmail
 */

/** This is the mailto page */
define('PAGE_NAME', 'mailto');

/**
 * Path for SquirrelMail required files.
 * @ignore
 */
define('SM_PATH','../');

/* SquirrelMail required files. */
require_once(SM_PATH . 'functions/global.php');


// Force users to login each time?  Setting this to TRUE does NOT mean 
// that if no user is logged in that it won't require a correct login 
// first!  Instead, setting it to TRUE will log out anyone currently
// logged in and force a re-login.  Setting this to FALSE will still
// require a login if no one is logged in, but it will allow you to go
// directly to compose your message if you are already logged in.  
//
// Note, however, that depending on how the client browser manages 
// sessions and how the client operating system is set to handle 
// mailto: links, you may have to log in every time no matter what
// (IE under WinXP appears to pop up a new window and thus always 
// start a new session; Firefox under WinXP seems to start a new tab 
// which will find a current login if one exists). 
//
$force_login = FALSE;


// Open only the compose window, meaningless if $force_login is TRUE
//
$compose_only = FALSE;


header('Pragma: no-cache');

$trtable = array('cc'           => 'cc',
                 'bcc'          => 'bcc',
                 'body'         => 'body',
                 'subject'      => 'subject');
$url = '';

$data = array();

if (sqgetGlobalVar('emailaddress', $emailaddress)) {
    $emailaddress = trim($emailaddress);
    if (stristr($emailaddress, 'mailto:')) {
        $emailaddress = substr($emailaddress, 7);
    }
    if (strpos($emailaddress, '?') !== FALSE) {
        list($emailaddress, $a) = explode('?', $emailaddress, 2);
        if (strlen(trim($a)) > 0) {
            $a = explode('=', $a, 2);
            $data[strtolower($a[0])] = $a[1];
        }
    }
    $data['to'] = $emailaddress;

    /* CC, BCC, etc could be any case, so we'll fix them here */
    foreach($_GET as $k=>$g) {
        $k = strtolower($k);
        if (isset($trtable[$k])) {
            $k = $trtable[$k];
            $data[$k] = $g;
        }
    }
}
sqsession_is_active();

if (!$force_login && sqsession_is_registered('user_is_logged_in')) {
    if ($compose_only) {
        $redirect = 'compose.php?mailtodata=' . urlencode(serialize($data));
    } else {
        $redirect = 'webmail.php?right_frame=compose.php&mailtodata=' . urlencode(serialize($data));
    }
} else {
    $redirect = 'login.php?mailtodata=' . urlencode(serialize($data));
}

session_write_close();
header('Location: ' . get_location() . '/' . $redirect);

<?php

/**
 * folders_subscribe.php
 *
 * Copyright (c) 1999-2002 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * Subscribe and unsubcribe form folders. 
 * Called from folders.php
 *
 * $Id$
 */

require_once('../src/validate.php');
require_once('../functions/imap.php');
require_once('../functions/display_messages.php');

/* globals */
$username = $_SESSION['username'];
$key = $_COOKIE['key'];
$onetimepad = $_SESSION['onetimepad'];

$method = $_GET['method'];
$mailbox = $_POST['mailbox'];

/* end globals */

$location = get_location();

if (!isset($mailbox)) {
    header("Location: $location/folders.php");
}

$imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);

if ($method == 'sub') {
    for ($i=0; $i < count($mailbox); $i++) {
        $mailbox[$i] = trim($mailbox[$i]);
        sqimap_subscribe ($imapConnection, $mailbox[$i]);
    }
    $success = 'subscribe';
} else {
    for ($i=0; $i < count($mailbox); $i++) {
        $mailbox[$i] = trim($mailbox[$i]);
        sqimap_unsubscribe ($imapConnection, $mailbox[$i]);
    }
    $success = 'unsubscribe';
}

sqimap_logout($imapConnection);
header("Location: $location/folders.php?success=$subscribe");

?>

<?php
/*
 *  config.php - configuration settings for notify.php
 *
 * Copyright (c) 2002 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * Configuration settings for Calendar plugin
 * 
 * $Id$
 */

// You MUST configure the following 4 Variables  (xName <xAcct@domain.com>)
$fromName  = "Reminder";	//Who the reminder is from
$fromAcct  = "reminder"; 	//the info to the left of the @ sybmol in fromAcct@domain.com
$replyName = "Reminder";	//Where replies must go 
$replyAcct = "reminder"; 	//the info to the left of the @ sybmol in replyAcct@domain.com

// Security Settings (1=Yes, 0=No)
$localDebug  = 0;		//script debug output when run from this machine
$remoteDebug = 0;		//script debug output when run from a remote machine
$allowRemote = 0;		//Allow script to be run remotely
?>

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

// System Wide Settings
$enableNotification  = 1;	//Enable notification drop down display
				// 1 = Enable, 0 = Disable
$defaultNotification = 2;	//Default Notification response on Add New
				// Acceptable Values:
				// 0	- Don't Email
				// 2	- Email Me - 0m prior
				// 4	- Email Me - 5m prior
				// 6	- Email Me - 15m prior
				// 8	- Email Me - 30m prior
				// 10	- Email Me - 1h prior
				// 12	- Email Me - 4h prior
				// 14	- Email Me - 1d prior
?>

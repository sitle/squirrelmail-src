<?php
/*
 * Created by SquirrelMail Development Team
 * This class created by porting track-associated debug functions from WTF to FOOWD.
 *
 * Original copyright from WTFW:
	This file is part of the Wiki Type Framework (WTF).
	Copyright 2002, Paul James
	See README and COPYING for more information, or see http://wtf.peej.co.uk

	WTF is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	WTF is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with WTF; if not, write to the Free Software

	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * $Id$
 */

/*** Debugging functions ***/

if (!defined('DEBUG'))       define('DEBUG',       FALSE);
if (!defined('DEBUG_SQL'))   define('DEBUG_SQL',   FALSE);
if (!defined('DEBUG_VAR'))   define('DEBUG_VAR',   FALSE);
if (!defined('DEBUG_TRACE')) define('DEBUG_TRACE', FALSE);
if (!defined('DEBUG_TIME'))  define('DEBUG_TIME',  FALSE);
if (!defined('DEBUG_EXT'))   define('DEBUG_EXT',   FALSE);

$TRACK[0] = 0;
$DEBUGSTRING = '';
$DBTRACKNUM = 0;
$startTime = getTime();

function writeDebug(&$foowd) {
    global $DEBUGSTRING, $DBTRACKNUM, $startTime, $EXTERNAL_RESOURCES;

    // If no debugging enabled or nothing to print, return early.
    if ( !DEBUG && !DEBUG_SQL && !DEBUG_VAR && !DEBUG_TRACE && !DEBUG_TIME ) 
        return;

    if ( DEBUG ) {
        echo '<div class="debug_output">';

        echo '<div class="debug_output_heading">Debug Information</div>';
        echo '<pre>';
        if ( DEBUG_SQL ) {
            echo 'Total DB Executions: ';
            echo $DBTRACKNUM . "\n";
        } 
        if ( DEBUG_TIME ) {
            echo 'Total Execution Time: ';
            echo substr( endTime($startTime), 0, 5)  . "\n";
        }
        echo '</pre>';

        if ( $DEBUGSTRING != '' ) {
            echo '<div class="debug_output_heading">Execution History</div>';
            echo '<pre>';
            echo $DEBUGSTRING;
            echo '</pre>';
        }

        if ( DEBUG_EXT ) {
            echo '<div class="debug_output_heading">External Resources</div>';
            echo '<pre>';
            echo "***** External Resources *****\n";
            print_r($EXTERNAL_RESOURCES);
            echo '</pre><br />';
        }

        if ( DEBUG_VAR ) {
            echo '<div class="debug_output_heading">Variables and Objects</div>';

            $dbuser = $foowd->dbuser;
            $dbpass = $foowd->dbpass;
            unset($foowd->dbuser);
            unset($foowd->dbpass);
            echo '<pre>';
            echo "***** FOOWD Environment *****\n";
            print_r($foowd); 
            echo '</pre><br />';
            $foowd->dbuser = $dbuser;
            $foowd->dbpass = $dbpass;

            echo '<pre>';
            echo "***** REQUEST *****\n";
            print_r($_REQUEST);
            echo '</pre><br />';
        }        
        echo '</div><br />';
    }
    return;
}

/*** Execution time ***/

function getTime() {
	$microtime = explode(' ', microtime());
	return $microtime[0] + $microtime[1];
}

function endTime($startTime) {
	$endTime = getTime();
	return $endTime - $startTime;
}

/* program execution tracking */

function track($level = NULL) {
    global $TRACK, $DEBUGSTRING, $startTime;

    // If no debugging enabled, return early.
    if ( !DEBUG || !DEBUG_TRACE)
        return;

    if ($level == NULL) { // leaving section
        for ($foo = 1; $foo < $TRACK[0]; $foo++) {
            $DEBUGSTRING .= '|';
        }
        $DEBUGSTRING .= '+-\ ';
        if (DEBUG_TIME && $startTime) {
            $DEBUGSTRING .= substr( endTime($startTime), 0, 5);
        }
        $DEBUGSTRING .= '<br/>';
        $TRACK[0]--;
    } else { // entering section
        $TRACK[0]++;
        $TRACK[$TRACK[0]] = $level;
        for ($foo = 1; $foo < $TRACK[0]; $foo++) {
            $DEBUGSTRING .= '|';
        }
        $DEBUGSTRING .= '+-/ ';
        if (DEBUG_TIME && $startTime) {
            $DEBUGSTRING .= substr( endTime($startTime), 0, 5).': ';
        }
        if (func_num_args() > 1) {
            $args = func_get_args();
            array_shift($args);
            $parameters = '';
            foreach ($args as $key => $arg) {
                if ($arg == NULL) {
                    $args[$key] = 'NULL';
                } elseif ($arg === TRUE) {
                    $args[$key] = 'TRUE';
                } elseif ($arg === FALSE) {
                    $args[$key] = 'FALSE';
                }
                $parameters .= $args[$key].', ';               
            }
            $parameters = substr($parameters, 0, -2);
        } else {
            $parameters = '';
        }
        $DEBUGSTRING .= $level.'('.$parameters.')<br />';
    }
}

/** DB request tracking */

function dbtrack($SQL= NULL) {
	global $DBTRACKNUM, $DEBUGSTRING, $TRACK;

    if ( SQL == NULL || !DEBUG || !DEBUG_SQL ) 
        return;

	if (DEBUG_SQL) {
        $DBTRACKNUM++;
        if ( DEBUG_TRACE ) {
    		for ($foo = 1; $foo < $TRACK[0]; $foo++) {
	    		$DEBUGSTRING .= '|';
		    }
            $DEBUGSTRING .= '|  ';
        }
		$DEBUGSTRING .= htmlspecialchars($SQL).'<br />';
	}
}


?>

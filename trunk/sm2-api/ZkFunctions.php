<?php

/**
 * Zookeeper: ZkFunctions.php
 * Copyright (c) 2001-2002 The Zookeeper Project Team
 * Licensed under the GNU GPL. For full terms see the file COPfYING.
 *
 * $Id$
 */

/**
 * zkCheckName
 *
 * This function checks to make sure a name given is valid. This is done so
 * funny paths can't accidentally slip through and create security holes.
 *
 * @param string $name the name to check for validity
 * @return bool indicates whether or not the name is valid
 */
function zkCheckName($name) {
    return (preg_match('/^[A-Za-z]([_-]?[A-Za-z0-9])*$/',$name));
}

/**
 * zkGetLogLevel
 *
 * This function takes a log level string and returns the corresponding
 * log level contant value.
 *
 * @param string $level_str the log level string to translate
 * @return integer the log level constant for that string
 */
function zkGetLogLevelConstant($level_str) {
    switch ($level_str) {
        case 'EMERG':   return (ZKLOG_EMERG);
        case 'ALERT':   return (ZKLOG_ALERT);
        case 'CRIT':    return (ZKLOG_CRIT);
        case 'ERR':     return (ZKLOG_ERR);
        case 'WARNING': return (ZKLOG_WARNING);
        case 'NOTICE':  return (ZKLOG_NOTICE);
        case 'INFO':    return (ZKLOG_INFO);
        case 'DEBUG':   return (ZKLOG_DEBUG);
        case 'UNDEF':   return (ZKLOG_UNDEF);
        case 'UNKNOWN':
        default:        return (ZKLOG_UNKNOWN);
    }   
}

/**
 * zkGetLogLevel
 *
 * This function takes a log level string and returns the corresponding
 * log level contant value.
 *
 * @param string $level_str the log level string to translate
 * @return integer the log level constant for that string
 */
function zkGetLogLevelString($level_str) {
    switch ($level_str) {
        case ZKLOG_EMERG:   return ('EMERG');
        case ZKLOG_ALERT:   return ('ALERT');
        case ZKLOG_CRIT:    return ('CRIT');
        case ZKLOG_ERR:     return ('ERR');
        case ZKLOG_WARNING: return ('WARNING');
        case ZKLOG_NOTICE:  return ('NOTICE');
        case ZKLOG_INFO:    return ('INFO');
        case ZKLOG_DEBUG:   return ('DEBUG');
        case ZKLOG_UNDEF:   return ('UNDEF');
        case ZKLOG_UNKNOWN:          
        default:        return ('UNKNOWN');
    }   
}

?>

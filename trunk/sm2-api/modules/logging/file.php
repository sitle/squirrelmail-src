<?php

/*
 * Zookeeper
 * Copyright (c) 2001 Paul Joseph Thompson
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * $Id$
 */
 
 /**
 * ZkImp_logging_file
 *
 * The ZkImp_logging_file class provides basic file logging for the Zookeeper
 * logging service. Then logs it creates are similar in format to the standard
 * apache error log.
 */
class ZkImp_logging_module {
    var $logfile;
    
    /**
     * Create a new ZkImp_logging_module with the given options.
     *
     * @param array $options an associative array that can pass options
     *                       to the module
     */
    function ZkImp_logging_module($options) {
        $this->logfile = $logfile;
        
        /* EXPLANATION FOR FIXMEs BELOW
         *
         * We want the creation of this module to fail if the logfile
         * specified is bad for some reason. Therefore, we do some
         * simple checks here.
         */
        
        /* Perform some checks on the logfile. */
        if (file_exists($this->logfile)) {
            if (!is_writeable($this->logfile)) {
                /* FIXME: THIS IS AN ERROR.                 */
                /* RECODE TO RETURN A ZOOKEEPER ERROR HERE. */
            }
        } else {
            if (!touch($this->logfile)) {
                /* FIXME: THIS IS AN ERROR.                 */
                /* RECODE TO RETURN A ZOOKEEPER ERROR HERE. */
            }
        }
    }
    
    /**
     * Log a message to this logging module.
     *
     * @param string  $message message to log to the logs
     * @param integer $errno   error number (if applicable)
     * @param string  $cat     category for this log message
     * @param string  $subcat  subcategory for this mog message
     * @param integer $level   log level for this log message
     */
    function logMessage($message, $errno, $cat, $subcat, $level) {
        /***********************************************************/
        /*** Before we do anything, generate the message string. ***/
        /***********************************************************/
        
        /* Build the date part of the string, in apache error log style. */
        $msgstr = date('[D M ');
        $msgstr .= str_pad(date('j'), 2, ' ', STR_PAD_LEFT);
        $msgstr .= str_pad(date('g:i:s Y]', 14, ' ', STR_PAD_LEFT));
        
        /* Add the log level to the string. */
        $msgstr .= ' [' . zkGetLogLevelString($level) . '] ';
        
        /* Toss the category, subcategory, and error number on there. */
        $msgstr .= "$cat/$subcat " (isset($errno) ? $errno : '') . ' - ';
        
        /* Last, add the actual message. */
        $msgstr .= $message;
        
        /*************************************************/
        /*** Attempt to write the message to the file. ***/
        /*************************************************/
        
        /* Log the message to the file. */
        $logptr = fopen($this->logfile);
        
        /* Check to make sure the opening of our logfile suceeded. */
        if (!$logptr) {
            /* FIXME: THIS IS AN ERROR.                 */
            /* RECODE TO RETURN A ZOOKEEPER ERROR HERE. */
        }
        
        /* Attempt to write the message to the logfile. */
        if (!fwrite($logptr, $msgstr)) {
            /* FIXME: THIS IS AN ERROR.                 */
            /* RECODE TO RETURN A ZOOKEEPER ERROR HERE. */
        }
        
        /* Finally, close the logfile. */
        if (!fclose($logptr)) {
            /* FIXME: THIS IS AN ERROR.                 */
            /* RECODE TO RETURN A ZOOKEEPER ERROR HERE. */
        }
        
        /*
    }
}

?>

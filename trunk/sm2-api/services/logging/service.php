<?php

/*
 * Squirrelmail2 API
 * Copyright (c) 2001 Th Squirrelmail Foundation
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * $Id$
 */

/**
 * ZkSvc_logging
 *
 * The ZkSvc_logging class handles logging for a web application.
 */
class ZkSvc_logging {
    var $logs;

    /**
     * Create a new ZkSvc_logging object with the given module.
     *
     * @param array  $options options to pass to ZkAuthHandler
     * @param object $logmod  module to use for logging
     */
    function ZkSvc_logging($options) {
        /* Do nothing, at this point. */
        }
        
    /**
     * Return the name of this service.
     *
     * @return string the name of this service
     */
    function getServiceName() {
        return ('logging');
    }

    /**
     * Add a Zookeeper log module to the list of loaded log moduless for
     * this Zookeeper logging service.
     *
     * @param object $logmod module to register with this logging service
     */
    function loadModule(&$logmod, $options) {
        /* Build the log array for this new module. */
        $logarr['module'] =& $logmod);
        $logarr['min'] = (isset($options['min'] ? $options['min'] : -1000000);
        $logarr['max'] = (isset($options['max'] ? $options['max'] : 1000000);
        
        /* Add it to the main log array. */
        $this->logs[] =& $logmod;
    }
        
    /**
     * Log a message to the loaded log modules.
     *
     * @param string  $message message to log to the logs
     * @param integer $errno   error number (if applicable)
     * @param string  $cat     category for this log message
     * @param string  $subcat  subcategory for this mog message
     * @param integer $level   log level for this log message
     */
    function logMessage
    ($message, $errno, $cat, $subcat, $level = ZKLOG_UNKNOWN) {
        foreach ($this->logs as $log) {
            /* Make sure this message is within this logs range. */
            if (($level >= $log['min']) && ($level <= $log['max'])) {
                $logmod->logMessage($message, $errno, $cat, $subcat, $level);
            }
        }
     }
    
    /**
     * Log a Zookeeper error to the loaded log modules.
     *
     * @param object $error the Zookeeper error to log to the logs
     */
    function logError($error) {
        $this->logMessage(
            $error->message,
            $error->errno,
            $error->cat,
            $error->subcat,
            $error->level
        );
    }
}

?>

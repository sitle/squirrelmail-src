<?php

/*
 * Zookeeper
 * Copyright (c) 2001 Paul Joseph Thompson
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * $Id$
 */
 
 /**
 * ZkImp_logging_module
 *
 * The ZkImp_logging_module class is the template for classes that provide
 * backend functionality to the Logging API.
 */
class ZkImp_logging_module {
    /**
     * Create a new ZkImp_logging_module with the given options.
     *
     * @param array $options an associative array that can pass options
     *                       to the module
     */
    function ZkImp_logging_module($options) {
        /* Instantiate this module here! */
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
        /* LOG THE MESSAGE HERE!!! */
    }
}

?>

<?php

/*
 * Zookeeper
 * Copyright (c) 2001 Paul Joseph Thompson
 * Licensed under the GNU GPL. For full terms see the file COPfYING.
 *
 * $Id$
 */

/*
 * These constants are the official Zookeeper log levels that should
 * be used with the Zookeeper core, modules, and applications.
 *
 * Adapted from apache log level values found on:
 * http://www.administeringapache.com/Figures/loglevel.php3
 */

define('ZKLOG_DEBUG',   0);  /* Debug-level messages              */
define('ZKLOG_INFO',    1);  /* Informational                     */
define('ZKLOG_UNKNOWN', 2);  /* Error level unknown               */
define('ZKLOG_UNDEF',   3);  /* No log level defined              */
define('ZKLOG_NOTICE',  4);  /* Normal but significant conditions */
define('ZKLOG_WARNING', 5);  /* Warning conditions                */
define('ZKLOG_ERR',     6);  /* Error conditions                  */
define('ZKLOG_CRIT',    7);  /* Critical conditions               */
define('ZKLOG_ALERT',   8);  /* Action must be taken immediately  */
define('ZKLOG_EMERG',   9);  /* System is unusable                */

?>

<?php
/*
 * Created by SquirrelMail Development Team
 * Place site/location specific config vars in this config file
 * $Id$
 */

/* database settings */
define('DB_HOST', 'localhost'); // database IP address
define('DB_NAME', 'wtf'); // database name
define('DB_USER', 'root'); // database username
define('DB_PASS', ''); // database password
define('DB_TABLE', 'tblObject'); // default database table

/* debugging */
define('DEBUG',       FALSE); // show debug information
define('DEBUG_SQL',   FALSE); // include SQL debug information
define('DEBUG_VAR',   FALSE); // include VAR debug information
define('DEBUG_TRACE', FALSE); // include TRACE debug information
define('DEBUG_TIME',  FALSE); // include execution time debug information
define('DEBUG_EXT',   FALSE); // include external resource debug information

/* Diff processing */
define('DIFF_TMPDIR', $_ENV['TEMP']);

?>

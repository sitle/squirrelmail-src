<?php

define('PATH','../foowd/lib/');
define('SM_PATH', 'lib/');
define('CFG_PATH','config/');
define('TEMPLATE_PATH','templates');

/*
 * Database Settings
 * -------------------------------------------------------------
 * If database settings are not given explicitly to the FOOWD
 * object at creation time, these constants are used to define
 * the database connection.
 * -------------------------------------------------------------
 */

define('DB_LAYER', 'mysql');      // which Foowd storage layer to use (mysql|odbc|dbx)
define('DB_HOST',  '');   // database IP address
define('DB_NAME',  '');      // database name
define('DB_USER',  '');      // database username
define('DB_PASS',  '');      // database password
define('DB_TABLE', 'tblobject');  // default database table

/*
 * Pre-Class-Load Configuration
 * -------------------------------------------------------------
 * Load configuration files that should proceed loading of classes
 * -------------------------------------------------------------
 */
require(CFG_PATH.'config.constants.php');     // Diff/History/REGEX/Object defaults

require(PATH.'lib.php');                      // FOOWD lib
require(SM_PATH.'smdoc.env.foowd.php');       // environment class
require(SM_PATH.'smdoc.env.debug.php');       // debug class

require(PATH.'env.database.php');             // FOOWD database base class

require(CFG_PATH.'config.groups.php');        // Group/Permission

/*
 * FOOWD System Files
 * -------------------------------------------------------------
 * Classes that are not required by your application can safely
 * be removed from being loaded here. Include your own class
 * definitions here.
 * -------------------------------------------------------------
 */
require(PATH.'input.cookie.php');
require(PATH.'input.querystring.php');
require(SM_PATH.'smdoc.input.session.php');
require(SM_PATH.'smdoc.input.form.php');

require(PATH.'class.object.php');
require(SM_PATH.'smdoc.class.error.php');     // error handling 
require(SM_PATH.'smdoc.class.user.php');      // modified user
require(PATH.'class.workspace.php');
require(PATH.'class.text.plain.php');
require(PATH.'class.text.html.php');

require(SM_PATH.'smdoc.class.group.php');        // Static group management class
require(SM_PATH.'smdoc.class.external.php');     // external tools
require(SM_PATH.'smdoc.class.translation.php');  // translation

require(SM_PATH.'smdoc.extern.siteindex.php');
require(SM_PATH.'smdoc.extern.changes.php');

/*
 * Post-Class-Load Configuration
 * -------------------------------------------------------------
 * Load configuration files that require definition of classes
 * -------------------------------------------------------------
 */
require(CFG_PATH.'config.cache.php');       // Object Cache settings
/*
 * Session initialization
 * -------------------------------------------------------------
 */
ini_set('session.name' , getConstOrDefault('FOOWD_SESSION_ID', 'SMDOC_SESSID'));
session_start();

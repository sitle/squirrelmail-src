<?php

define('DATETIME_FORMAT', 'Y/m/d h:ia'); // formatting string to format dates

/*
 * Database Settings 
 * -------------------------------------------------------------
 * If database settings are not given explicitly to the FOOWD
 * object at creation time, these constants are used to define
 * the database connection.
 * -------------------------------------------------------------
 */
define('DATABASE', 'mysql');      // which storage medium to use (mysql|flatfile)
require(PATH.'lib/db.mysql.php'); // MySQL DB access

define('DB_HOST', 'localhost');   // database IP address
define('DB_NAME', 'smdocs');      // database name
define('DB_USER', 'smdocs');      // database username
define('DB_PASS', 'passwd');      // database password
define('DB_TABLE', 'tblobject');  // default database table

/*
 * Pre-Class-Load Configuration
 * -------------------------------------------------------------
 * Load configuration files that should proceed loading of classes
 * -------------------------------------------------------------
 */
require(PATH.'lib/lib.php');                   // FOOWD lib
require(PATH.'lib/class.foowd.php');           // FOOWD environment class
require(PATH.'lib/smdoc.lib.error.php');       // Error Handling
require(PATH.'config/config.groups.php');      // Group/Permission
require(PATH.'config/config.constants.php');   // Diff/History/REGEX/Object defaults

/*
 * FOOWD System Files 
 * -------------------------------------------------------------
 * Classes that are not required by your application can safely
 * be removed from being loaded here. Include your own class
 * definitions here.
 * -------------------------------------------------------------
 */
require(PATH.'lib/smdoc.class.template.php');  // Basic template class
require(PATH.'lib/smdoc.class.debug.php');     // debug display/information
require(PATH.'lib/smdoc.class.group.php');     // Static group management class

require(PATH.'lib/input.cookie.php');
require(PATH.'lib/input.querystring.php');
require(PATH.'lib/smdoc.input.session.php');

include(PATH.'lib/input.checkbox.php');
include(PATH.'lib/input.dropdown.php');
include(PATH.'lib/input.form.php');
include(PATH.'lib/input.radio.php');
include(PATH.'lib/input.table.php');
include(PATH.'lib/input.textarea.php');
include(PATH.'lib/input.textarray.php');
include(PATH.'lib/input.textbox.php');

require(PATH.'lib/class.object.php');
require(PATH.'lib/class.user.php');
require(PATH.'lib/class.text.plain.php');
require(PATH.'lib/class.text.html.php');
require(PATH.'lib/class.workspace.php');  // Have workspaces available
require(PATH.'lib/class.group.php');      // Use user group class

require(PATH.'lib/smdoc.class.external.php');   
require(PATH.'lib/smdoc.class.translation.php');

require(PATH.'extern/smdoc.siteindex.php');
require(PATH.'extern/smdoc.changes.php');
require(PATH.'extern/smdoc.tools.php');

/*
 * Post-Class-Load Configuration
 * -------------------------------------------------------------
 * Load configuration files that require definition of classes
 * -------------------------------------------------------------
 */
require(PATH.'config/config.cache.php');       // Object Cache settings
require(PATH.'config/config.display.php');     // Template configuration
/*
 * Session initialization
 * -------------------------------------------------------------
 */
ini_set('session.name' , getConstOrDefault('FOOWD_SESSION_ID', 'SMDOC_SESSID'));
session_start();

<?php
/*
config.default.php
Default FOOWD Settings
*/

/*
 * Modified by SquirrelMail Development Team
 * $Id$
 */

define('FOOWD_VERSION', '0.8');

/** Object Settings 
 *
 * These settings define the default object access parameters
 * if one or more are missing from a call to one of the object
 * retrieve FOOWD object member functions.
 */
define('DEFAULT_OBJECTID', 936075699); // default object to retrieve
define('DEFAULT_CLASSID', NULL);       // classid of default class type to retrieve
define('DEFAULT_METHOD', 'view');      // default method to call on object
define('ALLOW_DULICATE_TITLE', TRUE);  // allow objects to have the same name

/** Meta Data Constants 
 * 
 * Regular expression that various items must match to be valid.
 * The system has not been tested with different values to those
 * show below, changing these values may have unexpected results.
 */
define('REGEX_TITLE', '/^[a-zA-Z0-9-_ ]{1,32}$/'); // object title
define('REGEX_ID', '/^[0-9-]{1,10}$/'); // object id
define('REGEX_DATETIME', '/^[0-9-]{1,10}$/'); // datetime field
define('REGEX_PASSWORD', '/^[A-Za-z0-9]{1,32}$/'); // user password
define('REGEX_EMAIL', '/^[A-Za-z0-9._-]+@[A-Za-z0-9._-]+\.[A-Za-z]{1,4}$/'); // email address

/* Display Settings */
define('DATETIME_FORMAT', 'D jS F Y \a\t h:ia'); // formatting string to format dates

/** Security Settings 
 *
 * Choose your user auth type, note that if you are using IIS
 * with PHP as a CGI module, HTTP authentication will not work
 * and you are stuck with using Cookies.
 * The ANON_GOD constant allows you to !temporarily! give the
 * anonymous user godlike powers, this is useful for gettng an
 * empty system off the ground. Set it back to FALSE before
 * your application goes live.
 */
define('ANON_GOD', FALSE); // anonymous user is a god
define('AUTH_TYPE', 'cookie'); // user auth type, either 'http' or 'cookie'

/* History Settings */
define('DESTROY_OLDER_THAN', '-1 month'); // time to keep an archive version of an object
define('MINIMUM_NUMBER_OF_ARCHIVED_VERSIONS', 3); // number of archived versions of an object to keep

/** Diff Settings 
 *
 * To enable diffs in the foowd_text classes you need to specify
 * the path and filename of the diff engine to use. The two
 * regular expressions match added and removed lines from the
 * diff output.
 */
define('DIFF_COMMAND', 'diff -u3');
define('DIFF_ADD_REGEX', '/^\+(.*)/');
define('DIFF_MINUS_REGEX', '/^\-(.*)/');

/** Load FOOWD System Files 
 * 
 * Classes that are not required by your application can safely
 * be removed from being loaded here. Include your own class
 * definitions here.
 */
require(PATH.'lib/lib.php');         // FOOWD lib
require(PATH.'lib/track.php');       // DEBUG lib
require(PATH.'lib/db.mysql.php');    // MySQL DB access
require(PATH.'lib/class.foowd.php'); // FOOWD environment class
// input objects
include(PATH.'lib/input.querystring.php');
include(PATH.'lib/input.cookie.php');
include(PATH.'lib/input.form.php');
include(PATH.'lib/input.textbox.php');
include(PATH.'lib/input.textarea.php');
include(PATH.'lib/input.checkbox.php');
include(PATH.'lib/input.radio.php');
include(PATH.'lib/input.dropdown.php');
include(PATH.'lib/input.textarray.php');
// FOOWD system classes
require(PATH.'lib/class.object.php');
require(PATH.'lib/class.user.php');
require(PATH.'lib/class.workspace.php');
require(PATH.'lib/class.external.php');
include(PATH.'lib/class.text.plain.php');
include(PATH.'lib/class.text.html.php');

// EXTERNAL classes
include(PATH.'extern/foowd.docs.php');

/** Cache Settings 
 *
 * To enable caching for a particular object method, create
 * an element in the $foowd_cache array with the value of the
 * number of seconds before flushing the cache item where the
 * 1st index is the objectid, 2nd the classid, 3rd the method.
 * Cached versions do not take users into account, so any method
 * that is user specific should not be cached.
 * Note that if you are using IIS with PHP as a CGI module,
 * caching will not work.
 */
//$foowd_cache[-1570786539][1158898744]['view'] = 3600; // cache view method of object -1570786539, refresh cache every hour

/** External Resource Settings 
 * 
 * To include external pages, create an element in the 
 * $EXTERNAL_RESOURCES array with the following information:
 *  
 *  $EXTERNAL_RESOURCES[<objectid>]['func'] - name of function to call to retrieve content
 *  $EXTERNAL_RESOURCES[<objectid>]['title'] - title of external resource
 *  $EXTERNAL_RESOURCES[<objectid>]['cvs_info'] - CVS file id string ($Id$), 
 *                                              for last modified and version (optional)
 *  where <objectid> is the crc32 result of func.
 *  Can set this here, or in the definition of the external resource
 */

?>

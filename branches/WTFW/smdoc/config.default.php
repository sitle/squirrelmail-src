<?php
/*
config.default.php
Default FOOWD Settings
*/

/* Database Settings */
/*
If database settings are not given explicitly to the FOOWD
object at creation time, these constants are used to define
the database connection.
*/
define('DATABASE', 'flatfile'); // which storage medium to use (mysql|flatfile)
switch (DATABASE) {
case 'mysql':
	define('DB_HOST', 'localhost'); // database address
	define('DB_NAME', 'foowd'); // database name
	define('DB_USER', 'root'); // database username
	define('DB_PASS', ''); // database password
	define('DB_TABLE', 'tblObject'); // default database table
	require(PATH.'lib/db.mysql.php'); // MySQL DB access
	break;
case 'flatfile':
	define('DB_HOST', substr($_SERVER['SCRIPT_FILENAME'], 0, strrpos($_SERVER['SCRIPT_FILENAME'], '/')).'/flatfile/'); // database address
	require(PATH.'lib/db.file.php'); // Flat file access
	break;
}

/* Debugging */
/*
Uncomment to enable debugging output.
*/
//define('DEBUG', TRUE);

/* Object Settings */
/*
These settings define the default object access parameters
if one or more are missing from a call to one of the object
retrieve FOOWD object member functions.
*/
define('DEFAULT_OBJECTID', 936075699); // default object to retrieve
define('DEFAULT_CLASSID', NULL); // classid of default class type to retrieve
define('DEFAULT_METHOD', 'view'); // default method to call on object
define('ALLOW_DULICATE_TITLE', TRUE); // allow objects to have the same name

/* Meta Data Constants */
/*
Regular expression that various items must match to be valid.
The system has not been tested with different values to those
show below, changing these values may have unexpected results.
*/
define('REGEX_TITLE', '/^[a-zA-Z0-9-_ ]{1,32}$/'); // object title
define('REGEX_ID', '/^[0-9-]{1,11}$/'); // object id
define('REGEX_DATETIME', '/^[0-9-]{1,10}$/'); // datetime field
define('REGEX_PASSWORD', '/^[A-Za-z0-9]{1,32}$/'); // user password
define('REGEX_EMAIL', '/^[A-Za-z0-9._-]+@[A-Za-z0-9._-]+\.[A-Za-z]{1,4}$/'); // email address

/* Display Settings */
define('DATETIME_FORMAT', 'D jS F Y \a\t h:ia'); // formatting string to format dates

/* Security Settings */
/*
Choose your user auth type, note that if you are using IIS
with PHP as a CGI module, HTTP authentication will not work
and you are stuck with using Cookies.
The ANON_GOD constant allows you to !temporarily! give the
anonymous user godlike powers, this is useful for gettng an
empty system off the ground. Set it back to FALSE before
your application goes live.
If you are using cookie authentication, it may be important
that you set the cookie constants to your domain and path.
*/
define('ANON_GOD', FALSE); // anonymous user is a god
define('AUTH_TYPE', 'cookie'); // user auth type, either 'http' or 'cookie'
define('COOKIE_EXPIRE', 31536000);
define('COOKIE_PATH', '/');
define('COOKIE_DOMAIN', '');
//define('AUTH_IP_127.0.0.1', 'Root'); // this line will force you to be logged in as Root if coming from IP 127.0.0.1

/* History Settings */
define('DESTROY_OLDER_THAN', '-1 month'); // time to keep an archive version of an object
define('MINIMUM_NUMBER_OF_ARCHIVED_VERSIONS', 3); // number of archived versions of an object to keep

/* Cache Settings */
/*
To enable caching for a particular object method, create
an element in the $foowd_cache array with the value of the
number of seconds before flushing the cache item where the
1st index is the objectid, 2nd the classid, 3rd the method.
Cached versions do not take users into account, so any method
that is user specific should not be cached.
Note that if you are using IIS with PHP as a CGI module,
caching will not work.
*/
//$foowd_cache[-1570786539][1158898744]['view'] = 3600; // cache view method of object -1570786539, refresh cache every hour

/* Diff Settings */
/*
To enable diffs in the foowd_text classes you need to specify
the path and filename of the diff engine to use. The two
regular expressions match added and removed lines from the
diff output.
*/
define('DIFF_COMMAND', 'diff -u3');
define('DIFF_ADD_REGEX', '/^\+(.*)/');
define('DIFF_MINUS_REGEX', '/^\-(.*)/');

/* Load FOOWD System Files */
/*
Classes that are not required by your application can safely
be removed from being loaded here. Include your own class
definitions here.
*/
require(PATH.'lib/lib.php'); // FOOWD lib
require(PATH.'lib/class.foowd.php'); // FOOWD environment class
// input objects
require(PATH.'lib/input.checkbox.php');
require(PATH.'lib/input.cookie.php');
require(PATH.'lib/input.dropdown.php');
require(PATH.'lib/input.form.php');
require(PATH.'lib/input.querystring.php');
require(PATH.'lib/input.radio.php');
require(PATH.'lib/input.textarea.php');
require(PATH.'lib/input.textarray.php');
require(PATH.'lib/input.textbox.php');
// FOOWD classes
require(PATH.'lib/class.object.php');
require(PATH.'lib/class.user.php');
require(PATH.'lib/class.workspace.php'); // Have workspaces available
//require(PATH.'lib/class.group.php'); // Use user group class
require(PATH.'lib/class.text.plain.php');
require(PATH.'lib/class.text.html.php');
// Example classes
//require(PATH.'examples/class.hello.php'); // Hello World example
//require(PATH.'examples/class.ubb.php'); // UBB code example

?>
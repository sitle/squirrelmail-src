<?php
/*
Copyright 2002, Paul James

This file is part of the Wiki Type Framework (WTF).

WTF is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

WTF is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with WTF; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/*
wtf.config.php
WTF Configuration
*/

/*
 * Modified by SquirrelMail Development Team
 * $Id$
 */

/* site defaults */
define('DEFAULTPAGENAME', 'SquirrelMail'); // default page name

/* content settings */
define('MAXTITLELENGTH', 50); // maximum length of title allowed (DB field must support length)
define('MAXPASSWORDLENGTH', 16); // maximum length of user password allowed
define('MAXCONTENTLENGTH', 65536); // maximum length of content allowed
define('ALLOWDUPLICATETITLES', TRUE); // allow two things to have the same title name (users can never have the same title)
define('CONVERTNEWLINESTO', '<br />'); // replace new lines with given tag in non-XML content blocks (set to FALSE to disable)
define('QUICKFORMATS', TRUE); // allow quick formatting
define('SPLITLONGWORDS', FALSE); // split long non-breaking text to MAXCONTENTWIDTH
define('MAXCONTENTWIDTH', 45); // maximum width of content body

/* user settings */
define('HTTPAUTH', FALSE); // whether to use HTTP authentication instead of the login form
define('HTTPAUTHREALM', 'Wiki Type Framework'); // realm for HTTP authentication
define('USECOOKIE', TRUE); // whether to use cookies for keeping user state between requests (if FALSE you must use HTTPAUTH to keep state)
define('COOKIELIFE', 2592000); // in seconds
define('COOKIEPATH', '');
define('COOKIEDOMAIN', '');
define('PASSWORDSALT', 'wooyay'); // salt for added to password before encrypting for extra security (if you change this existing users passwords will become useless)
define('USERTIMEOUT', 600); // seconds since last request until user is declared as having left the site.
define('USERTHINGBODY', '<p>This space intentionally left blank.</p>');

/* output format of date stamps */
define('DATEFORMAT', 'D jS F Y \a\t h:ia');
define('SHORTDATEFORMAT', 'jS F Y');

/* GZIP output compression (requires zlib) */
define('GZIPOUTPUT', FALSE); // compress output using gzip encoding if user agent accepts it
define('GZIPLEVEL', 9); // gzip copression level (0-9)

/* diff engine */
define('DIFFCOMMAND', '/bin/diff'); // full path of diff command (set to FALSE to disable diffs, on Windows use the Cygwin diff command)
define('DIFFINLINE', TRUE);

/* history settinngs */
define('DESTROYOLDERTHAN', '-1 month'); // time to keep an archive version of a thing lives for (default = 1 month)
define('NUMBEROFARCHIVEVERSIONSTOKEEP', 1); // number of archived versions of a thing to keep (default = 1 version)

/* special user groups */
define('EVERYONE', 'Everyone'); // group of everyone (new users are placed in this group)
define('GODS', 'Gods'); // group of gods
define('EDITORS', 'Editors'); // group of editors
define('AUTHOR', 'Author'); // group that automagically contains thing author only (if a user belongs to this group, they can still access all things in this group)

define('CREATORS', EVERYONE); // group that can create content things
define('GROUPS', GODS); // group that can create, remove, and alter user groups

/* default security levels */
define('DEFAULTVIEWGROUP', EVERYONE); // default view group
define('DEFAULTEDITGROUP', EVERYONE); // default edit group
define('DEFAULTDELETEGROUP', EDITORS); // default delete group
define('DEFAULTADMINGROUP', GODS); // default admin group
define('USERVIEWGROUP', EVERYONE); // view group for user things
define('USEREDITGROUP', AUTHOR); // edit group for user things
define('USERDELETEGROUP', GODS); // delete group for user things
define('USERADMINGROUP', GODS); // default admin group

/* URI settings */
define('THING', 'thing'); // thing name in querystring
define('THINGID', 'thingid'); // thing id in querystring
define('THINGURI', FILENAME.'?'.THING.'='); // full prefix for thing name in querystring
define('THINGIDURI', FILENAME.'?'.THINGID.'='); // full prefix for thing id in querystring

/* regex's (PCRE's but do not enclose in delimiters due to them sometimes being concatinated together, '/' is always used as delmiter) */
define('TITLEMATCHREGEX', '[-\'?!£\$%\/A-Za-z0-9 ]+');
define('PASSWORDMATCHREGEX', '[-\'?!£\$%\/A-Za-z0-9 ]+');
define('URIMATCHREGEX', '(http|ftp)://[-a-zA-Z0-9/._?=]*'); // URIs must match this regex to be valid
define('EMAILMATCHREGEX', '[\w.-]+@[\w.-]+\.[\w-]+'); // e-mail addresses must match this regex to be valid

/* hard thing settings */
define('HARDTHINGAUTHORNAME', 'Webmaster'); // username to own hard things
define('HARDTHINGAUTHORUSERID', -1672260811); // userid to own hard things
define('HARDTHINGAUTHORHOMEID', -1672260811); // homeid to own hard things
define('HARDTHINGCREATED', '2002-01-01'); // date of hard things creation

/* thing id's */
define('ANONYMOUSUSERID', -1672260811); // userid of the anonymous user
define('ROOTUSERID', 385153371); // userid of the root user
define('USERCLASSID', -1919691191); // classid of user class
define('NOTHINGFOUNDID', -941827936); // thingid of thing to display if nothing is found for a thing request

/* anonymous user settings */
define('USEHOSTNAMEFORANONYMOUSUSER', FALSE); // use the users hostname for anonymous username
define('USEHOSTIPFORANONYMOUSUSER', TRUE); // use the users ip for anonymous username
define('ANONYMOUSUSERNAME', 'Anonymous User'); // name for anonymous user

/* formatting settings */
define('DEFAULTSKIN', 'sqmail'); // default skin (used when new users are generated)
$SKIN['default'] = 'formatting/default/default.php';
$SKIN['sqmail'] = 'formatting/sqmail/default.php';
$SKIN['xml'] = 'formatting/xml/xml.php';
//$SKINBROWSER['/googlebot/'] = 'default'; // give certain formatting to a certain user agent regex match

/* quick format regex and replace strings */
$QUICKFORMAT = array(
// convert [] to <a> tag
	array('regex' => '/\[('.TITLEMATCHREGEX.')\]/', 'replace' => '<a href="'.THINGURI.'\\1">\\1</a>'),
// convert [|] to <a> tag
	array('regex' => '/\[('.TITLEMATCHREGEX.')\|('.TITLEMATCHREGEX.')\]/', 'replace' => '<a href="'.THINGURI.'\\1">\\2</a>'),
// convert [#] to <a> tag
	array('regex' => '/\[('.TITLEMATCHREGEX.')#('.TITLEMATCHREGEX.')\]/', 'replace' => '<a href="'.THINGURI.'\\1#\\2">\\1</a>'),
// convert [#|] to <a> tag
	array('regex' => '/\[('.TITLEMATCHREGEX.')#('.TITLEMATCHREGEX.')\|('.TITLEMATCHREGEX.')\]/', 'replace' => '<a href="'.THINGURI.'\\1#\\2">\\3</a>'),
// convert URL to <a> tag
	array('regex' => '@('.URIMATCHREGEX.')@', 'replace' => '<a href="\\1">\\1</a>'),
// convert [e2:] to Everything2 <a> tag
	array('regex' => '/\[e2:('.TITLEMATCHREGEX.')\]/', 'replace' => '<a href="http://www.everything2.com?node=\\1">\\1</a>'),
// convert [wiki:] to WikiWikiWeb <a> tag
	array('regex' => '/\[wiki:([a-zA-Z0-9]*)\]/', 'replace' => '<a href="http://c2.com/cgi/wiki?\\1">\\1</a>'),
// convert [phpwiki:] to PHPWiki <a> tag
	array('regex' => '/\[phpwiki:([a-zA-Z0-9]*)\]/', 'replace' => '<a href="http://phpwiki.sourceforge.net/phpwiki/\\1">\\1</a>'),
// convert [zwiki:] to ZWiki <a> tag
	array('regex' => '/\[zwiki:([a-zA-Z0-9]*)\]/', 'replace' => '<a href="http://zwiki.org/\\1">\\1</a>'),
// convert [openwiki:] to OpenWiki <a> tag
	array('regex' => '/\[openwiki:([a-zA-Z0-9]*)\]/', 'replace' => '<a href="http://openwiki.com/ow.asp?\\1">\\1</a>'),

// escaped []'s
	array('regex' => '/\\\\\[/', 'replace' => '['),
	array('regex' => '/\\\\\]/', 'replace' => ']')
);

/* tags in which not to apply quicklinks */
$NOQUICKFORMATTAG[] = 'a';

/* tags that can be used in XML content blocks ('tag' => 'space separated attribute name list') */
$TAGS['br'] = '';
$TAGS['p'] = '';
$TAGS['a'] = 'href';
$TAGS['b'] = '';
$TAGS['i'] = '';
$TAGS['u'] = '';
$TAGS['hr'] = '';
$TAGS['ul'] = '';
$TAGS['ol'] = '';
$TAGS['li'] = '';
$TAGS['title'] = 'anchor';
$TAGS['subtitle'] = 'anchor';
$TAGS['question'] = '';
$TAGS['answer'] = '';
$TAGS['quote'] = '';
$TAGS['code'] = '';

/* tag permissions */
//$TAGSGROUP['title'] = EDITORS;

/* XML entities */
$ENTITY['alpha'] = '&#945;';
$ENTITY['beta'] = '&#946;';
$ENTITY['gamma'] = '&#947;';
$ENTITY['euro'] = '&#8364;';

/* database */
if (DATABASE == 'mysql') include(PATH.'wtf.mysql.php'); // mysql functions
if (DATABASE == 'pgsql') include(PATH.'wtf.pgsql.php'); // postgres functions
if (DATABASE == 'mssql') include(PATH.'wtf.mssql.php'); // sql server functions

/* load classes */
include(PATH.'wtf.class.wtf.php');
include(PATH.'wtf.class.thing.php');
include(PATH.'wtf.class.definition.php');
include(PATH.'wtf.class.content.php');
include(PATH.'wtf.class.hardthing.php');
include(PATH.'wtf.class.user.php');
include(PATH.'wtf.class.workspace.php');

/* load extensions */
include(PATH.'wiki.config.php');
include(PATH.'file.config.php'); // uncomment to load file extension

include(PATH.'sqmail.wikimenu.php');

/* wtf functions */
include(PATH.'wtf.func.php');
include(PATH.'wtf.render.php');
include(PATH.'wtf.validate.php');
include(PATH.'wtf.processcontent.php');
include(PATH.'wtf.defaults.php');
include(PATH.'wtf.wikimenu.php');

/* load things */
include(PATH.'wtf.thing.content.php');
include(PATH.'wtf.thing.definition.php');
include(PATH.'wtf.thing.workspace.php');
include(PATH.'wtf.thing.search.php');

/* load PIs */
include(PATH.'wtf.pi.php.php');
include(PATH.'wtf.pi.include.php');

/* load PPTags (pre-processed tags) */
include(PATH.'wtf.tag.image.php');
?>

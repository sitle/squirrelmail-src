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
wtf.defaults.php
WTF Default Settings
*/

/*** Check PHP Settings ***/
if (PHP_VERSION < '4.0.4') {
	terminal_error('WTF requires PHP version 4.0.4 or above, please upgrade.');
}
if (DATABASE == 'mysql' && !extension_loaded('mysql')) {
	terminal_error('MySQL support is not available, load the MySQL module or use another database module.');
}
if (DATABASE == 'pgsql' && !extension_loaded('pgsql')) {
	terminal_error('PostgreSQL support is not available, load the PostgreSQL module or use another database module.');
}
if (DATABASE == 'mssql' && !extension_loaded('mssql')) {
	terminal_error('MS SQL Server support is not available, load the MS SQL Server module or use another database module.');
}
if (GZIPOUTPUT && !extension_loaded('zlib')) {
	terminal_error('Zlib is not available, load the Zlib module or turn off WTF gzip compression.');
}

/* mirror access */
if (!defined('MIRROR')) define('MIRROR', 0);

/* database settings */
if (!defined('DATABASE')) define('DATABASE', 'mysql');
if (!defined('DBHOST')) define('DBHOST', 'localhost');
if (!defined('DBNAME')) define('DBNAME', 'wtf');
if (!defined('DBUSER')) define('DBUSER', 'root');
if (!defined('DBPASS')) define('DBPASS', '');
if (!defined('OBJECTTABLE')) define('OBJECTTABLE', 'tblObject');
if (!defined('CLASSTABLE')) define('CLASSTABLE', 'tblObject');

/* debugging */
if (!defined('DEBUG')) define('DEBUG', FALSE);
if (!defined('DEBUG_VAR')) define('DEBUG_VAR', TRUE);
if (!defined('DEBUG_TRACE')) define('DEBUG_TRACE', TRUE);
if (!defined('DEBUG_SQL')) define('DEBUG_SQL', TRUE);
if (!defined('DEBUG_TIME')) define('DEBUG_TIME', TRUE);
if (!defined('RENDER')) define('RENDER', TRUE);

/* site defaults */
define('VERSION', '0.20.2');
if (!defined('DEFAULTPAGENAME')) define('DEFAULTPAGENAME', 'Wiki Type Framework');
if (!defined('DEFAULTCLASS')) define('DEFAULTCLASS', 'thing');
if (!defined('COPYRIGHT')) define('COPYRIGHT', 'Copyright 2002 by Paul James');

/* content settings */
if (!defined('MAXTITLELENGTH')) define('MAXTITLELENGTH', 50);
if (!defined('MAXPASSWORDLENGTH')) define('MAXPASSWORDLENGTH', 16);
if (!defined('MAXCONTENTLENGTH')) define('MAXCONTENTLENGTH', 65536);
if (!defined('ALLOWDUPLICATETITLES')) define('ALLOWDUPLICATETITLES', TRUE);
if (!defined('CONVERTNEWLINESTO')) define('CONVERTNEWLINESTO', '<br/>');
if (!defined('QUICKFORMATS')) define('QUICKFORMATS', TRUE);
if (!defined('SPLITLONGWORDS')) define('SPLITLONGWORDS', FALSE);
if (!defined('MAXCONTENTWIDTH')) define('MAXCONTENTWIDTH', 45);
if (!defined('LTENTITYALTERNATE')) define('LTENTITYALTERNATE', '&lt;');
if (!defined('GTENTITYALTERNATE')) define('GTENTITYALTERNATE', '&gt;');
if (!defined('AMPENTITYALTERNATE')) define('AMPENTITYALTERNATE', '&amp;');

/* user settings */
if (!defined('HTTPAUTH')) define('HTTPAUTH', FALSE);
if (!defined('HTTPAUTHREALM')) define('HTTPAUTHREALM', 'Wiki Type Framework');
if (!defined('USECOOKIE')) define('USECOOKIE', TRUE);
if (!defined('COOKIELIFE')) define('COOKIELIFE', 2592000);
if (!defined('COOKIEPATH')) define('COOKIEPATH', '');
if (!defined('COOKIEDOMAIN')) define('COOKIEDOMAIN', '');
if (!defined('PASSWORDSALT')) define('PASSWORDSALT', 'wooyay');
if (!defined('USERTIMEOUT')) define('USERTIMEOUT', 600);
if (!defined('USERTHINGBODY')) define('USERTHINGBODY', '<p>This space intentionally left blank.</p>');

/* output format of date stamps */
if (!defined('DATEFORMAT')) define('DATEFORMAT', 'D jS F Y \a\t h:ia');

/* GZIP output compression (requires zlib) */
if (!defined('GZIPOUTPUT')) define('GZIPOUTPUT', FALSE);
if (!defined('GZIPLEVEL')) define('GZIPLEVEL', 9);

/* diff engine */
if (!defined('DIFFCOMMAND')) define('DIFFCOMMAND', '/bin/diff');
if (!defined('DIFFADDIDREGEX')) define ('DIFFADDIDREGEX', '/^[0-9]+,?[0-9]*a([0-9]+),?([0-9]*)$/');
if (!defined('DIFFMINUSIDREGEX')) define ('DIFFMINUSIDREGEX', '/^[0-9]+,?[0-9]*d([0-9]+),?([0-9]*)$/');
if (!defined('DIFFCHANGEIDREGEX')) define ('DIFFCHANGEIDREGEX', '/^([0-9]+),?([0-9]*)c([0-9]+),?([0-9]*)$/');
if (!defined('DIFFADDREGEX')) define ('DIFFADDREGEX', '/^> (.*)$/');
if (!defined('DIFFMINUSREGEX')) define ('DIFFMINUSREGEX', '/^< (.*)$/');

if (!defined('DIFFINLINE')) define('DIFFINLINE', FALSE);
if (!defined('DIFFINLINECOMMAND')) define('DIFFINLINECOMMAND', DIFFCOMMAND.' -u3');
if (!defined('DIFFINLINEADDREGEX')) define ('DIFFINLINEADDREGEX', '/^\+(.*)/');
if (!defined('DIFFINLINEMINUSREGEX')) define ('DIFFINLINEMINUSREGEX', '/^\-(.*)/');

/* archive settings */
if (!defined('DESTROYOLDERTHAN')) define('DESTROYOLDERTHAN', '-1 month');
if (!defined('NUMBEROFARCHIVEVERSIONSTOKEEP')) define('NUMBEROFARCHIVEVERSIONSTOKEEP', 1);

/* user groups */
if (!defined('EVERYONE')) define('EVERYONE', 'Everyone');
if (!defined('GODS')) define('GODS', 'Gods');
if (!defined('EDITORS')) define('EDITORS', 'Editors');
if (!defined('AUTHOR')) define('AUTHOR', 'Author');
if (!defined('CREATORS')) define('CREATORS', EVERYONE);
if (!defined('GROUPS')) define('GROUPS', GODS);

/* default security levels */
if (!defined('DEFAULTVIEWGROUP')) define('DEFAULTVIEWGROUP', EVERYONE);
if (!defined('DEFAULTEDITGROUP')) define('DEFAULTEDITGROUP', EVERYONE);
if (!defined('DEFAULTDELETEGROUP')) define('DEFAULTDELETEGROUP', EDITORS);
if (!defined('USERVIEWGROUP')) define('USERVIEWGROUP', EVERYONE);
if (!defined('USEREDITGROUP')) define('USEREDITGROUP', AUTHOR);
if (!defined('USERDELETEGROUP')) define('USERDELETEGROUP', GODS);

/* URI settings */
if (!defined('THING')) define('THING', 'thing');
if (!defined('THINGID')) define('THINGID', 'thingid');
if (!defined('FILENAME')) define('FILENAME', '/index.php');
if (!defined('THINGURI')) define('THINGURI', FILENAME.'?'.THING.'=');
if (!defined('THINGIDURI')) define('THINGIDURI', FILENAME.'?'.THINGID.'=');

/* regex's */
if (!defined('TITLEMATCHREGEX')) define('TITLEMATCHREGEX', '[-\'?!£\$%\/A-Za-z0-9 ]+');
if (!defined('PASSWORDMATCHREGEX')) define('PASSWORDMATCHREGEX', '[-\'?!£\$%\/A-Za-z0-9 ]+');
if (!defined('URIMATCHREGEX')) define('URIMATCHREGEX', '(http|ftp)://[\w.-]+\.[a-zA-Z]{2,4}[a-zA-Z0-9]*');
if (!defined('EMAILMATCHREGEX')) define('EMAILMATCHREGEX', '[\w.-]+@[\w.-]+\.[a-zA-Z]{2,4}');

/* hard thing settings */
if (!defined('HARDTHINGAUTHORNAME')) define('HARDTHINGAUTHORNAME', 'Webmaster');
if (!defined('HARDTHINGAUTHORUSERID')) define('HARDTHINGAUTHORUSERID', -1672260811);
if (!defined('HARDTHINGAUTHORHOMEID')) define('HARDTHINGAUTHORHOMEID', -1672260811);
if (!defined('HARDTHINGCREATED')) define('HARDTHINGCREATED', 'the beginning of time');

/* thing id's */
if (!defined('ANONYMOUSUSERID')) define('ANONYMOUSUSERID', -1672260811);
if (!defined('ROOTUSERID')) define('ROOTUSERID', 385153371);
if (!defined('USERCLASSID')) define('USERCLASSID', -1919691191);
if (!defined('NOTHINGFOUNDID')) define('NOTHINGFOUNDID', -941827936);

/* anonymous user settings */
if (!defined('USEHOSTNAMEFORANONYMOUSUSER')) define('USEHOSTNAMEFORANONYMOUSUSER', FALSE);
if (!defined('USEHOSTIPFORANONYMOUSUSER')) define('USEHOSTIPFORANONYMOUSUSER', TRUE);
if (!defined('ANONYMOUSUSERNAME')) define('ANONYMOUSUSERNAME', 'Anonymous User');

/* formatting settings */
if (!defined('DEFAULTSKIN')) define('DEFAULTSKIN', 'default');

?>

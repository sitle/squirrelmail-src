<?php
/**
 * Tests decodeHeader function.
 * @copyright 2006-2009 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id$
 * @package plugins
 * @subpackage test
 */

/** init */
if (file_exists('../../include/init.php')) {
    include_once('../../include/init.php');
    include_once(SM_PATH . 'functions/mime.php');
} else if (file_exists('../../include/validate.php')) {
    //if (!defined('SM_PATH')) define('SM_PATH', '../../');
    define('SM_PATH', '../../');
    include_once(SM_PATH . 'include/validate.php');
    include_once(SM_PATH . 'functions/mime.php');
} else {
    chdir('..');
    include_once('../src/validate.php');
    include_once('../functions/mime.php');
}

displayPageHeader($color,'none');

$header = array("< &  \xC3", // plain text
                '=?iso-8859-1?Q?=3C_&__=C3?=', // Q encoding
                '=?iso-8859-1?B?PCAmICDD?=', // B encoding
                '=?utf-8?Q?=3C_&__=C3=80?=', // Q encoding other charset
                '=?utf-8?B?PCAmICDDgA==?=', // B encoding other charset
);

if (sqGetGlobalVar('lossy',$lossy,SQ_GET)) {
    if ($lossy) {
        $lossy_encoding = true;
    } else {
        if ($default_charset =='utf-8') $default_charset = 'iso-8859-1';
        $lossy_encoding = false;
    }
}

if ($default_charset=='utf-8' || $lossy_encoding) {
    echo '<p><a href="decodeheader.php?lossy=0">test other side</a></p>';
} else {
    echo '<p><a href="decodeheader.php?lossy=1">test other side</a></p>';
}

echo '<p>Default charset: '.$default_charset."<br />\n".
    'Lossy_encoding: '.($lossy_encoding ? 'true' : 'false').'</p>';

echo '<p>Test depends on selected translation (used character set) and lossy_encoding setting.</p>';

echo '<pre>';

/* first decodeHeader test. html chars and spaces */
echo "(MDN) 000:\n html chars are not encoded,\n space is not encoded,\n 8bit chars unmodified\n";
foreach ($header as $test) {
    echo htmlentities(decodeHeader($test,false,false,false));
    echo "\n";
}
echo "--------\n";
echo "(compose) 001:\n html chars are not encoded,\n space is not encoded,\n 8bit chars converted or not (depends on \$lossy_encoding and \$default_charset)\n";
foreach ($header as $test) {
    echo htmlentities(decodeHeader($test,false,false,true));
    echo "\n";
}
echo "--------\n";
echo "010\n";
foreach ($header as $test) {
    echo htmlentities(decodeHeader($test,false,true,false));
    echo "\n";
}
echo "--------\n";
echo "011\n";
foreach ($header as $test) {
    echo htmlentities(decodeHeader($test,false,true,true));
    echo "\n";
}
echo "--------\n";
echo "(download) 100\n";
foreach ($header as $test) {
    echo htmlentities(decodeHeader($test,true,false,false));
    echo "\n";
}
echo "--------\n";
echo "101\n";
foreach ($header as $test) {
    echo htmlentities(decodeHeader($test,true,false,true));
    echo "\n";
}
echo "--------\n";
echo "(default) 110\n";
foreach ($header as $test) {
    echo htmlentities(decodeHeader($test,true,true,false));
    echo "\n";
}
echo "--------\n";
echo "111\n";
foreach ($header as $test) {
    echo htmlentities(decodeHeader($test,true,true,true));
    echo "\n";
}
echo "--------\n";

echo '</pre>';
?>
</body></html>
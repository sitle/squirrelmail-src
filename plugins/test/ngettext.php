<?php
/**
 * ngettext test script - string generator
 * @copyright &copy; 2006 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id$
 * @package plugins
 * @subpackage test
 */

/**/
if (file_exists('../../include/init.php')) {
    include_once('../../include/init.php');
} else if (file_exists('../../include/validate.php')) {
    //if (!defined('SM_PATH')) define('SM_PATH', '../../');
    define('SM_PATH', '../../');
    include_once(SM_PATH . 'include/validate.php');
} else {
    chdir('..');
    include_once('../src/validate.php');
}

displayPageHeader($color,'none');

/** sm 1.5.1 code */
sq_bindtextdomain('test',SM_PATH . 'locale');
sq_textdomain('test');
?>
<h3 align="center">ngettext test strings</h3>
<p>Test depends on selected translation and translated strings in 
locale/xx/LC_MESSAGES/test.mo files.</p>

<?php
echo "<pre>";
for ($i=-10;$i<=250;$i++) {
    echo sprintf(ngettext("%s squirrel on the tree.","%s squirrels on the tree.",$i),$i);
    echo "\n";
}
echo "</pre>";
sq_textdomain('squirrelmail');
echo "</body></html>";
?>
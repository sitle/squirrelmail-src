<?php
/**
 * Lists available tests
 * @copyright 2006-2009 The SquirrelMail Project Team
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
?>
<p><a href="decodeheader.php">decodeHeader() test</a></p>
<p><a href="ngettext.php">ngettext() test</a> (with sm 1.5.1 dependency)</p>
</body></html>
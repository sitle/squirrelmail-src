<?php
/**
 * Lists available tests
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
    // TODO: check sm 1.2.x
    include_once('../../src/validate.php');
}

displayPageHeader('Tests',$color);
?>
<p><a href="decodeheader.php">decodeHeader() test</a></p>
</body></html>
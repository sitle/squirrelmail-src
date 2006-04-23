<?php
/**
 * Init plugin
 * @copyright &copy; 2006 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id$
 * @package plugins
 * @subpackage test
 */

/**
 * Initialize the plugin
 * @return void
 */
function squirrelmail_plugin_init_test() {
    global $squirrelmail_plugin_hooks;
    $squirrelmail_plugin_hooks['menuline']['test'] = 'test_menuline';
}

/**
 * Add link to upper menu
 */
function test_menuline() {
    displayInternalLink('plugins/test/test.php','Test','right');
    echo "&nbsp;&nbsp;\n";
}
?>
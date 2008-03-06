<?php

/**
 * plugins/fortune/setup.php
 *
 * Original code contributed by paulm@spider.org
 *
 * Simple SquirrelMail WebMail Plugin that displays the output of
 * fortune above the message listing.
 *
 * @copyright (c) 1999-2006 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id$
 * @package plugins
 * @subpackage fortune
 *
 */

/**
 * Init plugin
 * @access private
 */
function squirrelmail_plugin_init_fortune() {
  global $squirrelmail_plugin_hooks;

  $squirrelmail_plugin_hooks['mailbox_index_before']['fortune'] = 'fortune';
  $squirrelmail_plugin_hooks['optpage_loadhook_display']['fortune'] = 'fortune_optpage_loadhook_display';
  $squirrelmail_plugin_hooks['loading_prefs']['fortune'] = 'fortune_load';
}

/**
 * Show fortune
 * @access private
 */
function fortune() {
    global $fortune_visible, $color;

    // Don't show fortune if not set, or not enabled //
    if (!isset($fortune_visible) || !$fortune_visible) {
        return;
    }

    include_once(SM_PATH . 'plugins/fortune/fortune_functions.php');
    fortune_show();
}

/**
 * Get fortune prefs
 * @access private
 */
function fortune_load() {
    global $username, $data_dir, $fortune_visible;

    $fortune_visible = getPref($data_dir, $username, 'fortune_visible');
}

/**
 * Add fortune options
 * @access private
 */
function fortune_optpage_loadhook_display() {
    include_once(SM_PATH . 'plugins/fortune/fortune_functions.php');
    fortune_show_options();
}

?>
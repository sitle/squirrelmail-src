<?php
/**
 * Plugin init file
 *
 * Copyright (c) 2005 The SquirrelMail Project Team
 * This file is part of SquirrelMail Demo plugin.
 *
 * Demo plugin is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Demo plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Demo plugin; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @version $Id$
 * @package plugins
 * @subpackage demo
 */

/**
 * Init function
 */
function squirrelmail_plugin_init_demo() {
    global $squirrelmail_plugin_hooks;
    // login page form
    $squirrelmail_plugin_hooks['login_form']['demo']='demo_login_form';

    $squirrelmail_plugin_hooks['options_identities_process']['demo']='demo_options_identities_process';
    $squirrelmail_plugin_hooks['options_identities_top']['demo']='demo_options_identities_top';
    $squirrelmail_plugin_hooks['options_identities_renumber']['demo']='demo_options_identities_renumber';
    $squirrelmail_plugin_hooks['options_identities_table']['demo']='demo_options_identities_table';
    $squirrelmail_plugin_hooks['options_identities_buttons']['demo']='demo_options_identities_buttons';
}

/**
 * Show language selection form
 */
function demo_login_form() {
    include_once(SM_PATH.'plugins/demo/functions.php');
    return demo_login_form_do();
}

/**
 * Process Advanced Identities submission
 */
function demo_options_identities_process(&$args) {
    include_once(SM_PATH.'plugins/demo/functions.php');
    demo_options_identities_process_do($args);
}

/**
 * Add some text before advanced identities forms
 */
function demo_options_identities_top() {
    include_once(SM_PATH.'plugins/demo/functions.php');
    demo_options_identities_top_do();
}

/**
 * Process renumbering of identities
 */
function demo_options_identities_renumber(&$args) {
    include_once(SM_PATH.'plugins/demo/functions.php');
    demo_options_identities_renumber_do($args);
}

/**
 * Add code to Advanced Identities option form table
 */
function demo_options_identities_table(&$args) {
    include_once(SM_PATH.'plugins/demo/functions.php');
    return demo_options_identities_table_do($args);
}

/**
 * Add some text before advanced identities forms
 */
function demo_options_identities_buttons(&$args) {
    include_once(SM_PATH.'plugins/demo/functions.php');
    return demo_options_identities_buttons_do($args);
}

/**
 * Show plugin version
 * @return string plugin version
 */
function demo_version() {
    return '1.0cvs';
}
?>
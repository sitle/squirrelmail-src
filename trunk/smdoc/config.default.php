<?php
/*
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition/modification to the
 * Framework for Object Orientated Web Development (Foowd).
 *
 * $Id$
 */

define('SM_PATH', 'lib/');
define('FOOWD_DIR', '../foowd/lib/');
define('CFG_PATH','config/');
define('TEMPLATE_PATH','templates/');
define('DATETIME_FORMAT', 'Y/m/d h:ia'); // formatting string to format dates

$foowd_parameters = array(

/*
 * Debug Settings
 * -------------------------------------------------------------
 */
    'debug' => array(
       'debug_class' => 'smdoc_debug',
        'debug_path' => SM_PATH . 'smdoc.env.debug.php',
     'debug_enabled' => FALSE,
         'debug_var' => FALSE
                    ),

/*
 * Site Settings
 * -------------------------------------------------------------
 */
    'site' => array(
          'email_webmaster' => 'webmaster@example.org',
            'email_noreply' => 'noreply@example.org',
                'site_name' => 'Default Foowd site',
    'allow_duplicate_title' => FALSE,
                   ),

/*
 * Archive Settings
 * -------------------------------------------------------------
 */
    'archive' => array(
            'destroy_older_than' => '-1 month',
                    'tidy_delay' => 86400,
         'min_archived_versions' => 3,
                      ),

/*
 * Database Settings
 * -------------------------------------------------------------
 */
    'database' => array(
             'db_class' => 'smdoc_db_mysql',
              'db_path' => SM_PATH . 'smdoc.env.database.php',
	          'db_type' => 'mysql',
	          'db_host' => 'localhost',
	      'db_database' => 'foowd',
	          'db_user' => 'foowd',
	      'db_password' => 'foowd',
        'db_persistent' => TRUE,
             'db_table' => 'tblobject'
                       ),

/*
 * User Settings
 * -------------------------------------------------------------
 */
    'user' => array(
           'user_class' => 'smdoc_user',
            'user_path' => SM_PATH . 'smdoc.class.user.php',
       'user_auth_type' => 'session',
      'anon_user_class' => 'foowd_anonuser',
       'anon_user_path' => SM_PATH . 'class.anonuser.php',
       'anon_user_name' => 'Anonymous',
        'anon_user_god' => FALSE,
        'password_salt' => '',
  'user_session_length' => 900,
                   ),

/*
 * Group Settings
 * -------------------------------------------------------------
 */
    'group' => array(
           'group_class' => 'smdoc_group',
            'group_path' => SM_PATH . 'smdoc.class.group.php',
                    ),

/*
 * Workspace Settings
 * -------------------------------------------------------------
 */
    'workspace' => array(
           'workspace_class' => 'foowd_workspace',
            'workspace_path' => FOOWD_DIR . 'class.workspace.php',
       'workspace_base_name' => 'Outside',
                        ),

/*
 * Template Settings
 * -------------------------------------------------------------
 */
    'template' => array(
           'template_class' => 'smdoc_display',
            'template_path' => SM_PATH . 'smdoc.env.template.php',
             'template_dir' => TEMPLATE_PATH,
                       ),

);                                   /* end $foowd_parameters */


/*
 * Common includes
 * -------------------------------------------------------------
 */
require_once(SM_PATH . 'smdoc.env.foowd.php');
require_once(FOOWD_DIR.'class.object.php');
require_once(SM_PATH . 'input.querystring.php');
require_once(SM_PATH . 'input.session.php');


/*
 * Session initialization
 * -------------------------------------------------------------
 */
ini_set('magic_quotes_runtime','0');
ini_set('session.name' , 'SMDOC_SESSID');
session_start();

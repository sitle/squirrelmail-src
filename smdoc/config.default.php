<?php
/*
 * Copyright (c) 2003-2004 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition/modification to the
 * Framework for Object Orientated Web Development (Foowd).
 *
 * $Id$
 */

/**
 * Configuration parameters for Foowd/smdoc.
 * 
 * @package smdoc
 */

/** Path for smdoc includes */
define('SM_DIR',    './lib/');
/** Path for input classes */
define('INPUT_DIR', './lib/input/');
/** Path for template files */
define('TEMPLATE_PATH','./templates/');
/** Preferred Date display format */
define('DATETIME_FORMAT', 'Y/m/d h:ia');
/** Location of tmp directory for diff */
define('DIFF_TMPDIR', '/tmp/');
/** Base filename/primary entrypoint */
define('FILENAME', 'index.php');
/** URL that gets you to the above file */
define('BASE_URL', 'http://'.$_SERVER['HTTP_HOST'].'/smdoc/');


/**
 * Configuration array containing initial parameters for foowd
 * @var array
 */
$foowd_parameters = array(

/*
 * Debug Settings
 * -------------------------------------------------------------
 */
    'debug' => array(
       'debug_class' => 'smdoc_debug',
        'debug_path' => SM_DIR . 'smdoc.env.debug.php',
     'debug_enabled' => FALSE,
         'debug_var' => FALSE,
                    ),

/*
 * Site Settings
 * -------------------------------------------------------------
 */
    'site' => array(
          'email_webmaster' => 'webmaster@example.org',
            'email_noreply' => 'noreply@example.org',
                'site_name' => 'Default Foowd site',
           'default_method' => 'view',
     'default_class_method' => 'create'
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
             'db_class' => 'smdoc_db',
              'db_path' => SM_DIR . 'smdoc.env.database.php',
	          'db_type' => 'mysql',
	          'db_host' => 'localhost',
	      'db_database' => 'smdocs',
	          'db_user' => 'smdocs',
	      'db_password' => 'foowd',
        'db_persistent' => TRUE,
             'db_table' => 'smdoc_object'
                       ),

/*
 * User Settings
 * -------------------------------------------------------------
 */
    'user' => array(
           'user_class' => 'smdoc_user',
            'user_path' => SM_DIR . 'smdoc.class.user.php',
       'user_auth_type' => 'session',
      'anon_user_class' => 'foowd_anonuser',
       'anon_user_path' => SM_DIR . 'class.anonuser.php',
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
            'group_path' => SM_DIR . 'smdoc.env.group.php',
                    ),

/*
 * Workspace Settings
 * -------------------------------------------------------------
 */
    'workspace' => array(
           'workspace_class' => 'smdoc_translation',
            'workspace_path' => SM_DIR . 'smdoc.class.translation.php',
       'workspace_base_name' => 'en_US',
                        ),

/*
 * Template Settings
 * -------------------------------------------------------------
 */
    'template' => array(
           'template_class' => 'foowd_template',
            'template_path' => SM_DIR . 'env.template.php',
             'template_dir' => TEMPLATE_PATH,
                       ),

);                                   /* end $foowd_parameters */

/*
 * Common includes
 * -------------------------------------------------------------
 */
/** Base input library: input_base, and sqGetGlobalVar, etc. */
require_once(INPUT_DIR . 'input.lib.php');
/** Input class that manages data in the session. */
include_once(INPUT_DIR . 'input.session.php');

/** Library containing common utility functions. */
require_once(SM_DIR . 'lib.php');
/** Customized Foowd env. */
require_once(SM_DIR . 'smdoc.env.foowd.php');
/** Success/Failure codes for status across form submission. */
require_once(SM_DIR . 'smdoc.error.constants.php');

/** Base Foowd object. */
require_once(SM_DIR . 'class.object.php');
/** Error Handling - smdoc_error class and error handler */
include_once(SM_DIR . 'smdoc.class.error.php');
/** ShortName mapping/lookup. */
include_once(SM_DIR . 'smdoc.class.namelookup.php');
/** Management and inclusion of translations. */
include_once(SM_DIR . 'smdoc.class.translation.php');
/** Customized user implementation. */
include_once(SM_DIR . 'smdoc.class.user.php');

/** Other object types */
include_once(SM_DIR . 'class.text.plain.php');
include_once(SM_DIR . 'smdoc.class.text.textile.php');
include_once(SM_DIR . 'smdoc.class.news.php');
include_once(SM_DIR . 'smdoc.class.spec.php');


/*
 * Session initialization
 * -------------------------------------------------------------
 */
ini_set('magic_quotes_runtime','0');
ini_set('session.name' , 'SMDOC_SESSID');
session_start();

<?php
/*
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition to the
 * Framework for Object Orientated Web Development (Foowd).
 */

/**
 * Constants for passing success/error status across a form submission.
 *
 * $Id$
 * 
 * @package smdoc
 * @subpackage error
 */

/**
 * Substitute numerical value passed in
 * with corresponding status string.
 * @param int ok       Single numerical value for success message
 * @param mixed error  Single numerical value (or array of said values) 
 *                     for error messages.
 */
function getStatusStrings(&$ok, &$error)
{
  if ( is_array($error) )
  {
    $tmp_ok = '';
    $errorMsg = '';
    foreach ( $error as $code )
    {
      getStatusStrings($tmp_ok, $code);
      $errorMsg .= $code . '<br />';
    }
    $error = $errorMsg;
  }    
  elseif ( is_numeric($ok) && defined($ok.'_MSG') )
    $ok = constant($ok.'_MSG');
  elseif ( is_numeric($error) && defined($error.'_MSG'))
    $error = constant($error.'_MSG');
}

define('INVALID_METHOD', 1);
define('1_MSG', _("Invalid or disabled method"));

define('FORM_FILL_FIELDS', 2);
define('2_MSG', _("Required fields are marked with an *"));


define('USER_LOGIN_OK', 10);
define('10_MSG', _("User logged in."));

define('USER_LOGIN_PREV', 11);
define('11_MSG', _("User already logged in."));

define('USER_LOGOUT_OK', 12);
define('12_MSG', _("You are now logged out."));

define('USER_UPDATE_OK', 13);
define('13_MSG', _("User updated."));

define('USER_CREATE_OK', 14);
define('14_MSG', _("User created and saved."));

define('USER_NO_PERMISSION', 15);
define('15_MSG', _("Access Denied."));



define('OBJECT_CREATE_OK', 20);
define('20_MSG', _("Object created and saved."));

define('OBJECT_CREATE_FAILED', 21);
define('21_MSG', _("Object could not be created."));

define('OBJECT_DUPLICATE_TITLE', 22);
define('22_MSG', _("Cannot have duplicate titles within this workspace."));

define('OBJECT_UPDATE_OK', 23);
define('23_MSG', _("Object updated."));

define('OBJECT_UPDATE_CANCEL', 24);
define('24_MSG', _("Action cancelled."));

define('OBJECT_UPDATE_FAILED', 25);
define('25_MSG', _("Object could not be updated."));

define('OBJECT_DELETE_OK', 26);
define('26_MSG', _("Object deleted."));

define('OBJECT_DELETE_FAILED', 27);
define('27_MSG', _("Object could not be deleted."));

define('OBJECT_NOT_FOUND', 28);
define('28_MSG', _("Object could not be found."));

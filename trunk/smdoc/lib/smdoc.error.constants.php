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

define('USER_LOGIN_OK', 2);
define('2_MSG', _("User logged in."));

define('USER_LOGIN_PREV', 3);
define('3_MSG', _("User already logged in."));

define('USER_LOGIN_BAD_HOST', 4);
define('4_MSG', _("Your host is not valid for connecting as this user."));

define('USER_LOGOUT_OK', 5);
define('5_MSG', _("You are now logged out."));

define('FORM_FILL_FIELDS', 6);
define('6_MSG', _("Required fields are marked with an *"));

define('USER_CREATE_OK', 7);
define('7_MSG', _("User created and saved."));

define('OBJECT_CREATE_OK', 8);
define('8_MSG', _("Object created and saved."));

define('USER_UPDATE_OK', 9);
define('9_MSG', _("User updated."));

define('OBJECT_UPDATE_OK', 10);
define('10_MSG', _("Object updated."));

define('OBJECT_UPDATE_CANCEL', 11);
define('11_MSG', _("Action cancelled."));

define('OBJECT_CREATE_FAILED', 12);
define('12_MSG', _("Object could not be created."));

define('OBJECT_DELETE_OK', 13);
define('13_MSG', _("Object deleted."));

define('OBJECT_DELETE_FAILED', 14);
define('14_MSG', _("Object could not be deleted."));

define('DIFF_FAILED_SAME', 15);
define('15_MSG', _("Object can not be compared to itself."));

define('DIFF_OK_SAME', 16);
define('16_MSG', _("Object versions are identical."));

define('OBJECT_DUPLICATE_TITLE', 17);
define('17_MSG', _("Cannot have duplicate titles within this workspace."));

define('USER_NO_PERMISSION', 18);
define('18_MSG', _("Access Denied."));

define('OBJECT_UPDATE_FAILED', 19);
define('19_MSG', _("Object could not be updated."));


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

setClassMeta('smdoc_user', 'User');
setConst('USER_CLASS_ID', META_SMDOC_USER_CLASS_ID);
setConst('USER_CLASS_NAME', getConst('META_'. USER_CLASS_ID .'_CLASSNAME'));
setConst('USER_CLASS', 'smdoc_user');

include_once(PATH . 'class.user.php');

class smdoc_user extends foowd_user
{
  /**
   * USER FACTORY METHODS (STATIC)
   */
  function &factory(&$foowd, $user)
  {
    if ( !isset($user) || $user == NULL )
      $user = array();

    $foowd->track('smdoc_user::factory', $user);

    $new_user = NULL;

    // Load the user if loadUser is UNSET or TRUE
    if (!isset($user['loadUser']) || $user['loadUser'])
    {
      $user = smdoc_user::getUserDetails($foowd, $user);
      if ( isset($user['username']) && isset($user['password']) )
      {
        $new_user = smdoc_user::fetchUser($foowd,
                         crc32(strtolower($user['username'])),
                         $user['password']);
      }
    }

    // If loading the user is unsuccessful (or unattempted),
    // fetch an anonymous user
    if ( $new_user == NULL )
      $new_user = smdoc_user::fetchAnonymousUser($foowd);

    $foowd->track();
    return $new_user;
  }

  function &fetchAnonymousUser(&$foowd)
  {
    $anonUserClass = getConstOrDefault('ANONYMOUS_USER_CLASS', 'foowd_anonuser');
    if (class_exists($anonUserClass)) {
      return new $anonUserClass($foowd);
    } else {
      trigger_error('Could not find anonymous user class.', E_USER_ERROR);
    }
  }

  function &fetchUser(&$foowd, $userid, $password)
  {
    // If we don't have required elements, return early.
    if ( !isset($userid) || !defined('USER_CLASS_ID') )
      return NULL;

    $foowd->track('foowd_user::fetchUser', $userid);

    // Set up clause for DB Query
    $whereClause[] = 'AND';
    $whereClause[] = 'objectid = '.$userid;
    $whereClause[] = 'classid = '.USER_CLASS_ID;

    $query = $foowd->database->select($foowd, NULL, array('object'),
                                      $whereClause, NULL, array('version DESC'), 1);

    if ($query && $foowd->database->returnedRows($query) > 0 )
    {
      $record = $foowd->database->getRecord($query);
      if (isset($record['object']))
      {
        $serializedObj = $record['object'];
        $user = unserialize($serializedObj);
        if (isset($password) && $user->passwordCheck($password))
        {
          $foowd->track();
          return $user;
        }
      }
    }
    $foowd->track();
    return NULL;
  }

  /**
   * Get user details from an external mechanism.
   *
   * If not already set, populate the user array with the user classid and
   * fetch the username and password of the current user from one of the input
   * mechanisms
   *
   * @class foowd_user
   * @method getUserDetails
   * @param array user The user array passed into <code>foowd::foowd</code>.
   * @return array The resulting user array.
   */
  function getUserDetails(&$foowd, $user) {
    $foowd->track('smdoc->getUserDetails', $user);

    // Get class id
    $session_classid = new input_session('user_classid');
    if ( !isset($session_classid->value) ) {
        if ( !isset($user['classid']) ) {
            if ( isset($user['class']) ) {
                $user['classid'] = crc32(strtolower($user['class']));
            } else {
                $user['classid'] = getConstOrDefault('USER_CLASS_ID', crc32('foowd_user'));
            }
        }
        $session_classid->set($user['classid']);
    } else {
        $user['classid'] = $session_classid->value;
    }

    // get username and password
    if ( isset($user['username']) && isset($user['password']) ) {
        if ( isset($user['plainPassword']) || strlen($user['password']) != 32) { // plain text password, md5 it
            $user['password'] = md5(getConstOrDefault('PASSWORD_SALT', '').strtolower($user['password']));
        }
    } else {
        sendTestCookie($foowd);
        include_once($foowd->path.'/input.cookie.php');
        $username = new input_cookie('username', REGEX_TITLE);
        $user['username'] = $username->value;
        $password = new input_cookie('password', REGEX_PASSWORD);
        $user['password'] = $password->value;
    }
    $foowd->track();
    return $user;
  }

  function smdoc_user( &$foowd,
                   $username = NULL,
                   $password = NULL,
                   $email = NULL,
                   $groups = NULL,
                   $hostmask = NULL)
  {
    parent::foowd_user($foowd, $username, $password, $email, $groups, $hostmask);
  }



}


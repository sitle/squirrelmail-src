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
      $foowd->debug('msg', 'User details: ' . serialize($user) );
      if ( isset($user['username']) && isset($user['userid']) && isset($user['password']) )
      {
        if ( !isset($user['userid']) )
            $user['userid'] = crc32(strtolower($user['username']));
        $new_user = smdoc_user::fetchUser($foowd, $user);
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

  function &fetchUser(&$foowd, $userArray = NULL)
  {
    // If we don't have required elements, return early.
    if ( !isset($userArray['userid']) )
      return FALSE;

    $foowd->track('foowd_user::fetchUser', $userArray);

    // Set up clause for DB Query
    $whereClause[] = 'AND';
    $whereClause[] = 'objectid = '.$userArray['userid'];
    $whereClause[] = 'classid = '.USER_CLASS_ID;

    $query = $foowd->database->select($foowd, NULL, array('object'),
                                      $whereClause, NULL, array('version DESC'), 1);

    if ( !$query || $query->returnedRows($query) <= 0 ) {
      $foowd->debug('msg', 'Could not find user in database');
      $foowd->track();
      return FALSE;
    }  
      
    $record = $query->getRecord($query);

    if ( !isset($record['object']) ) {
      $foowd->debug('msg', 'Could not retrieve user from database');
      $foowd->track();
      return FALSE;
    }

    $serializedObj = $record['object'];
    $user = unserialize($serializedObj);
        
    if ( !isset($userArray['password']) || 
         ( $user->passwordCheck($userArray['password']) && $user->hostmaskCheck() )) {
      $foowd->track();
      return $user;
    } 
          
    $foowd->debug('msg', 'Password incorrect for user');
    $foowd->track();
    return FALSE;
  }


  /**
   * Constructs a new user.
   *
   * @constructor foowd_user
   * @param object foowd The foowd environment object.
   * @param optional str username The users name.
   * @param optional str password An MD5 hash of the users password.
   * @param optional str email The users e-mail address.
   * @param optional array groups The user groups the user belongs to.
   * @param optional str hostmask The users hostmask.
   */
  function smdoc_user( &$foowd,
                   $username = NULL,
                   $password = NULL,
                   $email = NULL,
                   $groups = NULL,
                   $hostmask = NULL)
  {
    parent::foowd_user($foowd, $username, $password, $email, $groups, $hostmask);
  }

  /**
   * Check if the user is the anonymous user.
   *
   * @class foowd_user
   * @method isAnonymous
   * @return bool Returns TRUE if the user is of the anonymous user class.
   */
  function isAnonymous() {
    return FALSE;
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

    $session_userinfo = new input_session('userinfo', NULL, NULL, true);
    if ( $session_userinfo->value == NULL )
        return $user;

    $user_info = $session_userinfo->value;
    $update_session = FALSE;

    if ( !isset($user_info['classid']) ) {
        $user_info['classid'] = getConstOrDefault('USER_CLASS_ID', crc32('smdoc_user'));
        $update_session = TRUE;
    } 

    if ( !isset($user['username']) && !isset($user['password']) ) {
        $user['username'] = $user_info['username'];
        $user['password'] = $user_info['password'];
        $user['userid']   = $user_info['userid'];
    }
    $user['classid']   = $user_info['classid'];

    if ( $update_session )
        $session_userinfo->set($user_info);

    $foowd->track();
    return $user;
  }


  /**
   * Log the user in.
   *
   * @class foowd_user
   * @method login
   * @param object foowd The foowd environment object.
   * @param optional str username The username of the user to log in as.
   * @param optional str password The plain text password of the user to log in with.
   * @return int 0 = logged in successfully<br />
   *             1 = no user given<br />
   *             2 = unknown user<br />
   *             3 = bad password<br />
   *             4 = unknown authentication method<br />
   *             5 = user already logged in<br />
   *             6 = did not http auth correctly<br />
   *             7 = must have cookies enabled<br />
   *             8 = bad hostmask<br />
   */
  function login(&$foowd, $username = FALSE, $password = NULL) {
    if ( !$foowd->user->isAnonymous() ) 
      return 5; // user already logged in
    if ( !$username ) 
      return 1; // no user given

    $salt = getConstOrDefault('PASSWORD_SALT', '');

    $user_info['username'] = $username;
    $user_info['password'] = md5($salt.$password);
    $user_info['userid']   = crc32(strtolower($username));

    $newuser = smdoc_user::fetchUser($foowd, $user_info);

    if ( !is_object($newuser) || strtolower($newuser->title) != strtolower($username)) 
      return 2; // unknown user
    if ( !$newuser->hostmaskCheck() )
      return 8; // bad hostmask
    if ( $newuser->password != md5($salt.strtolower($password)) )
      return 3; // bad password

    $foowd->user = $newuser;
    $session_userinfo = new input_session('userinfo', NULL, $user_info, true); 
    $session_userinfo->set($user_info);
    return 0; // logged in successfully
  }

  /**
   * Log out the user.
   *
   * @class foowd_user
   * @method logout
   * @param object foowd The foowd environment object.
   * @param optional str authType The type of user authentication to use.
   * @return int 0 = cookie logged out successfully<br />
   *             1 = http logged out successfully<br />
   *             2 = ip auth, can not log out<br />
   *             3 = user already logged out<br />
   *             4 = http log out failed due to browser<br />
   */
  function logout(&$foowd, $authType = 'session') {
    if ( $foowd->user->isAnonymous() ) 
      return 3; // user already logged out
      
    $foowd->user = smdoc_user::fetchAnonymousUser($foowd);

    $session_userinfo = new input_session('userinfo', NULL, NULL, true); 
    $session_userinfo->set(NULL);
        
    return 0; // logged out successfully
  }

  /**
   * Create a new user.
   *
   * @class foowd_user
   * @method create
   * @param object foowd The foowd environment object.
   * @param str username The name of the user to create.
   * @param str password The password of the user.
   * @param str email The e-mail address of the user.
   * @return int 0 = created ok<br />
   *             1 = created ok, ip auth so you can't log in<br />
   *             2 = need cookie, support not found<br />
   *             3 = eek, error creating user<br />
   */
  function create(&$foowd, $className, $username, $password, $email) {
    $object = new $className($foowd, $username, $password, $email);
    if ( $object->objectid != 0 && $object->save($foowd, FALSE) ) 
      return 0; // created ok
    else
      return 3; // eek, error creating user.
  }

  /**
   * Get user a new password if it has been lost.
   *
   * @class foowd_user
   * @method fetchPassword
   * @param object foowd The foowd environment object.
   * @param str className The name of the user class.
   * @param str username The name of the user to fetch the password for.
   * @param optional str queryUsername Username given for stage 2 of the retrieval process.
   * @param optional str id The ID given for stage 2 of the process.
   * @return int 0 = nothing, display form<br />
   *             1 = password change request e-mail sent<br />
   *             2 = could not send e-mail due to technical problem<br />
   *             3 = user has no e-mail address<br />
   *             4 = user does not exist<br />
   *             5 = password changed and e-mail sent<br />
   *             6 = could not send e-mail due to technical problem<br />
   *             7 = id does not match<br />
   *             8 = user does not exist<br />
   */
  function fetchPassword(&$foowd, $className, $username, $queryUsername = '', $id = '') {
    if ( $username == '' ) 
      return 0; // nothing, display form

    $lostuser = $foowd->fetchObject(crc32(strtolower($username)), USER_CLASS_ID);

    if ( !isset($lostuser->title) || strtolower($lostuser->title) != strtolower($username) )
      return 4; // user does not exist
    if ( !isset($lostuser->email) )
      return 3; // user has no e-mail address

    // We have username only, send stage one email
    if ( $id == '' && $queryUsername == '' ) {

        $message = call_user_func(
                        array($className, 'fetchPasswordRequestEmail'),
                        $className,
                        $lostuser->getTitle(),
                        md5($lostuser->updated.$lostuser->title) ); 
        $result = email($foowd, $lostuser->email, 
                        sprintf(_("%s - Password Change Request"), 
                        getSiteName()), $message,
                        'From: '.getWebmasterEmail().'\r\nReply-To: '.getNoreplyEmail());
        if ( $result )
            return 1; // password change request e-mail sent
        else
            return 2; // could not send e-mail due to technical problem

    // We have id and query, change password and send confirmation
    } else {
        if ( strtolower($lostuser->title) != strtolower($queryUsername) )
            return 8; // user does not exist
        if ($id != md5($lostuser->updated.$lostuser->title)) 
            return 7; // id does not match
        
        $newPassword = '';
        $foo_len = rand(6,12);
        srand(time());
        for($foo = 0; $foo < $foo_len; $foo++) 
            $newPassword .= chr(rand(97, 122)); 

        $salt = getConstOrDefault('PASSWORD_SALT', '');
        $lostuser->password = md5($salt.$newPassword);

        if ( $lostuser->save($foowd, FALSE) ) {
          $message = call_user_func(
                          array($className, 'fetchPasswordChangedEmail'),
                          $className,
                          $lostuser->getTitle(),
                          $newPassword);
          $result = email($foowd, $lostuser->email, 
                          sprintf(_("%s - Password Change Request"), 
                          getSiteName()), $message,
                          'From: '.getWebmasterEmail().'\r\nReply-To: '.getNoreplyEmail());
          if ( $result )
              return 5; // password changed and e-mail sent
        }
        return 6; // could not send e-mail due to technical problem (or could not save new password)
    }

  }


/* Class methods */

  /**
   * Output an object creation form and process its input.
   *
   * @class foowd_user
   * @method class_create
   * @param object foowd The foowd environment object.
   * @param str className The name of the class.
   */
  function class_create(&$foowd, $className) {
    $foowd->track('smdoc_user->class_create');

    include_once($foowd->path.'/input.textbox.php');
    include_once($foowd->path.'/input.form.php');
    
    $queryTitle = new input_querystring('title', REGEX_TITLE, NULL);
    $createUsername = new input_textbox('createUsername', REGEX_TITLE, $queryTitle->value, _("Username").':');
    $createPassword = new input_passwordbox('createPassword', REGEX_PASSWORD, NULL, _("Password").':');
        $verifyPassword = new input_passwordbox('verifyPassword', REGEX_PASSWORD, NULL, _("Verify").':');
    $createEmail = new input_textbox('createEmail', REGEX_EMAIL, NULL, _("E-mail Address").':', NULL, NULL, NULL, FALSE);
    $createForm = new input_form('createForm', NULL, 'POST', _("Create"), _("Reset"));

    if ( $createForm->submitted()     && $createUsername->value != '' && 
             $createPassword->value != '' && $verifyPassword->value != '' ) {

            if ( $createPassword->value == $verifyPassword->value ) {
                $result = call_user_func(array($className, 'create'), $foowd, $className, 
                                         $createUsername->value,
                                         $createPassword->value,
                                         $createEmail->value);
            } else {
                $result = -1;
            }

            switch ($result) {
              case 0:
                $url = getURI(array('class' => $className,
                                    'method' => 'login',
                                    'ok' => USER_CREATE_OK,
                                    'username' => htmlspecialchars($createUsername->value)));
                header('Location: ' . $url);
                return NULL;
              case -1: 
                $return['failure'] = _("Passwords must match");
                $verifyPassword->value = '';
                break;
              case 3: 
                $return['failure'] = _("Could not create user.");
                break;
            }
        } 
        
        if (!isset($return['failure']) )
          $return['failure'] =  FORM_FILL_FIELDS;
      
        $createForm->addObject($createUsername);
        $createForm->addObject($createPassword);
        $createForm->addObject($verifyPassword);
        $createForm->addObject($createEmail);
        $return['form'] = &$createForm;

        return $return;
    }

  /**
   * Output a login form and process its input.
   *
   * @class foowd_user
   * @method class_login
   * @param object foowd The foowd environment object.
   * @param str className The name of the class.
   */
  function class_login(&$foowd, $className) {
        $foowd->track('smdoc_user->class_login');

        include_once($foowd->path.'/input.textbox.php');
        include_once($foowd->path.'/input.form.php');

    $usernameQuery = new input_querystring('username', REGEX_TITLE, '');
    $loginUsername = new input_textbox('loginUsername', REGEX_TITLE, $usernameQuery->value, _("Username").':');
    $loginPassword = new input_passwordbox('loginPassword', REGEX_PASSWORD, NULL, _("Password").':');

        $loginForm = new input_form('loginForm', NULL, 'POST', _("Log In"), NULL);
        $loginForm->addObject($loginUsername);
    $loginForm->addObject($loginPassword);

        if ( $loginForm->submitted() ) {
            $result = call_user_func( array($className, 'login'),
                            $foowd, 
                                      $loginUsername->value,
                            $loginPassword->value );
        } else {
            $result = 1;
        }

        switch ($result) {
          case 0:
          case 5:
            $ok = ( $result == 0 ) ? USER_LOGIN_OK : USER_LOGIN_PREV;
            $url = getURI(array('objectid' => $foowd->user->objectid, 
                                'classid' => USER_CLASS_ID,
                                'ok' => $ok));
            $foowd->track();
            header('Location: ' . $url);
          case 1:
            $return['failure'] =  FORM_FILL_FIELDS;
            $return['form'] = &$loginForm;
            break;
          case 2:
          case 3:
            $return['failure'] =  _("User or password is incorrect.");
            $return['form'] = &$loginForm;
            break;
          case 8:
            $url =  getURI(array('error' => USER_LOGIN_BAD_HOST));
            $foowd->track();
            header('Location: ' . $url);
        }
          
        $foowd->track(); 
        return $return;
    }

  /**
   * Log the user out and display a log out screen.
   *
   * @class foowd_user
   * @method class_logout
   * @param object foowd The foowd environment object.
   * @param str className The name of the class.
   */
  function class_logout(&$foowd, $className) {
        $return = parent::class_logout($foowd, $className);

        switch ($return['rc']) {
          case 0:
          case 1:
          case 3:
            $url =  getURI(array('class'  => 'smdoc_user',
                                 'method' => 'login',
                                 'ok'     => USER_LOGOUT_OK));
            header('Location: ' . $url);
            return NULL;
        }
        return $return;
    }
}


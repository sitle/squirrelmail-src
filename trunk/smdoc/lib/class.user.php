<?php
/*
Copyright 2003, Paul James

This file is part of the Framework for Object Orientated Web Development (Foowd).

Foowd is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

Foowd is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Foowd; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/*
class.user.php
Foowd user object class
*/

/* Method permissions */
setPermission('foowd_user', 'class', 'create', 'Everyone'); // we want anyone to be able to create a user so we override this permission for this object with the empty string
setPermission('foowd_user', 'object', 'xml', 'Gods'); // we don't want just anyone being able to see our encrypted user passwords
setPermission('foowd_user', 'object', 'groups', 'Gods');
setPermission('foowd_user', 'object', 'update', 'Author');

/* Class descriptor */
setClassMeta('foowd_user', 'User');

/**
 * The Foowd user class.
 *
 * Class for holding information about a user and providing methods for
 * manipulating and getting information on a user.
 *
 * @author Paul James
 * @package Foowd
 */
class foowd_user extends foowd_object {

  /**
   * The users password.
   * 
   * Stored as an MD5 hash to prevent snooping.
   *
   * @var str
   */
  var $password;

  /**
   * The users e-mail address.
   * 
   * @var str
   */
  var $email;

  /**
   * The user groups the user is a member of.
   * 
   * @var array
   */
  var $groups = array();

  /**
   * The users hostmask.
   *
   * Can optionally be set to limit use of the user to a single IP address or hostmask.
   * 
   * @var str
   */
  var $hostmask = NULL;
  
  /**
   * Constructs a new user.
   *
   * @param object foowd The foowd environment object.
   * @param str username The users name.
   * @param str password An MD5 hash of the users password.
   * @param str email The users e-mail address.
   * @param array groups The user groups the user belongs to.
   * @param str hostmask The users hostmask.
   */
  function foowd_user(
    &$foowd,
    $username = NULL,
    $password = NULL,
    $email = NULL,
    $groups = NULL,
    $hostmask = NULL
  ) {
    $foowd->track('foowd_user->constructor');

// password
    if (preg_match(REGEX_PASSWORD, $password)) {
      $salt = getConstOrDefault($foowd->password_salt, '');
      $this->password = md5($salt.strtolower($password));
    } else {
      trigger_error('Could not create object, password contains invalid characters.');
      $this->objectid = 0;
      $foowd->track(); return FALSE;
    }

// base object constructor
    parent::foowd_object($foowd, $username, NULL, NULL, NULL, FALSE);

// email
    if (preg_match($this->foowd_vars_meta['email'], $email)) {
      $this->email = $email;
    }
    
// make user created and owned by self
    $this->creatorid = $this->objectid; // created by self
    $this->creatorName = $this->title;

// user groups
    if (is_array($groups)) {
      foreach ($groups as $group) {
        if (preg_match($this->foowd_vars_meta['groups'], $group)) {
          $this->groups[] = $group;
        }
      }
    }
    
// hostmask
    $this->hostmask = $hostmask;
    
    $foowd->track();
  }

  /**
   * Serliaisation wakeup method.
   *
   * Re-create Foowd meta arrays not stored when object was serialized.
   */
  function __wakeup() {
    parent::__wakeup();
    $this->foowd_vars_meta['password'] = '/^[a-z0-9]{32}$/'; // this is not set to the password regex as it's stored as an md5
    $this->foowd_vars_meta['email'] = REGEX_EMAIL;
    $this->foowd_vars_meta['groups'] = REGEX_GROUP;
    $this->foowd_vars_meta['hostmask'] = '/^[._?*a-zA-Z0-9-]*$/';
  }

/* Member functions */

  /**
   * Whether a user is in a user group.
   *
   * @param str groupName Name of the group to check.
   * @param int creatorid The userid of the creator .
   * @return bool TRUE or FALSE.
   */
  function inGroup($groupName, $creatorid = NULL) {
    if ($groupName == 'Everyone') { // group is everyone
      return TRUE;
    } elseif ($groupName == 'Nobody') { // group is nobody
      return FALSE;
    } elseif (is_array($this->groups) && in_array($groupName, $this->groups)) { // user is in group
      return TRUE;
    } elseif (is_array($this->groups) && in_array('Gods', $this->groups)) { // user is a god
      return TRUE;
    } elseif ($groupName == 'Author' && $creatorid != NULL && $this->objectid != NULL && $this->objectid == $creatorid) { // group is author and so is user
      return TRUE;
    } elseif ($groupName == 'Registered' && !$this->isAnonymous()) { // group is registered and so is user
      return TRUE;
    } elseif ($this->foowd->anonuser_god && $this->isAnonymous()) { // user is anon user with god powers
      return TRUE;
    } else {
      return FALSE;
    }
  }
    
  /**
   * Check the string is the users password.
   *
   * @param str password The password to check.
   * @param bool plainText The password is in plain text rather than an md5 hash.
   * @return bool Returns TRUE if the passwords match.
   */
  function passwordCheck($password, $plainText = FALSE) {
    if ($plainText) {
      $password = md5(getConstOrDefault($this->foowd->password_salt, '').strtolower($password));
    }
    if ($this->password === $password || defined('AUTH_IP_'.$_SERVER['REMOTE_ADDR'])) {
      return TRUE;
    } else {
      return FALSE;
    }
  }
  
  /**
   * Check the users hostmask matches that of the incoming request.
   *
   * @return bool Returns TRUE if the hostmasks match.
   */
  function hostmaskCheck() {
    if ($this->hostmask && function_exists('fnmatch')) { // only works on systems with access to "fnmatch", other systems always return TRUE
      if (isset($_SERVER['REMOTE_HOST']) && fnmatch($this->hostmask, $_SERVER['REMOTE_HOST'])) {
        return TRUE;
      } elseif (isset($_SERVER['REMOTE_ADDR']) && fnmatch($this->hostmask, $_SERVER['REMOTE_ADDR'])) {
        return TRUE;
      } else {
        return FALSE;
      }
    }
    return TRUE;
  }
  
  /**
   * Check if the user is the anonymous user.
   *
   * @return bool Returns TRUE if the user is of the anonymous user class.
   */
  function isAnonymous() {
    if ($this->objectid == getConstOrDefault($this->foowd->anonuser_id, FALSE)) {
      return TRUE;
    } else {
      return FALSE;
    }
  }

  /**
   * Get user details from an external mechanism.
   *
   * If not already set, populate the user array with the user classid and
   * fetch the username and password of the current user from one of the input
   * mechanisms
   *
   * @static
   * @param object foowd The Foowd environment object
   * @param str authType The authentication type to use.
   * @param str username The default username
   * @param str password The default password
   * @param str salt The password salt to use
   * @return array The resulting user array.
   */
  function getUserDetails(&$foowd, $authType, $username, $password, $salt) {
    $foowd->track('foowd->getUserDetails');

    $user = FALSE;

// get our username and password
    if (isset($username) && isset($password)) { // nothing to do, we were already passed the user details explicitly
      $user['username'] = $username;
      $user['password'] = md5($salt.strtolower($password));
    } else { // use the selected input mechanism to fetch the user details
      if (isset($_SERVER['REMOTE_ADDR']) && defined('AUTH_IP_'.$_SERVER['REMOTE_ADDR'])) { // use IP to retrieve user details, whether IP Auth mode or not.
        $user['username'] = constant('AUTH_IP_'.$_SERVER['REMOTE_ADDR']);
        $user['password'] = TRUE;
        $foowd->track(); return $user;
      }
      switch ($authType) {
      case 'cookie': // use cookie to retrieve user details
        sendTestCookie($foowd);
        include_once(FOOWD_DIR.'input.cookie.php');
        $username = new input_cookie('username', REGEX_TITLE);
        $user['username'] = $username->value;
        $password = new input_cookie('password', '/^[a-z0-9]{32}$/');
        $user['password'] = $password->value;
        break;
      case 'http': // use http auth to retrieve user details
        if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
          $user['username'] = $_SERVER['PHP_AUTH_USER'];
          $user['password'] = md5(getConstOrDefault($foowd->password_salt, '').strtolower($_SERVER['PHP_AUTH_PW']));
        }
        break;
      }
    }
    $foowd->track(); return $user;
  }
  
  /**
   * Log the user in.
   *
   * @access private
   * @static
   * @param object foowd The foowd environment object.
   * @param str authType The type of user authentication to use.
   * @param str username The username of the user to log in as.
   * @param str password The plain text password of the user to log in with.
   * @param bool longTermCookie Whether to use a persistant cookie rather than a session cookie.
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
  function login(&$foowd, $authType, $username = FALSE, $password = NULL, $longTermCookie = TRUE) {
    if ($foowd->user->isAnonymous()) { // is anon user
      switch ($authType) {
      case 'http':
        header('WWW-Authenticate: Basic realm="'.getConstOrDefault('AUTH_REALM', 'Framework for Object Orientated Web Development').'"');
        header('HTTP/1.0 401 Unauthorized');
        return 6; // did not http auth correctly
      case 'cookie':
        if (!cookieTest($foowd)) {
          return 7; // must have cookies enabled
        }
        if ($username) {
          $user = &$foowd->getObj(array('objectid' => crc32(strtolower($username)), 'classid' => crc32(strtolower($foowd->user_class))));
          if (is_object($user) && strtolower($user->title) == strtolower($username)) {
            if ($user->hostmaskCheck()) {
              $salt = getConstOrDefault($foowd->password_salt, '');
              if ($user->password == md5($salt.strtolower($password))) {
                $foowd->user = $user;
                if ($longTermCookie) {
                  $expireTime = NULL;
                } else {
                  $expireTime = 0;
                }
                include_once(FOOWD_DIR.'input.cookie.php');
                $cookieUsername = new input_cookie('username', REGEX_TITLE, NULL, $expireTime);
                $cookiePassword = new input_cookie('password', '/^[a-z0-9]{32}$/', NULL, $expireTime);
                $cookieUsername->set($user->title);
                $cookiePassword->set($user->password);
                return 0; // logged in successfully
              } else {
                return 3; // bad password
              }
            } else {
              return 8; // bad hostmask
            }
          } else {
            return 2; // unknown user
          }
        } else {
          return 1; // no user given
        }
      }
      return 4; // unknown authentication method
    } else {
      if ($authType == 'http') {
        return 0; // logged in successfully
      } else {
        return 5; // user already logged in
      }
    }
  }
  
  /**
   * Log out the user.
   *
   * @access private
   * @static
   * @param object foowd The foowd environment object.
   * @param str authType The type of user authentication to use.
   * @return int 0 = cookie logged out successfully<br />
   *             1 = http logged out successfully<br />
   *             2 = ip auth, can not log out<br />
   *             3 = user already logged out<br />
   *             4 = http log out failed due to browser<br />
   */
  function logout(&$foowd, $authType) {
    if ($foowd->user->objectid == getConstOrDefault($foowd->anonuser_id, FALSE) || $foowd->user->objectid == NULL) {
      return 3; // user already logged out
    } else {
      if ($authType == 'ip' || defined('AUTH_IP_'.$_SERVER['REMOTE_ADDR'])) {
        return 2; // ip auth, can not log out
      } elseif ($authType == 'cookie') {
        $anonUserClass = getConstOrDefault($foowd->anonuser_class, 'foowd_anonuser');
        if (class_exists($anonUserClass)) {
          $foowd->user = &new $anonUserClass($foowd);
        }
        include_once(FOOWD_DIR.'input.cookie.php');
        $cookieUsername = new input_cookie('username', REGEX_TITLE);
        $cookiePassword = new input_cookie('password', '/^[a-z0-9]{32}$/');
        $cookieUsername->delete();
        $cookiePassword->delete();
        return 0; // cookie logged out successfully
      } else {
        header('WWW-Authenticate: Basic realm="'.$foowd->auth_realm);
        header('HTTP/1.0 401 Unauthorized');
        if (isset($_SERVER['PHP_AUTH_USER']) || ($foowd->user->objectid != getConstOrDefault($foowd->anonuser_id, NULL) && $foowd->user->objectid != NULL)) {
          return 4; // http log out failed due to browser
        } else {
          return 1; // http logged out successfully
        }
      }
    }
  }
  
  /**
   * Create a new user.
   *
   * @access private
   * @static
   * @param object foowd The foowd environment object.
   * @param str className The name of the user class to create an instance of.
   * @param str authType The type of user authentication to use.
   * @param str username The name of the user to create.
   * @param str password The password of the user.
   * @param str email The e-mail address of the user.
   * @return int 0 = created ok<br />
   *             1 = created ok, ip auth so you can't log in<br />
   *             2 = need cookie, support not found<br />
   *             3 = eek, error creating user<br />
   */
  function create(&$foowd, $className, $authType, $username, $password, $email) {
    if (cookieTest($foowd)) {
      $object = &new $className($foowd, $username, $password, $email);
      if ($object->objectid != 0) {
        if ($authType != 'ip') {
          return 0; // created ok
        } else {
          return 1; // created ok, ip auth so you can't log in
        }
      } else {
        return 3; // eek, error creating user.
      }
    } else {
      return 2; // need cookie, support not found
    }
  }
  
  /**
   * Get user a new password if it has been lost.
   *
   * @access private
   * @static
   * @param object foowd The foowd environment object.
   * @param str className The name of the user class.
   * @param str username The name of the user to fetch the password for.
   * @param str queryUsername Username given for stage 2 of the retrieval process.
   * @param str id The ID given for stage 2 of the process.
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
  function fetchPassword(&$foowd, $className, $username, $queryUsername = NULL, $id = NULL) {

    if ($id == NULL && $username != '') { // user given username, fetch user and send stage 1 e-mail requrest 

      $user = &$foowd->getObj(array('objectid' => crc32(strtolower($username)), 'classid' => crc32(strtolower($foowd->user_class))));
      if (isset($user->title) && strtolower($user->title) == strtolower($username)) {
        if (isset($user->email)) {
          $foowd->template->assign('sitename', $foowd->sitename);
          $foowd->template->assign('username', $user->title);
          $foowd->template->assign('hostname', $_SERVER['SERVER_NAME']);
          $foowd->template->assign('class', $className);
          $foowd->template->assign('id', md5($user->updated.$user->title));
          $message = $foowd->template->fetch('fetchpwd_request.tpl');
          if (email(
            $foowd,
            $user->email,
            sprintf(_("%s - Password Change Request"), $foowd->sitename),
            $message,
            'From: '.$foowd->webmaster_email.'\r\nReply-To: '.$foowd->noreply_email
          )) {
            return 1; // password change request e-mail sent
          } else {
            return 2; // could not send e-mail due to technical problem
          }
        } else {
          return 3; // user has no e-mail address
        }
      } else {
        return 4; // user does not exist
      }

    } elseif ($id != '' && $queryUsername != '') { // user returned after stage 1 e-mail, change password and send stage 2 e-mail

      $user = &$foowd->getObj(array('objectid' => crc32(strtolower($username)), 'classid' => crc32(strtolower($foowd->user_class))));
      if (
        isset($user->title) &&
        strtolower($user->title) == strtolower($queryUsername)
      ) {
        if ($id == md5($user->updated.$user->title)) {
          $newPassword = '';
          srand(time());
          for($foo = 0; $foo < rand(6,12); $foo++) { $newPassword .= chr(rand(97, 122)); }
          $salt = getConstOrDefault($foowd->password_salt, '');
          $foowd->template->assign('sitename', $foowd->sitename);
          $foowd->template->assign('username', $user->title);
          $foowd->template->assign('password', $newPassword);
          $foowd->template->assign('hostname', $_SERVER['SERVER_NAME']);
          $foowd->template->assign('class', $className);
          $message = $foowd->template->fetch('fetchpwd_response.tpl');
          if (email(
            $foowd,
            $user->email,
            sprintf(_("%s - Password Change Request"), $foowd->sitename),
            $message,
            'From: '.$foowd->webmaster_email.'\r\nReply-To: '.$foowd->noreply_email
          )) {
            $user->set('password', md5($salt.$newPassword));
            return 5; // password changed and e-mail sent
          } else {
            return 6; // could not send e-mail due to technical problem
          }
        } else {
          return 7; // id does not match
        }
      } else {
        return 8; // user does not exist
      }

    } else {
      return 0; // nothing, display form
    }
  }

  /**
   * Update the users properties.
   *
   * @param str email The users new e-mail address.
   * @return bool TRUE on success.
   */
  function updateUser($email) {
    if ($email != $this->email) {
      $this->set('email', $email);
    }
    return TRUE;
  }

  /**
   * Update the users password.
   *
   * @param str pwd1 The users new password.
   * @param str pwd2 Verification of the new password.
   * @return int 0 = updated<br />
   *             1 = passwords do not match<br />
   *             2 = not updated<br />
   */
  function updatePassword($pwd1, $pwd2) {
    if ($pwd1 != $pwd2) {
      return 1; // passwords do not match
    } elseif ($pwd1 != '') {
      $this->set('password', md5($this->foowd->password_salt.strtolower($pwd1)));
      if ($this->foowd->user_authType == 'cookie') {
        include_once(FOOWD_DIR.'input.cookie.php');
        $cookiePassword = new input_cookie('password', '/^[a-z0-9]{32}$/', NULL);
        $cookiePassword->set($this->password);
      }
      return 0; // updated
    }
    return 2; // not updated
  }

  /**
   * Update the groups the user belongs to.
   *
   * @param array selectedGroups The groups selected for the user to be in.
   * @param array allGroups All the user groups in the system.
   * @return bool TRUE if successful.
   */
  function groups(&$selectedGroups, &$allGroups) {
    $changed = FALSE;
    if ($selectedGroups == $this->groups) { // box has been emptied so empty array
      $selectedGroups = array();
    }
    $groups = $this->groups;
    foreach ($allGroups as $group => $name) { // remove groups in list that have been unselected
      if (!in_array($group, $selectedGroups)) {
        $key = array_search($group, $this->groups);
        if ($key !== FALSE) {
          unset($groups[$key]);
          $changed = TRUE;
        }
      }
    }
    foreach ($selectedGroups as $group) { // add groups that have been selected
      if (!in_array($group, $this->groups)) {
        $groups[] = $group;
        $changed = TRUE;
      }
    }
    if ($changed) {
      if ($this->set('groups', $groups)) {
        return TRUE;
      } else {
        trigger_error('Could not update user groups.');
      }
    }
    return FALSE;
  }
  
/* Class methods */

  /**
   * Output an object creation form and process its input.
   *
   * @static
   * @param object foowd The foowd environment object.
   * @param str className The name of the class.
   */
  function class_create(&$foowd, $className) {
    $foowd->track('foowd_user->class_create');

    include_once(FOOWD_DIR.'input.querystring.php');
    include_once(FOOWD_DIR.'input.textbox.php');
    include_once(FOOWD_DIR.'input.form.php');
    
    $queryTitle = new input_querystring('title', REGEX_TITLE, NULL);
    $createUsername = new input_textbox('createUsername', REGEX_TITLE, $queryTitle->value, _("Username").':');
    $createPassword = new input_passwordbox('createPassword', REGEX_PASSWORD, NULL, _("Password").':');
    $createEmail = new input_textbox('createEmail', REGEX_EMAIL, NULL, _("E-mail Address").':', NULL, NULL, NULL, FALSE);
    $createForm = new input_form('createForm', NULL, 'POST', _("Create"), NULL);
    if ($createForm->submitted()) {
    
      if (!$createUsername->wasSet) {
        $foowd->template->assign('success',  FALSE);
        $foowd->template->assign('error',  1);
      } elseif (!$createPassword->wasSet) {
        $foowd->template->assign('success',  FALSE);
        $foowd->template->assign('error',  2);
      } elseif ($createUsername->value != '' && $createPassword->value != '') {
        switch (call_user_func(array($className, 'create'), &$foowd, $className, $foowd->user_authType, $createUsername->value, $createPassword->value, $createEmail->value)) {
        case 0:
          $foowd->template->assign('success',  TRUE);
          $foowd->template->assign('class', $className);
          $foowd->template->assign('username', htmlspecialchars($createUsername->value));
          break;
        case 1:
          $foowd->template->assign('success',  TRUE);
          $foowd->template->assign('webmaster', $foowd->webmaster_email);
          break;
        case 2:
          $foowd->template->assign('success',  FALSE);
          $foowd->template->assign('error',  3);
          break;
        case 3:
          $foowd->template->assign('success',  FALSE);
          $foowd->template->assign('error',  4);
          break;
        }
      }
      
    }

    if (!cookieTest($foowd)) {
      $foowd->template->assign('cookie', TRUE);
    }
    $createForm->addObject($createUsername);
    $createForm->addObject($createPassword);
    $createForm->addObject($createEmail);
    $foowd->template->assign_by_ref('form', $createForm);

    $foowd->track();
  }

  /**
   * Output a login form and process its input.
   *
   * @static
   * @param object foowd The foowd environment object.
   * @param str className The name of the class.
   */
  function class_login(&$foowd, $className) {
    $foowd->track('foowd_user->class_login');
  
    if ($foowd->user_authType == 'cookie') {
    
      include_once(FOOWD_DIR.'input.querystring.php');
      include_once(FOOWD_DIR.'input.textbox.php');
      include_once(FOOWD_DIR.'input.checkbox.php');
      include_once(FOOWD_DIR.'input.form.php');
    
      $usernameQuery = new input_querystring('username', REGEX_TITLE, '');
      $loginUsername = new input_textbox('loginUsername', REGEX_TITLE, $usernameQuery->value, _("Username").':');
      $loginPassword = new input_passwordbox('loginPassword', REGEX_PASSWORD, NULL, _("Password").':');
      $loginCookie = new input_checkbox('loginCookie', TRUE, 'Keep me logged in on this computer');
      $loginForm = new input_form('loginForm', NULL, 'POST', _("Log In"), NULL);
      $loginForm->addObject($loginUsername);
      $loginForm->addObject($loginPassword);
      $loginForm->addObject($loginCookie);
      if ($loginForm->submitted()) {
        $result = call_user_func(
          array($className, 'login'),
          $foowd,
          $foowd->user_authType,
          $loginUsername->value,
          $loginPassword->value,
          $loginCookie->checked
        );
      } else {
        $result = 1;
      }
    } else {
      $result = call_user_func(
        array($className, 'login'),
        $foowd,
        $foowd->user_authType
      );
    }

    if ($result == 0) {
      $foowd->template->assign('success', TRUE);
    } elseif ($result == 1) {
      if (!cookieTest($foowd)) {
        $foowd->template->assign('cookie', TRUE);
      }
      $foowd->template->assign_by_ref('form', $loginForm);
    } else {
      $foowd->template->assign('success', FALSE);
      $foowd->template->assign('error', $result);
      $foowd->template->assign('username', htmlspecialchars($loginUsername->value));
      $foowd->template->assign_by_ref('form', $loginForm);
    }

    $foowd->template->assign('class', $className);

    $foowd->track();
  }

  /**
   * Log the user out and display a log out screen.
   *
   * @static
   * @param object foowd The foowd environment object.
   * @param str className The name of the class.
   */
  function class_logout(&$foowd, $className) {
    $foowd->track('foowd_user->class_logout');
    
    $result = call_user_func(array($className, 'logout'), $foowd, $foowd->user_authType);

    if ($result == 0 || $result == 1) {
      $foowd->template->assign('success', TRUE);
    } else {
      $foowd->template->assign('success', FALSE);
      $foowd->template->assign('error', $result);
    }
    
    $foowd->track();
  }
  
  /**
   * Output a fetch password form and process its input.
   *
   * @static
   * @param object foowd The foowd environment object.
   * @param str className The name of the class.
   */
  function class_lostPassword(&$foowd, $className) {
    $foowd->track('foowd_user->class_lostPassword');

    include_once(FOOWD_DIR.'input.querystring.php');
    include_once(FOOWD_DIR.'input.textbox.php');
    include_once(FOOWD_DIR.'input.form.php');

    $usernameQuery = new input_querystring('username', REGEX_TITLE, NULL);
    $idQuery = new input_querystring('id', '/[a-z0-9]{32}/', NULL);
    $lostUsername = new input_textbox('lostUsername', REGEX_TITLE, $usernameQuery->value, _("Username").':');
    $lostForm = new input_form('lostForm', NULL, 'POST', _("Retrieve Password"), NULL);

    switch (call_user_func(array($className, 'fetchPassword'), &$foowd, $className, $lostUsername->value, $usernameQuery->value, $idQuery->value)) {
    case 0: // done nothing, display form
      $lostForm->addObject($lostUsername);
      $foowd->template->assign_by_ref('form', $lostForm);
      break;
    case 1: // stage 1 complete
      $foowd->template->assign('stage1', TRUE);
      break;
    case 2: // could not send e-mail, technical problem
      $foowd->template->assign('failure', 2);
      $foowd->template->assign('webmaster', $foowd->webmaster_email);
      break;
    case 3: // could not send e-mail, user does not have an e-mail address listed
      $foowd->template->assign('failure', 3);
      $foowd->template->assign('username', htmlspecialchars($lostUsername->value));
      $foowd->template->assign('webmaster', $foowd->webmaster_email);
      break;
    case 4: // could not send e-mail, user does not exist
      $foowd->template->assign('failure', 4);
      $foowd->template->assign('username', htmlspecialchars($lostUsername->value));
      break;
    case 5: // stage 2 complete
      $foowd->template->assign('stage2', TRUE);
      $foowd->template->assign('class', $className);
      $foowd->template->assign('username', $lostUsername->value);
      break;
    case 6: // could not change password, technical problem
      $foowd->template->assign('failure', 6);
      $foowd->template->assign('webmaster', $foowd->webmaster_email);
      break;
    case 7: // could not change password, id does not match
      $foowd->template->assign('failure', 7);
      $foowd->template->assign('webmaster', $foowd->webmaster_email);
      $foowd->template->assign('class', $className);
      $foowd->template->assign('username', $usernameQuery->value);
      break;
    case 8: // could not change password, could not find user
      $foowd->template->assign('failure', 8);
      $foowd->template->assign('webmaster', $foowd->webmaster_email);
      break;
    }
    $foowd->track();
  }

/* Object methods */

  /**
   * Output the object.
   */
  function method_view() {
    $this->foowd->track('foowd_user->method_view');
    
    $this->foowd->template->assign('username', $this->getTitle());
    if ($this->email) {
      $this->foowd->template->assign('email', htmlspecialchars(mungEmail($this->email)));
    }
    $this->foowd->template->assign('created', date(DATETIME_FORMAT, $this->created));
    $this->foowd->template->assign('created_since', timeSince($this->created));
    $this->foowd->template->assign('lastvisit',  date(DATETIME_FORMAT, $this->updated));
    $this->foowd->template->assign('lastvisit_since', timeSince($this->updated));
    if ($this->foowd->user->objectid == $this->objectid) {
      $this->foowd->template->assign('update', TRUE);
      $this->foowd->template->assign('objectid', $this->objectid);
      $this->foowd->template->assign('classid', $this->classid);
    }

    $this->foowd->track();
  }

  /**
   * Output a user update form and process its input.
   */
  function method_update() {
    $this->foowd->track('foowd_user->method_update');
    
    include_once(FOOWD_DIR.'input.form.php');
    include_once(FOOWD_DIR.'input.textbox.php');
    
    $updateForm = new input_form('updateForm', NULL, 'POST', _("Update"));
    $email = new input_textbox('email', REGEX_EMAIL, $this->email, _("E-mail").': ');
    $updateForm->addObject($email);
    $this->foowd->template->assign_by_ref('form', $updateForm);
    if ($updateForm->submitted()) {
      if ($this->updateUser($email->value)) {
        $this->foowd->template->assign('success', TRUE);
      } else {
        $this->foowd->template->assign('success', FALSE);
      }
    }

    $passwordForm = new input_form('passwordForm', NULL, 'POST', _("Change Password"), NULL);
    $password = new input_passwordbox('password', REGEX_PASSWORD, '', _("Password").': ');
    $password2 = new input_passwordbox('password2', REGEX_PASSWORD, '', _("Verify").': ');
    $passwordForm->addObject($password);
    $passwordForm->addObject($password2);
    $this->foowd->template->assign_by_ref('password_form', $passwordForm);
    if ($passwordForm->submitted()) {
      $result = $this->updatePassword($password->value, $password2->value);
      if ($result == 0) {
        $this->foowd->template->assign('success', TRUE);
        $this->foowd->template->assign('class', get_class($this));
        $this->foowd->template->assign('username', $this->getTitle());
      } else {
        $this->foowd->template->assign('success', FALSE);
        $this->foowd->template->assign('error', $result);
      }
    }

    $this->foowd->track();
  }

  /**
   * Output a user group update form and process its input.
   */
  function method_groups() {
    $this->foowd->track('foowd_object->method_groups');

    include_once(FOOWD_DIR.'input.form.php');
    include_once(FOOWD_DIR.'input.dropdown.php');

    $permissionForm = new input_form('permissionForm', NULL, 'POST');

    $groups = $this->foowd->getUserGroups(FALSE);

    $permissionBox = new input_dropdown('permissionGroups', $this->groups, $groups, _("User Groups").':', count($groups), TRUE);
    $permissionForm->addObject($permissionBox);
    $this->foowd->template->assign_by_ref('form', $permissionForm);

    if ($permissionForm->submitted()) {
      if ($this->groups($permissionBox->value, $groups)) {
        $this->foowd->template->assign('success', TRUE);
      } else {
        $this->foowd->template->assign('success', FALSE);
      }
    }

    $this->foowd->track();
  }

}

?>

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

setClassMeta('base_user', 'User');
setConst('USER_CLASS_ID', META_BASE_USER_CLASS_ID);
setConst('USER_CLASS_NAME', 'base_user');

global $USER_SOURCE;
$USER_SOURCE = array('table' => 'smdoc_user',
                     'table_create' => array(getClassname(USER_CLASS_ID),'makeTable'));

setPermission('base_user', 'class','login', 'Everyone');
setPermission('base_user', 'class','logout','Registered');
setPermission('base_user', 'class','create','Everyone');
setPermission('base_user', 'object', 'groups', 'Gods');

setPermission('base_user', 'object', 'clone', 'Nobody');
setPermission('base_user', 'object', 'admin', 'Nobody');
setPermission('base_user', 'object', 'xml', 'Nobody');
setPermission('base_user', 'object', 'permissions', 'Nobody');
setPermission('base_user', 'object', 'history', 'Nobody');

include_once(SM_DIR . 'class.anonuser.php');

/**
 * The smdoc extended user class.
 *
 * Class for holding information about a user and providing methods for
 * manipulating and getting information on a user.
 *
 */
class base_user extends foowd_object
{
  /**
   * Fetch User
   *
   * @param object foowd The foowd environment object.
   * @return retrieved foowd user or anonymous user instance
   */
  function &factory(&$foowd)
  {
    $foowd->track('base_user::factory');

    $user_info = array();
    $new_user = NULL;

    $user_info = base_user::getUserDetails($foowd);
    if ( isset($user_info['username']) )
      $new_user =& base_user::fetchUser($foowd, $user_info);

    // If loading the user is unsuccessful (or unattempted),
    // fetch an anonymous user
    if ( $new_user == NULL )
      $new_user =& base_user::fetchAnonymousUser($foowd);

    $foowd->track();
    return $new_user;
  }

  /**
   * Create Anonymous Foowd User
   *
   * @param object foowd The foowd environment object.
   * @return new instance of anonymous user class.
   */
  function &fetchAnonymousUser(&$foowd)
  {
    $anonUserClass = getConstOrDefault('ANONYMOUS_USER_CLASS', 'foowd_anonuser');
    if (class_exists($anonUserClass)) {
      return new $anonUserClass($foowd);
    } else {
      trigger_error('Could not find anonymous user class.', E_USER_ERROR);
    }
  }

  /**
   * Fetch Foowd User
   *
   * @param object foowd The foowd environment object.
   * @param mixed userArray Array containing user information (userid, password).
   * @return retrieved foowd user or FALSE on failure.
   */
  function &fetchUser(&$foowd, $userArray = NULL)
  {
    global $USER_SOURCE;

    $foowd->track('base_user::fetchUser', $userArray);

    if ( isset($userArray['objectid']) )
      $where['objectid'] = $userArray['objectid'];
    elseif ( isset($userArray['username']) )
      $where['title'] = $userArray['username'];
    else
      return FALSE;
    $indices = 

    $user =& $foowd->getObj($where, $USER_SOURCE, NULL, FALSE);
    $foowd->track();
    return $user;
  }

  /**
   * Get user details from an external mechanism.
   *
   * If not already set, populate the user array with the user classid and
   * fetch the username and password of the current user from one of the input
   * mechanisms
   *
   * @param object foowd The foowd environment object.
   * @return array The resulting user array.
   */
  function getUserDetails(&$foowd) 
  {
    $session_userinfo = new input_session('userinfo', NULL, NULL, TRUE);
    if ( !$session_userinfo->wasSet || 
         $session_userinfo->value == NULL )
      return FALSE;

    $user_info = $session_userinfo->value;
    $user = array();

    $user['username'] = $user_info['username'];
    $user['password'] = $user_info['password'];
    if ( isset($user_info['objectid']) )
      $user['objectid'] = $user_info['objectid'];

    return $user;
  }

  /**
   * Make a Foowd database table.
   *
   * When a database query fails due to a non-existant database table, this
   * method is envoked to create the missing table and execute the SQL
   * statement again.
   *
   * @param object foowd The foowd environment object.
   * @param str SQLString The original SQL string that failed to execute due to missing database table.
   * @return mixed The resulting database query resource or FALSE on failure.
   */
  function makeTable(&$foowd) 
  {
    global $USER_SOURCE;

    $foowd->track('base_user->makeTable');
    $sql = 'CREATE TABLE `'.$USER_SOURCE['table'].'` (
              `objectid` int(11) NOT NULL default \'0\',
              `title` varchar(32) NOT NULL default \'\',
              `object` longblob,
              `updated` datetime NOT NULL default \'0000-00-00 00:00:00\',
              PRIMARY KEY  (`objectid`),
              KEY `idxuser_updated` (`updated`),
              KEY `idxuser_title` (`title`)
            );';
    $result = $foowd->database->query($sql);
    $foowd->track();
    return $result;
  }

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
  var $groups;

  /**
   * Constructs a new user.
   *
   * @constructor base_user
   * @param object foowd The foowd environment object.
   * @param optional str username The users name.
   * @param optional str password An MD5 hash of the users password.
   * @param optional str email The users e-mail address.
   * @param optional array groups The user groups the user belongs to.
   * @param optional str hostmask The users hostmask.
   */
  function base_user( &$foowd,
                   $username = NULL,
                   $password = NULL,
                   $email = NULL,
                   $objectid = NULL)
  {
    global $USER_SOURCE;
    $foowd->track('base_user->constructor');

    $this->foowd =& $foowd;

    // Don't use workspace id when looking for unique title 
    if ( $objectid == NULL && 
         !$this->isTitleUnique($username, FALSE, $objectid, $USER_SOURCE) )
    {
      $this->objectid = 0;
      $foowd->track(); 
      return FALSE;
    }

    // init meta arrays
    $this->__wakeup();
 
    // Initialize variables
    $this->title = $username;
    $this->objectid = $objectid;
    $this->workspaceid = 0;
    $this->classid = USER_CLASS_ID;

    $this->creatorid = $this->objectid; // created by self
    $this->creatorName = $this->title;  // created by self
    $this->created = time();
    $this->updatorid = $this->objectid; // updated by self
    $this->updatorName = $this->title;  // updated by self
    $this->updated = time();

    $this->groups = array();

    $salt = $foowd->config_settings['user']['password_salt'];
    $this->password = md5($salt.$password);
    $this->email = $email;
    
    // set original access vars
    $this->foowd_original_access_vars['title'] = $this->title;
    $this->foowd_original_access_vars['objectid'] = $this->objectid;
    $this->foowd_original_access_vars['workspaceid'] = $this->workspaceid;

    // add to loaded object reference list
    $foowd->database->addToLoadedReference($this, $USER_SOURCE);

    // object created successfuly, queue for saving
    $this->foowd_changed = TRUE;      

    $foowd->track();
  }

  /**
   * Serialisation wakeup method.
   */
  function __wakeup() 
  {
    parent::__wakeup();

    global $USER_SOURCE;
    $this->foowd_source = $USER_SOURCE;

    // add some regex verification
    unset($this->foowd_vars_meta['version']);
    $this->foowd_vars_meta['password'] = '/^[a-z0-9]{32}$/'; // this is not set to the password regex as it's stored as an md5
    $this->foowd_vars_meta['email'] = REGEX_EMAIL;
    $this->foowd_vars_meta['groups'] = REGEX_GROUP;
    $this->foowd_vars_meta['title'] = REGEX_TITLE;

    // re-arrange our indices
    unset($this->foowd_indexes['version']);
    unset($this->foowd_indexes['classid']);
    unset($this->foowd_indexes['workspaceid']);

    // Original access vars
    unset($this->foowd_original_access_vars['version']);
    $this->foowd_original_access_vars['classid'] = USER_CLASS_ID;
    $this->foowd_original_access_vars['title'] = $this->title;

    // Default primary key
    $this->foowd_primary_key = array('objectid');    
  }

  /**
   * Whether a user is in a user group.
   *
   * @param str groupName Name of the group to check.
   * @param int creatorid The userid of the creator .
   * @return bool TRUE or FALSE.
   */
  function inGroup($groupName, $creatorid = NULL) 
  {
    if ($groupName == 'Everyone')               // group is everyone
      return TRUE;
    if ($groupName == 'Nobody')                 // group is nobody
      return FALSE;
    if ($groupName == 'Registered' )            // group is any registered user (not anonymous)
      return TRUE;
    if ( $groupName == 'Author' && 
         $creatorid != NULL && $this->objectid != NULL && 
         $this->objectid == $creatorid)           // group is author and so is user
      return TRUE;

    if ( is_array($this->groups) )
    {
      if ( in_array($groupName, $this->groups) )  // user is in group
        return TRUE;
      if ( in_array('Gods', $this->groups) )
        return TRUE;
    }

    return FALSE;
  }
 

  /**
   * Check the string is the users password.
   *
   * @param str password The password to check.
   * @param bool plainText The password is in plain text rather than an md5 hash.
   * @return bool Returns TRUE if the passwords match.
   */
  function passwordCheck($password, $plainText = FALSE) 
  {
    if ($plainText) 
    {
      $salt = $this->foowd->config_settings['user']['password_salt'];
      $password = md5($salt.$password);
    }

    if ( $this->password === $password )
      return TRUE;
    
    return FALSE;
  }

  /**
   * Check if the user is the anonymous user.
   *
   * @return bool Returns TRUE if the user is of the anonymous user class.
   */
  function isAnonymous() 
  {
    return FALSE;
  }

  /**
   * Log the user in.
   *
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
  function login(&$foowd, $username = FALSE, $password = NULL) 
  {
    if ( !$foowd->user->isAnonymous() ) 
      return 5;                             // user already logged in
    if ( !$username ) 
      return 1;                             // no user given

    $user_info['username'] = $username;

    $newuser =& base_user::fetchUser($foowd, $user_info);
    if ( !is_object($newuser) || 
         strtolower($newuser->title) != strtolower($username)) 
      return 2;                             // unknown user

    $salt = $foowd->config_settings['user']['password_salt'];
    if ( $newuser->password != md5($salt.$password) )
      return 3;                             // bad password

    $user_info['password'] = md5($salt.$password);
    $user_info['objectid'] = $newuser->objectid;

    // save user information
    $foowd->user = $newuser;
    $foowd->user->update();

    $session_userinfo = new input_session('userinfo', NULL, NULL, TRUE); 
    $session_userinfo->set($user_info);
    return 0;                               // logged in successfully
  }

  /**
   * Log out the user.
   *
   * @param object foowd The foowd environment object.
   * @param optional str authType The type of user authentication to use.
   * @return int 0 = cookie logged out successfully<br />
   *             1 = http logged out successfully<br />
   *             2 = ip auth, can not log out<br />
   *             3 = user already logged out<br />
   *             4 = http log out failed due to browser<br />
   */
  function logout(&$foowd) 
  {
    if ( $foowd->user->isAnonymous() ) 
      return 3; // user already logged out

    $foowd->user = base_user::fetchAnonymousUser($foowd);

    $session_userinfo = new input_session('userinfo', NULL, NULL, true); 
    $session_userinfo->set(NULL);
        
    return 0; // logged out successfully
  }

  /**
   * Create a new user.
   *
   * @param object foowd The foowd environment object.
   * @param str username The name of the user to create.
   * @param str password The password of the user.
   * @param str email The e-mail address of the user.
   * @return int 0 = created ok<br />
   *             1 = created ok, ip auth so you can't log in<br />
   *             2 = need cookie, support not found<br />
   *             3 = eek, error creating user<br />
   *             4 = duplicate user name<br />
   */
  function create(&$foowd, $username, $password, $email) 
  {
    global $USER_SOURCE;

    // no workspaceid, calculate new objectid.
    if ( !$foowd->database->isTitleUnique($username, FALSE, $objectid, $USER_SOURCE, TRUE) )
      return 4;

    $class = USER_CLASS_NAME;
    $object = new $class($foowd, $username, $password, $email, $objectid);
    if ( $object->objectid != 0 && $object->save($foowd) ) 
      return 0; // created ok
    else
      return 3; // eek, error creating user.
  }

  /**
   * Get user a new password if it has been lost.
   *
   * @access private
   * @static
   * @param object foowd The foowd environment object.
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
  function fetchPassword(&$foowd, $username, $queryUsername = NULL, $id = NULL) 
  {
    if ( $username == '' ) 
      return 0;                             // nothing, display form

    $user_info['username'] = $username;
    $lostuser =& base_user::fetchUser($foowd, $user_info);

    if ( !$lostuser || !isset($lostuser->title) || 
         strtolower($lostuser->title) != strtolower($username) )
      return 4;                             // user does not exist

    if ( !isset($lostuser->email) )
      return 3;                             // user has no e-mail address

    $site = $foowd->config_settings['site']['site_name'];
 
    // We have username only, send stage one email
    if ( $id == NULL && $queryUsername == NULL ) 
    {
      $foowd->template->assign('sitename', $site);
      $foowd->template->assign('username', $lostuser->title);
      $foowd->template->assign('hostname', $_SERVER['SERVER_NAME']);
      $foowd->template->assign('class', 'base_user');
      $foowd->template->assign('id', md5($user->updated.$user->title));
      $message = $foowd->template->fetch('fetchpwd_request.tpl');

      $result = email($foowd, $lostuser->email, 
                      sprintf(_("%s - Password Change Request"), $site), 
                      $message,
                      'From: '.$foowd->config_settings['site']['email_webmaster']
                       .'\r\nReply-To: '.$foowd->config_settings['site']['email_noreply']);
      if ( $result )
        return 1;                           // password change request e-mail sent
      else
        return 2;                           // could not send e-mail due to technical problem
    } 
    // We have id and query, change password and send confirmation
    else 
    {
      if ( strtolower($lostuser->title) != strtolower($queryUsername) )
        return 8;                           // user does not exist
      if ($id != md5($lostuser->updated.$lostuser->title)) 
        return 7;                           // id does not match
        
      $newPassword = '';
      $foo_len = rand(6,12);
      srand(time());
      for($foo = 0; $foo < $foo_len; $foo++) 
        $newPassword .= chr(rand(97, 122)); 

      $lostuser->set('password', md5($salt.$newPassword));
      
      $salt = $this->foowd->config_settings['user']['password_salt'];
      $foowd->template->assign('sitename', $site);
      $foowd->template->assign('username', $user->title);
      $foowd->template->assign('password', $newPassword);
      $foowd->template->assign('hostname', $_SERVER['SERVER_NAME']);
      $foowd->template->assign('class', 'base_user');
      $message = $foowd->template->fetch('fetchpwd_response.tpl');

      $result = email($foowd, $lostuser->email, 
                      sprintf(_("%s - Password Change Request"), $site), 
                      $message,
                      'From: '.$foowd->config_settings['site']['email_webmaster']
                       .'\r\nReply-To: '.$foowd->config_settings['site']['email_noreply']);

      if ( $result )
        return 5;                           // password changed and e-mail sent
      else
        return 6;                           // could not send e-mail due to technical problem (or could not save new password)
    }
    return 0;                               // nothing, display form
  }

  /**
   * Create form elements for the update form from the objects member variables.
   *
   * @param  object form The form to add the form items to.
   * @param  array  error If error is encountered, add message to this array
   */
  function addUserItemsToForm(&$form, &$error) 
  {
    global $USER_SOURCE;

    // Add regular elements to form
    include_once(INPUT_DIR.'input.textbox.php');
    include_once(INPUT_DIR.'input.dropdown.php');
    include_once(INPUT_DIR.'input.checkbox.php');

    $titleBox = new input_textbox('title', REGEX_TITLE, $this->title, 'Username', FALSE);
    $emailBox = new input_textbox('email', REGEX_EMAIL, $this->email, 'Email', FALSE);
    $showEmail = new input_checkbox('show_email', $this->show_email, 'Share Email');

    if ( $form->submitted() )
    {
      if ( !empty($titleBox->value) && $titleBox->value != $this->title )
      {
        $unique = !$this->isTitleUnique($titleBox->value, FALSE, $objectid, $USER_SOURCE, FALSE) ;
        if ( $unique )
          $this->set('title', $titleBox->value);
        else
        {
          $titleBox->wasValid = FALSE;
          $error[] = _("User already exists, please choose a new name.");
        }
      }

      if ( !empty($emailBox->value) && $emailBox->value != $this->email )
        $this->set('email', $emailBox->value);
      if ( $showEmail->checked != $this->show_email )
        $this->set('show_email', $showEmail->checked);
    }
    
    $form->addObject($titleBox);
    $form->addObject($emailBox);
    $form->addObject($showEmail);
    
    $this->addPasswordItemsToForm($form, $error);

    // If something changed, update the 
    // status of email as a public contact item based on 
    // new values
    if ( $error == NULL && $form->submitted() && $this->foowd_changed )
    {
      if ( $this->show_email && $this->email )
        $this->IM_nicks['Email'] = $this->email;
      else
        unset($this->IM_nicks['Email']);
    } 
  }

  /**
   * Create form elements for the update form from the objects member variables.
   *
   * @param  object form  The form to add the form items to. 
   * @param  array  error If error is encountered, add message to this array
   */
  function addPasswordItemsToForm(&$form, &$error) 
  {
    include_once(INPUT_DIR.'input.textbox.php');

    $verify = new input_passwordbox('verify', REGEX_PASSWORD, NULL, 'Verify', FALSE);
    $password = new input_passwordbox('password', REGEX_PASSWORD, NULL, 'Password', FALSE);

    if ( $form->submitted() &&
         (!empty($password->value) || !empty($verify->value)) )
    {
      if ( $password->wasValid && !empty($password->value) && 
           $verify->wasValid && $password->value == $verify->value )
      {
        $salt = $this->foowd->config_settings['user']['password_salt'];
        $this->set('password', md5($salt.$password->value));
      }
      else 
      {
        $password->wasValid = FALSE;
        $verify->wasValid = FALSE;
        $error[] = _("Passwords must be at least 4 characters, and must match.");
      }
    }

    $form->addObject($verify);
    $form->addObject($password);
  }

  /**
   * Update the groups the user belongs to.
   *
   * @param array selectedGroups The groups selected for the user to be in.
   * @param array allGroups All the user groups in the system.
   */
  function addGroupsToForm(&$form, &$error) 
  {
    include_once(INPUT_DIR.'input.dropdown.php');

    // Create array of groups
    $allGroups['None'] = 'None';
    $allGroups += $this->foowd->getUserGroups(FALSE);
    $groups = empty($this->groups) ? 'None' : $this->groups;
    $groupBox = new input_dropdown('groups', $groups, $allGroups, 'User Groups', TRUE);

    if ( $form->submitted() )
    {
      $new_groups = array();
      $grps = $groupBox->value;

      // If none selected, remove user from all groups
      if ( in_array('None', $grps) )
        $this->foowd->groups->removeUser($this->objectid, $this->groups);
      else
      {
        $remove_groups = array();
        $ok_groups = array();
        foreach ( $allGroups as $id => $name )
        {
          if ( in_array($id, $this->groups) )
          {
            if ( !in_array($id, $grps) )
              $remove_groups[] = $id;
            else
              $ok_groups[] = $id;
          }
          elseif ( in_array($id, $grps) )
            $new_groups[] = $id;
        }
        $this->foowd->groups->removeUser($this->objectid, $remove_groups);
        $this->foowd->groups->addUser($this->objectid, $new_groups);
        $new_groups = array_merge($new_groups, $ok_groups);
      }
      $this->set('groups', $new_groups);
    }

    $form->addObject($groupBox);
  }

// ----------------------------- class methods --------------

  /**
   * Output an object creation form and process its input.
   *
   * @class base_user
   * @method class_create
   * @param object foowd The foowd environment object.
   * @param str className The name of the class.
   */
  function class_create(&$foowd, $className) 
  {
    $foowd->track('base_user->class_create');

    include_once(INPUT_DIR . 'input.textbox.php');
    include_once(INPUT_DIR . 'input.form.php');
    
    $queryTitle = new input_querystring('title', REGEX_TITLE, NULL);
    $createUsername = new input_textbox('createUsername', REGEX_TITLE, $queryTitle->value, 'Username');

    $verifyPassword = new input_passwordbox('verifyPassword', REGEX_PASSWORD, NULL, 'Verify');
    $createPassword = new input_passwordbox('createPassword', REGEX_PASSWORD, NULL, 'Password');

    $createEmail = new input_textbox('createEmail', REGEX_EMAIL, NULL, 'Email Address', FALSE);
    $createForm = new input_form('createForm', NULL, SQ_POST);

    if ( $createForm->submitted() && 
         $createUsername->wasSet && $createUsername->wasValid && $createUsername != '' )
    {
      if ( $createPassword->wasSet && $createPassword->wasValid &&
           $verifyPassword->wasSet && $verifyPassword->wasValid &&
           $createPassword->value != '' && $createPassword->value == $verifyPassword->value ) 
      {
        $result = call_user_func(array($className, 'create'), $foowd,  
                                 $createUsername->value,
                                 $createPassword->value,
                                 $createEmail->value);
      }
      else 
        $result = -1;
    }
    else
      $result = -2;

    switch ($result) 
    {
      case 0:
        $_SESSION['ok'] = USER_CREATE_OK;
        $uri_arr['class'] = $className;
        $uri_arr['method'] = 'login';
        $uri_arr['username'] = htmlspecialchars($createUsername->value);
        $foowd->loc_forward(getURI($uri_arr, FALSE));
        exit;
      case -1: 
        $foowd->template->assign('failure', _("Passwords must be at least 4 characters, and must match."));
        $verifyPassword->set(NULL);
        $createPassword->set(NULL);
        break;
      case -2:
        $foowd->template->assign('failure', FORM_FILL_FIELDS);
        break;
      case 3: 
        $foowd->template->assign('failure', _("Could not create user."));
        break;
      case 4:
        $foowd->template->assign('failure',  _("User already exists, please choose a new name."));
        break;
    }
        
    $createForm->addObject($createUsername);
    $createForm->addObject($createPassword);
    $createForm->addObject($verifyPassword);
    $createForm->addObject($createEmail);
    $foowd->template->assign_by_ref('form', $createForm);

    return;
  }

  /**
   * Output a login form and process its input.
   *
   * @class base_user
   * @method class_login
   * @param object foowd The foowd environment object.
   * @param str className The name of the class.
   */
  function class_login(&$foowd, $className) 
  {
    $foowd->track('base_user->class_login');

    include_once(INPUT_DIR . 'input.textbox.php');
    include_once(INPUT_DIR . 'input.form.php');

    $usernameQuery = new input_querystring('username', REGEX_TITLE, '');
    $loginUsername = new input_textbox('loginUsername', REGEX_TITLE, $usernameQuery->value, 'Username');
    $loginPassword = new input_passwordbox('loginPassword', REGEX_PASSWORD, NULL, 'Password');


    $loginForm = new input_form('loginForm', NULL, SQ_POST, _("Log In"), NULL);
    $loginForm->addObject($loginUsername);
    $loginForm->addObject($loginPassword);

    if ( $loginForm->submitted() ) 
    {
      $result = call_user_func( array($className, 'login'),
                        $foowd, 
                        $loginUsername->value,
                        $loginPassword->value );
    } 
    else 
      $result = -1;

    switch ($result) 
    {
      case 0:
      case 5:
        $_SESSION['ok'] = ( $result == 0 ) ? USER_LOGIN_OK : USER_LOGIN_PREV;
        $uri_arr['objectid'] = $foowd->user->objectid;
        $uri_arr['classid'] = USER_CLASS_ID;
        $url = getURI($uri_arr, FALSE);
        $foowd->loc_forward($url);
        exit;
      case -1:
        $foowd->template->assign('failure', FORM_FILL_FIELDS);
        break;
      case 2:
      case 3:
        $foowd->template->assign('failure', _("User or password is incorrect."));
        break;
    }

    $foowd->template->assign_by_ref('form', $loginForm);       
    $foowd->track(); 
  }

  /**
   * Log the user out and display a log out screen.
   *
   * @class base_user
   * @method class_logout
   * @param object foowd The foowd environment object.
   * @param str className The name of the class.
   */
  function class_logout(&$foowd, $className) 
  {
    $result = call_user_func(array($className, 'logout'), $foowd);
    switch ($result) 
    {
      case 0:
      case 3:
        $_SESSION['ok'] = USER_LOGOUT_OK;
        $uri_arr['class'] = getClassname(USER_CLASS_ID);
        $uri_arr['method'] = 'login';
        $foowd->loc_forward(getURI($uri_arr, FALSE));
        return NULL;
    }
    trigger_error('Unexpected response when logging out user: ' . $result, E_USER_ERROR);
  }

  /**
   * Output a fetch password form and process its input.
   *
   * @static
   * @param object foowd The foowd environment object.
   * @param str className The name of the class.
   */
  function class_lostPassword(&$foowd, $className)
  {
    $foowd->track('base_user->class_lostPassword');

    include_once(INPUT_DIR.'input.querystring.php');
    include_once(INPUT_DIR.'input.textbox.php');
    include_once(INPUT_DIR.'input.form.php');

    $usernameQuery = new input_querystring('username', REGEX_TITLE, NULL);
    $idQuery = new input_querystring('id', '/[a-z0-9]{32}/', NULL);
    $lostUsername = new input_textbox('lostUsername', REGEX_TITLE, $usernameQuery->value, _("Username").':');
    $lostForm = new input_form('lostForm', NULL, 'POST', _("Retrieve Password"), NULL);

    $result = call_user_func(array($className, 'fetchPassword'), 
                             &$foowd, $className, 
                             $lostUsername->value, 
                             $usernameQuery->value, 
                             $idQuery->value);
    switch ($result)
    {
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

// -----------------------------object methods --------------

  /**
   * Output the object.
   *
   * @param object foowd The foowd environment object.
   */
  function method_view() 
  {
    $this->foowd->track('base_user->method_view');
    
    $this->foowd->template->assign('created', date(DATETIME_FORMAT, $this->created));
    $this->foowd->template->assign('lastvisit', date(DATETIME_FORMAT, $this->updated));

    if ( $this->foowd->user->inGroup('Author', $this->creatorid) )
      $this->foowd->template->assign('update', TRUE);

    if ( $this->email )
      $this->foowd->template->assign('email', mungEmail($this->email));

    $this->foowd->track(); 
  }

  /**
   * Output the object administration form and handle its input.
   *
   * @access protected
   */
  function method_groups() 
  {
    $this->foowd->track('base_user->method_groups');

    include_once(INPUT_DIR.'input.form.php');

    $groupsForm = new input_form('groupsForm', NULL, SQ_POST);

    $error = NULL;
    $this->addGroupsToForm($groupsForm, $error);

    if ( $error != NULL )
      $this->foowd->template->assign('failure', $error); 
    elseif ( $groupsForm->submitted() && $this->foowd_changed)
    {
      if ( $this->save() )
      {
        $_SESSION['ok'] = USER_UPDATE_OK;
        $uri_arr['objectid'] = $this->objectid;
        $uri_arr['classid'] = USER_CLASS_ID;        
//        $this->foowd->loc_forward( getURI($uri_arr, FALSE));
      }
      else
        $this->foowd->template->assign('failure', OBJECT_UPDATE_FAILED);
    }

    $this->foowd->template->assign_by_ref('form', $groupsForm);
    $this->foowd->track();
  }

  /**
   * Output a user update form and process its input.
   *
   * @param object foowd The foowd environment object.
   */
  function method_update() 
  {
    $this->foowd->track('base_user->method_update');
    
    include_once(INPUT_DIR . 'input.form.php');

    $updateForm = new input_form('updateForm', NULL, SQ_POST, _("Update Profile"));

    $error = NULL;   
    $this->addUserItemsToForm($updateForm, $error);
    
    if ( $error != NULL )
      $this->foowd->template->assign('failure', $error);
    elseif ( $updateForm->submitted() && $this->foowd_changed )
    {
      if ( $this->save() )
      {
        $_SESSION['ok'] = USER_UPDATE_OK;
        $uri_arr['objectid'] = $this->objectid;
        $uri_arr['classid'] = USER_CLASS_ID;        
        $this->foowd->loc_forward( getURI($uri_arr, FALSE));
      }
      else
        $this->foowd->template->assign('failure', OBJECT_UPDATE_FAILED);
    }

    $this->foowd->template->assign_by_ref('form', $updateForm);
    $this->foowd->track(); 
  }

// ----------------------------- disabled methods --------------

  /**
   * Create a new version of this object. Set the objects version number to the
   * next available version number and queue the object for saving. This will
   * have the effect of creating a new object entry since the objects version
   * number has changed.
   */
  function newVersion() 
  {
    trigger_error('newVersion not supported for base_user', E_USER_ERROR);    
  }

  /**
   * Clean up the archive versions of the object.
   *
   * @param object foowd The foowd environment object.
   * @return bool Returns TRUE on success.
   */
  function tidyArchive() 
  {
    trigger_error('tidyArchive does not apply to base_user' , E_USER_ERROR);
  }

  /**
   * Clone the object.
   *
   * @param object foowd The foowd environment object.
   * @param str title The title of the new object clone.
   * @param str workspace The workspace to place the object clone in.
   * @return bool Returns TRUE on success.
   */
  function clone($title, $workspace) 
  {
    trigger_error('Can not clone users.' , E_USER_ERROR);
  }

  /**
   * Convert variable list to XML.
   *
   * @param array vars The variables to convert.
   * @param array goodVars List of variables to convert.
   */
  function vars2XML($vars, $goodVars) 
  {
    trigger_error('vars2XML does not apply to base_user' , E_USER_ERROR);
  }
}


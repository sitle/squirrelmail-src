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

global $USER_SOURCE;
$USER_SOURCE = array('table' => 'smdoc_user',
                     'table_create' => array('smdoc_user','makeTable'));

setPermission('smdoc_user', 'object', 'clone', 'Nobody');
setPermission('smdoc_user', 'object', 'admin', 'Nobody');
setPermission('smdoc_user', 'object', 'xml', 'Nobody');
setPermission('smdoc_user', 'object', 'permissions', 'Nobody');
setPermission('smdoc_user', 'object', 'history', 'Nobody');

include_once(SM_DIR . 'class.anonuser.php');
include_once(SM_DIR . 'class.user.php');

/**
 * The smdoc extended user class.
 *
 * Class for holding information about a user and providing methods for
 * manipulating and getting information on a user.
 *
 */
class smdoc_user extends foowd_object
{
  /**
   * Fetch User
   *
   * @param object foowd The foowd environment object.
   * @return retrieved foowd user or anonymous user instance
   */
  function &factory(&$foowd)
  {
    $foowd->track('smdoc_user::factory');

    $user_info = array();
    $new_user = NULL;

    $user_info = smdoc_user::getUserDetails($foowd);
    if ( isset($user_info['username']) )
      $new_user =& smdoc_user::fetchUser($foowd, $user_info);

    // If loading the user is unsuccessful (or unattempted),
    // fetch an anonymous user
    if ( $new_user == NULL )
      $new_user =& smdoc_user::fetchAnonymousUser($foowd);

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

    // If we don't have required elements, return early.
    if ( !isset($userArray['username']) )
      return FALSE;

    $foowd->track('smdoc_user::fetchUser', $userArray);

    if ( isset($userArray['objectid']) )
      $where['objectid'] = $userArray['objectid'];
    else
      $where['title'] = $userArray['username'];

    $user =& $foowd->getObj($where, $USER_SOURCE, FALSE);
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

    $foowd->track('smdoc_user->makeTable');
    $sql = 'CREATE TABLE `'.$USER_SOURCE['table'].'` (
              `objectid` int(11) NOT NULL default \'0\',
              `title` varchar(32) NOT NULL default \'\',
              `object` longblob,
              `updated` datetime NOT NULL default \'1969-12-31 19:00:00\',
              `IMAP_server` int(10) unsigned default \'0\',
              `SMTP_server` int(10) unsigned default \'0\',
              `SM_version` int(10) unsigned default \'0\',
              `IRC_nick` varchar(12) default \'\',
              PRIMARY KEY  (`objectid`),
              KEY `idxuser_updated` (`updated`),
              KEY `idxuser_title` (`title`)
            );';
    return $foowd->database->query($sql);
  }

  /**
   * Translate constants for SquirrelMail version to string,
   * or return list of choices.
   * 
   * @param optional boolean getAll Ignore value and return array containing all strings.
   * @return either string for integer, or array containing all strings.
   */
  function smver_to_string($getAll = FALSE)
  {
    global $smver_strings;
    if ( !isset($smver_strings) )
      $smver_strings = array(_("Unknown"), 
                             _("Stable - backlevel"),
                             _("Stable - current"),
                             _("Stable - CVS"),
                             _("Devel  - current"),
                             _("Devel  - CVS"),
                             _("Other"));

    if ( $getAll )
      return ($smver_strings);
    return $smver_strings[$this->SM_version];
  }

  /**
   * Translate constants for IMAP server to string,
   * or return list of choices.
   * 
   * @param optional boolean getAll Ignore value and return array containing all strings.
   * @return either string for integer, or array containing all strings.
   */
  function imap_to_string($getAll = FALSE)
  {
    global $imap_strings;
    if ( !isset($imap_strings) )
      $imap_strings = array(_("Unknown"),
                            'Binc', 
                            'Courier-IMAP',
                            'Cyrus',
                            'Dovecot',
                            'Exchange',
                            _("Other"),
                            'UW-IMAP');

    if ( $getAll )
      return ($imap_strings);
    return $imap_strings[$this->IMAP_server];
  }

  /**
   * Translate constants for SMTP server to string,
   * or return list of choices.
   * 
   * @param optional boolean getAll Ignore value and return array containing all strings.
   * @return either string for integer, or array containing all strings.
   */
  function smtp_to_string($getAll = FALSE)
  {
    global $smtp_strings;
    if ( !isset($smtp_strings) )
      $smtp_strings = array(_("Unknown"),
                            'Courier-MTA',
                            'Cyrus',
                            'Exchange',
                            'Exim',
                            _("Other"),
                            'Postfix',
                            'Sendmail',
                            'Qmail');

    if ( $getAll )
        return ($smtp_strings);
    
    return $smtp_strings[$this->SMTP_server];
  }
    

//-------------------------------------------------------------------------------------

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
   * #squirrelmail IRC channel nick
   * @var string
   */
  var $IRC_nick;       
  
  /** 
   * Array containing other IM nicks 
   * @var array
   */
  var $IM_nicks;         

  /** 
   * Main supported IM version. @see smver_to_string 
   * @var constant
   */
  var $SM_version;       

  /** 
   * Preferred IMAP server. @see imap_to_string 
   * @var constant
   */
  var $IMAP_server;     

  /** 
   * Preferred SMTP server. @see smtp_to_string 
   * @var constant
   */
  var $SMTP_server;

  /** 
   * Show email in profile. 
   * @var boolean
   */
  var $show_email;

  /**
   * Constructs a new user.
   *
   * @constructor smdoc_user
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
                   $objectid = NULL)
  {
    global $USER_SOURCE;
    $foowd->track('smdoc_user->constructor');

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
    $this->show_email = false;
    $this->SM_version = 0;
    $this->IMAP_server = 0;
    $this->SMTP_server = 0;
    $this->IM_nicks = array();
    $this->IRC_nick = '';

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
    $this->foowd_vars_meta['irc'] = '/^[a-zA-Z0-9_]{1,12}$/';
    $this->foowd_vars_meta['msn'] = REGEX_EMAIL;
    $this->foowd_vars_meta['icq'] = '/^[0-9]{3,16}$/';
    $this->foowd_vars_meta['aim'] = '/^[a-zA-Z0-9_]{3,16}$/';
    $this->foowd_vars_meta['yahoo'] = '/^[a-zA-Z0-9_]{1,32}$/';
    $this->foowd_vars_meta['www'] = '/^https?:\/\/[a-zA-Z0-9_\-\.]+\.[a-zA-Z]+[a-zA-Z0-9_\-\.\/~]*$/';

    // re-arrange our indices
    unset($this->foowd_indexes['version']);
    unset($this->foowd_indexes['classid']);
    unset($this->foowd_indexes['workspaceid']);
    $this->foowd_indexes['IMAP_server'] = array('name' => 'imap', 'type' => 'INT', 'unsigned' => TRUE, 'notnull' => FALSE, 'default' => 0);   
    $this->foowd_indexes['SMTP_server'] = array('name' => 'smtp', 'type' => 'INT', 'unsigned' => TRUE, 'notnull' => FALSE, 'default' => 0);   
    $this->foowd_indexes['SM_version'] = array('name' => 'sm_ver', 'type' => 'INT', 'unsigned' => TRUE, 'notnull' => FALSE, 'default' => 0);
    $this->foowd_indexes['IRC_nick'] = array('name' => 'irc', 'type' => 'VARCHAR', 'length' => 12, 'notnull' => FALSE, 'default' => '');

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

    $newuser =& smdoc_user::fetchUser($foowd, $user_info);
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

    $foowd->user = smdoc_user::fetchAnonymousUser($foowd);

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

    $object = new smdoc_user($foowd, $username, $password, $email, $objectid);
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
    $lostuser =& smdoc_user::fetchUser($foowd, $user_info);

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
      $foowd->template->assign('class', 'smdoc_user');
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
      $foowd->template->assign('class', 'smdoc_user');
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
   * Update the users properties.
   *
   * @param str email The users new e-mail address.
   * @return int 0 = nothing, show form<br />
   *             1 = user updated successfully<br />
   *             2 = passwords were set, but didn't match<br />
   *             3 = username already in use<br />
   */
  function updateUser($form) 
  {
    global $USER_SOURCE;
    $obj =& $form->objects;

    // Check title first
    if ( !empty($obj['title']->value) && $obj['title']->value != $this->title )
    {
      $username = $obj['title']->value;
      // no workspaceid, don't calculate new objectid.
      $unique = !$this->foowd->isTitleUnique($username, FALSE, $objectid, $USER_SOURCE, FALSE) ;
      if ( $unique )
        $this->set('title', $username);
      else
      {
        $obj['title']->wasValid = FALSE;
        return 3;
      }
    }

    // Check for password change
    if ( !empty($obj['password']->value) )
    {
      if ( $obj['password']->value == $obj['verify']->value )
      {
        $salt = $foowd->config_settings['user']['password_salt'];
        $this->set('password', md5($salt.$obj['password']->value));
      }
      else
      {
        $obj['password']->wasValid = FALSE;
        $obj['verify']->wasValid = FALSE;
        return 2;
      }
    }

    // Check IM nick names
    foreach ( $obj['nick'] as $box )
    {
      $nk = $box->name;
      if ( (isset($this->IM_nicks[$nk])  && $box->value == $this->IM_nicks[$nk]) ||
           (!isset($this->IM_nicks[$nk]) && $box->value == NULL) )
        continue;

      if ( $box->value == NULL || $box->value == '' )
        unset($this->IM_nicks[$nk]);
      else
        $this->IM_nicks[$nk] = $box->value;

      $this->foowd_changed = TRUE;  
    }

    if ( !empty($obj['email']->value) && $obj['email']->value != $this->email )
      $this->set('email', $obj['email']->value);
    if ( $obj['show_email']->value != $this->show_email )
      $this->set('show_email', $obj['show_email']->value);
    if ( !empty($obj['IRC_nick']->value) && $obj['IRC_nick']->value != $this->IRC_nick )
      $this->set('IRC_nick', $obj['IRC_nick']->value);

    foreach ( $obj['stat'] as $box )
    {
      if ( empty($box->value) )
        $box->value = 0;
      $member = $box->name;
      if ( $box->value != $this->$member )
      {
        $this->$member = $box->value;
        $this->foowd_changed = TRUE;
      }
    }

    if ( $this->show_email && $this->email )
      $this->IM_nicks['Email'] = $this->email;
    else
      unset($this->IM_nicks['Email']);

    if ( $this->save() )
      return 1;
 
    return 4;
  }

// ----------------------------- class methods --------------

  /**
   * Output an object creation form and process its input.
   *
   * @class smdoc_user
   * @method class_create
   * @param object foowd The foowd environment object.
   * @param str className The name of the class.
   */
  function class_create(&$foowd, $className) 
  {
    $foowd->track('smdoc_user->class_create');

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
        $foowd->template->assign('failure', _("Passwords must be at least 6 characters, and must match."));
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
   * @class smdoc_user
   * @method class_login
   * @param object foowd The foowd environment object.
   * @param str className The name of the class.
   */
  function class_login(&$foowd, $className) 
  {
    $foowd->track('smdoc_user->class_login');

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
   * @class smdoc_user
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
        $uri_arr['class'] = 'smdoc_user';
        $uri_arr['method'] = 'login';
        $foowd->loc_forward(getURI($uri_arr, FALSE));
        return NULL;
    }
    trigger_error('Unexpected response when logging out user: ' . $result, E_USER_ERROR);
  }

// -----------------------------object methods --------------

  /**
   * Output the object.
   *
   * @param object foowd The foowd environment object.
   */
  function method_view() 
  {
    $this->foowd->track('smdoc_user->method_view');
    
    $this->foowd->template->assign('username', $this->getTitle());

    $this->foowd->template->assign('created', date(DATETIME_FORMAT, $this->created));
    $this->foowd->template->assign('lastvisit', date(DATETIME_FORMAT, $this->updated));

    if ($this->foowd->user->objectid == $this->objectid) 
    {
      $this->foowd->template->assign('update', TRUE);
      $this->foowd->template->assign('SM_version', $this->smver_to_string());
      $this->foowd->template->assign('IMAP_server', $this->imap_to_string());
      $this->foowd->template->assign('SMTP_server', $this->smtp_to_string());
    }

    if ( $this->email )
      $this->foowd->template->assign('email', mungEmail($this->email));

    if ( $this->IRC_nick != '' )
      $this->foowd->template->assign('IRC_nick', $this->IRC_nick);
    if ( !empty($this->IM_nicks) )
      $this->foowd->template->assign('IM_nicks', $this->IM_nicks);

    $this->foowd->track(); 
  }

  /**
   * Output a user update form and process its input.
   *
   * @param object foowd The foowd environment object.
   */
  function method_update() 
  {
    $this->foowd->track('smdoc_user->method_update');
    
    include_once(INPUT_DIR . 'input.form.php');
    include_once(INPUT_DIR . 'input.dropdown.php');
    include_once(INPUT_DIR . 'input.textbox.php');
    include_once(INPUT_DIR . 'input.checkbox.php');
    
    $updateForm = new input_form('updateForm', NULL, SQ_POST, _("Update"));

    $title = new input_textbox('title', REGEX_TITLE, $this->title, 'Username', FALSE);
    $email = new input_textbox('email', REGEX_EMAIL, $this->email, 'Email', FALSE);
    $showEmail = new input_checkbox('show_email', $this->show_email, 'Share Email');

    $verify = new input_passwordbox('verify', REGEX_PASSWORD, NULL, 'Verify', FALSE);
    $password = new input_passwordbox('password', REGEX_PASSWORD, NULL, 'Password', FALSE);

    $nicks = $this->IM_nicks;
    if ( !array_key_exists('MSN', $nicks) ) $nicks['MSN'] = '';
    if ( !array_key_exists('ICQ', $nicks) ) $nicks['ICQ'] = '';
    if ( !array_key_exists('AIM', $nicks) ) $nicks['AIM'] = '';
    if ( !array_key_exists('Y!', $nicks) )  $nicks['Y!'] = '';
    if ( !array_key_exists('WWW', $nicks) ) $nicks['WWW'] = '';

    $ircNick = new input_textbox('IRC_nick', $this->foowd_vars_meta['irc'],  $this->IRC_nick,'IRC',FALSE);
    $msnNick = new input_textbox('MSN', $this->foowd_vars_meta['msn'],  $nicks['MSN'], 'MSN', FALSE);
    $aimNick = new input_textbox('AIM', $this->foowd_vars_meta['aim'],  $nicks['AIM'], 'AIM', FALSE);
    $icqNick = new input_textbox('ICQ', $this->foowd_vars_meta['icq'],  $nicks['ICQ'], 'ICQ', FALSE);
    $yahooNick = new input_textbox('Y!',$this->foowd_vars_meta['yahoo'],$nicks['Y!'],  'Y!',  FALSE);
    $www     = new input_textbox('WWW', $this->foowd_vars_meta['www'],  $nicks['WWW'], 'WWW', FALSE);

    $smtpServer = new input_dropdown('SMTP_server', $this->SMTP_server, $this->smtp_to_string(true), 'SMTP Server');
    $imapServer = new input_dropdown('IMAP_server', $this->IMAP_server, $this->imap_to_string(true), 'IMAP Server');
    $smVersion  = new input_dropdown('SM_version', $this->SM_version, $this->smver_to_string(true), 'SquirrelMail Version');

    // public fields
    $updateForm->addObject($ircNick);
    $updateForm->addToGroup('nick', $aimNick);
    $updateForm->addToGroup('nick', $icqNick);
    $updateForm->addToGroup('nick', $msnNick);
    $updateForm->addToGroup('nick', $yahooNick);
    $updateForm->addToGroup('nick', $www);

    // private fields
    $updateForm->addObject($title);
    $updateForm->addObject($email);
    $updateForm->addObject($showEmail);
    $updateForm->addObject($password);
    $updateForm->addObject($verify);
    $updateForm->addToGroup('stat',$smtpServer);
    $updateForm->addToGroup('stat',$imapServer);
    $updateForm->addToGroup('stat',$smVersion);

    $this->foowd->template->assign_by_ref('form', $updateForm);
    $result = 0;
 
    if ( $updateForm->submitted() )
      $result = $this->updateUser($updateForm);

    switch($result)
    {
      case 1:
        $_SESSION['ok'] = USER_UPDATE_OK;
        $uri_arr['objectid'] = $this->foowd->user->objectid;
        $uri_arr['classid'] = USER_CLASS_ID;        
        $url = getURI($uri_arr, FALSE);
        $this->foowd->loc_forward( $url);
        break;
      case 2: 
        $this->foowd->template->assign('failure', _("Passwords must match, please check your entries."));
        break;
      case 3:
        $this->foowd->template->assign('failure', _("User already exists, please choose a new name."));
        break;
      case 4:
        $this->foowd->template->assign('failure', _("Could not update user."));
        break;
    }

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
    trigger_error('newVersion not supported for smdoc_user', E_USER_ERROR);    
  }

  /**
   * Clean up the archive versions of the object.
   *
   * @param object foowd The foowd environment object.
   * @return bool Returns TRUE on success.
   */
  function tidyArchive() 
  {
    trigger_error('tidyArchive does not apply to smdoc_user' , E_USER_ERROR);
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
    trigger_error('vars2XML does not apply to smdoc_user' , E_USER_ERROR);
  }
}


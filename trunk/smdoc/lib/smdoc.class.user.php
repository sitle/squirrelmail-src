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

setPermission('smdoc_user', 'object', 'xml', 'Nobody'); 
setPermission('smdoc_user', 'object', 'clone', 'Nobody');

include_once(PATH . 'class.user.php');

/**
 * The smdoc extended user class.
 *
 * Class for holding information about a user and providing methods for
 * manipulating and getting information on a user.
 *
 */
class smdoc_user extends foowd_user
{
  /** #squirrelmail IRC channel nick */
  var $IRC_nick;       
  
  /** Array containing other IM nicks */
  var $IM_nicks;         

  /** Main supported IM version. @see smver_to_string */
  var $SM_version;       

  /** Preferred IMAP server. @see imap_to_string */
  var $IMAP_server;     

  /** Preferred SMTP server. @see smtp_to_string */
  var $SMTP_server;

  /**
   * Fetch User
   *
   * @param object foowd The foowd environment object.
   * @param mixed user Array containing user information (loadUser, username, userid, password).
   * @return retrieved foowd user or anonymous user instance
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
    // If we don't have required elements, return early.
    if ( !isset($userArray['userid']) )
      return FALSE;
    $foowd->track('smdoc_user::fetchUser', $userArray);

    // Set up clause for DB Query
    $whereClause[] = 'objectid = '.$userArray['userid'];

    $oldTable = smdoc_user::setTable($foowd);
    $query = $foowd->database->select($foowd, NULL, array('object'),
                                      $whereClause, NULL, NULL, 1);
    if ( !$query || $query->returnedRows($query) <= 0 ) {
      smdoc_user::setTable($foowd, $oldTable);
      $foowd->debug('msg', 'Could not find user in database');
      $foowd->track();
      return FALSE;
    }  
      
    $record = $query->getRecord($query);
    smdoc_user::setTable($foowd, $oldTable);

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
   * Get user details from an external mechanism.
   *
   * If not already set, populate the user array with the user classid and
   * fetch the username and password of the current user from one of the input
   * mechanisms
   *
   * @class smdoc_user
   * @method getUserDetails
   * @param array user The user array passed into <code>foowd::foowd</code>.
   * @return array The resulting user array.
   */
  function getUserDetails(&$foowd, $user) 
  {
    $session_userinfo = new input_session('userinfo', NULL, NULL, true);
    if ( $session_userinfo->value == NULL )
      return $user;

    $user_info = $session_userinfo->value;
    $update_session = FALSE;

    if ( !isset($user_info['classid']) ) 
    {
      $user_info['classid'] = getConstOrDefault('USER_CLASS_ID', crc32('smdoc_user'));
      $update_session = TRUE;
    } 

    if ( !isset($user['username']) && !isset($user['password']) ) 
    {
      $user['username'] = $user_info['username'];
      $user['password'] = $user_info['password'];
      $user['userid']   = $user_info['userid'];
    }
    $user['classid']   = $user_info['classid'];

    if ( $update_session )
      $session_userinfo->set($user_info);

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
  function makeTable(&$foowd, $SQLString) 
  {
    $foowd->track('smdoc_user->makeTable');

    $createColumns = array(
            array('name' => 'objectid', 'type' => 'INT', 'notnull' => TRUE, 'default' => 0, 'primary' => TRUE, 'index' => TRUE),
            array('name' => 'title', 'type' => 'VARCHAR', 'length' => getRegexLength(REGEX_TITLE, 255), 'notnull' => TRUE, 'default' => '', 'index' => TRUE),
            array('name' => 'object', 'type' => 'BLOB'),
            array('name' => 'updated', 'type' => 'DATETIME', 'notnull' => TRUE, 'default' => date($foowd->database->dateTimeFormat, 0), 'index' => TRUE),
            array('name' => 'imap',  'type' => 'INT',  'default' => '0','notnull' => FALSE, 'unsigned' => TRUE),
            array('name' => 'smtp',  'type' => 'INT',  'default' => '0','notnull' => FALSE, 'unsigned' => TRUE),
            array('name' => 'sm_ver','type' => 'INT',  'default' => '0','notnull' => FALSE, 'unsigned' => TRUE),
            array('name' => 'irc', 'type' => 'VARCHAR','default' => '', 'notnull' => FALSE, 'length' => 12, 'index' => TRUE)
    );

    $result = FALSE;
    if ($foowd->database->createTable($foowd, $createColumns)) 
    {        
      $foowd->debug('sql', $SQLString);
      $result = $foowd->database->query($SQLString);
    }
    
    $foowd->track();
    return $result;
  }

  /**
   * Switch Foowd database table.
   * 
   * If no parameter is given, switches to the user database, otherwise
   * switches to the database specified by oldTable.
   *
   * @param object foowd    The foowd environment object.
   * @param optional mixed  oldTable Array containing table name/function.
   * @return mixed Array containing previous name/function.
   */
  function setTable(&$foowd, $oldTable = NULL) 
  {
    if ( $oldTable )
      $oldTable = $foowd->setTable($oldTable['name'], $oldTable['function']);
    else 
    {
      $userTable = array( 'name' => 'smdoc_user',
                          'function' => array('smdoc_user', 'makeTable'));
      $oldTable = $foowd->setTable($userTable);
    }
    return $oldTable;
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
                   $groups = NULL,
                   $hostmask = NULL)
  {
    $foowd->track('smdoc_user->constructor');
        
    // init meta arrays
    $this->__wakeup(); 

    // Verify input parameters
    if ( !preg_match(REGEX_PASSWORD, $password) ) 
    {
      trigger_error('Could not create object, password contains invalid characters.');
      $this->objectid = 0;
      $foowd->track();
      return FALSE;
    } 

    $maxTitleLength = getRegexLength($this->foowd_vars_meta['title'], 32);
    if ( strlen($username) <= 0 ||  
         strlen($username) > $maxTitleLength || 
         !preg_match($this->foowd_vars_meta['title'], $username) ) 
    {
      trigger_error('Could not create user, invalid username (bad length or characters).');
      $this->objectid = 0;
      $foowd->track();
      return FALSE;
    }

    // check for duplicate title/objectid
    $this->objectid = crc32($username);
    $oldTable = smdoc_user::setTable($foowd);
    $query = $foowd->database->select($foowd, NULL, 
                                      array('objectid'), 
                                      array('objectid = '.$this->objectid), 
                                      NULL, NULL, 1);
    smdoc_user::setTable($foowd, $oldTable);

    if ( $query )
    {
      trigger_error('Could not create object, duplicate name "'.htmlspecialchars($username).'".');
      $this->objectid = 0;
      $foowd->track(); 
      return FALSE;
    }
 
    // Initialize variables
    $this->title = $username;
    $this->workspaceid = 0;
    $this->classid = crc32(strtolower(get_class($this)));

    $this->creatorid = $this->objectid; // created by self
    $this->creatorName = $this->title;  // created by self
    $this->created = time();
    $this->updatorid = $this->objectid; // updated by self
    $this->updatorName = $this->title;  // updated by self
    $this->updated = time();

    $this->hostmask = $hostmask;
    $this->SM_version = 0;
    $this->IMAP_server = 0;
    $this->SMTP_server = 0;
    $this->IM_nicks = array();
    $this->IRC_nick = '';

    $salt = getConstOrDefault('PASSWORD_SALT', '');
    $this->password = md5($salt.strtolower($password));

    if (preg_match($this->foowd_vars_meta['email'], $email)) 
      $this->email = $email;
    
    // user groups
    if (is_array($groups)) 
    {
      foreach ($groups as $group) 
      {
        if ( preg_match($this->foowd_vars_meta['groups'], $group) ) 
          $this->groups[] = $group;
      }
    }
    
    // set original access vars
    $this->foowd_original_access_vars['objectid'] = $this->objectid;
    $this->foowd_original_access_vars['version'] = $this->version;
    $this->foowd_original_access_vars['classid'] = $this->classid;
    $this->foowd_original_access_vars['workspaceid'] = $this->workspaceid;

    $foowd->track();
  }

  /**
   * Serialisation wakeup method.
   */
  function __wakeup() 
  {
    parent::__wakeup();

    // re-arrange our indices
    unset($this->foowd_indexes['version']);
    unset($this->foowd_indexes['classid']);
    unset($this->foowd_indexes['workspaceid']);
    $this->foowd_indexes['IMAP_server'] = array('name' => 'imap', 'type' => 'INT', 'unsigned' => TRUE, 'notnull' => FALSE, 'default' => 0);   
    $this->foowd_indexes['SMTP_server'] = array('name' => 'smtp', 'type' => 'INT', 'unsigned' => TRUE, 'notnull' => FALSE, 'default' => 0);   
    $this->foowd_indexes['SM_version'] = array('name' => 'sm_ver', 'type' => 'INT', 'unsigned' => TRUE, 'notnull' => FALSE, 'default' => 0);
    $this->foowd_indexes['IRC_nick'] = array('name' => 'irc', 'type' => 'VARCHAR', 'length' => 12, 'notnull' => FALSE, 'default' => '');
    $this->foowd_indexes['updated'] = array('name' => 'updated', 'type' => 'DATETIME', 'notnull' => TRUE);

    // add some regex verification
    $this->foowd_vars_meta['irc'] = '/^[a-zA-Z0-9_]{12}$/';
    $this->foowd_vars_meta['msn'] = REGEX_EMAIL;
    $this->foowd_vars_meta['icq'] = '/^[0-9]{16}$/';
    $this->foowd_vars_meta['aim'] = '/^[a-zA-Z0-9_]{16}$/';
    $this->foowd_vars_meta['yahoo'] = '/^[a-zA-Z0-9_]{128}$/';  // completely random guess.
    $this->foowd_vars_meta['www'] = '/^https?://[A-Za-z0-9._-]+\.[A-Za-z]/?[A-Za-z0-9._-/]+$/';
  }

  /**
   * Save the object.
   *
   * @param object foowd The foowd environment object.
   * @param bool incrementVersion Increment the object version.
   * @param bool doUpdate Update the objects details.
   * @return mixed Returns an exit value on success or FALSE on failure.
   */
  function save(&$foowd, $incrementVersion = TRUE, $doUpdate = TRUE)
  {
    $foowd->track('smdoc_user->save');

    if ($doUpdate) { // update values
      $this->updatorid = $foowd->user->objectid;
      $this->updatorName = $foowd->user->title;
      $this->updated = time();
    }

    // serialize object
    $serializedObj = serialize($this);
    $fieldArray['object'] = $serializedObj;
  
    // create field array from object
    foreach ($this->foowd_indexes as $index => $definition) 
    {
      if (isset($this->$index)) 
      {
        $colname = $definition['name'];
        if ($this->$index == FALSE) 
        {
          if ($definition['type'] == 'VARCHAR')
            $fieldArray[$colname] = '';
          else
            $fieldArray[$colname] = 0;
        }
        else 
        {
          if ($definition['type'] == 'DATETIME')  // translate unixtime to db date format
            $fieldArray[$colname] = date($foowd->database->dateTimeFormat, $this->$index);
          else
            $fieldArray[$colname] = $this->$index;
        }
      }
    }

    // set conditions
    $conditionArray = array('objectid = '.$this->foowd_original_access_vars['objectid']);
    $exitValue = FALSE;

    $oldTable = smdoc_user::setTable($foowd);

    // try to update existing record
    if ( $foowd->database->update($foowd, $fieldArray, $conditionArray) ) 
      $exitValue = 1;

    // if update fails, write new record
    elseif ( $foowd->database->insert($foowd, $fieldArray) ) 
      $exitValue = 2;

    // if writing new record fails, modify table to include indexes from class definition
    else
    {
      $query = $foowd->database->select($foowd, NULL, array('*'),    NULL, NULL,    NULL, 1);
      if ( $query )
      {
        $record = $query->getRecord();
        $missingFields = array();

        // find missing fields
        foreach ($fieldArray as $field => $value) 
        {
          if (!isset($record[$field]) && $field != 'object') 
            $missingFields[] = $this->foowd_indexes[$field];
        }

        if ($missingFields != NULL && $foowd->database->alterTable($foowd, $missingFields)) 
        {
          if ($foowd->database->update($foowd, $fieldArray, $conditionArray))
            $exitValue = 3;
          elseif ($foowd->database->insert($foowd, $fieldArray))
            $exitValue = 4;
        }  
      }
    }
    smdoc_user::setTable($foowd, $oldTable);

    $foowd->track();
    return $exitValue;
  }

  /**
   * Delete the object.
   *
   * @param object foowd The foowd environment object.
   * @return bool Returns TRUE on success.
   */
  function delete(&$foowd) 
  {
    $foowd->track('foowd_object->delete');

    $conditionArray = array('objectid = '.$this->objectid);

    $oldTable = smdoc_user::setTable($foowd);
    $result = $foowd->database->delete($foowd, $conditionArray);
    smdoc_user::setTable($foowd, $oldTable);

    $foowd->track(); 
    return ($result ? TRUE : FALSE);
  }

  /**
   * Log the user in.
   *
   * @class smdoc_user
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
  function login(&$foowd, $username = FALSE, $password = NULL) 
  {
    if ( !$foowd->user->isAnonymous() ) 
      return 5;                             // user already logged in
    if ( !$username ) 
      return 1;                             // no user given

    $salt = getConstOrDefault('PASSWORD_SALT', '');

    $user_info['username'] = $username;
    $user_info['password'] = md5($salt.$password);
    $user_info['userid']   = crc32(strtolower($username));

    $newuser =& smdoc_user::fetchUser($foowd, $user_info);
    if ( !is_object($newuser) || strtolower($newuser->title) != strtolower($username)) 
      return 2;                             // unknown user
    if ( !$newuser->hostmaskCheck() )
      return 8;                             // bad hostmask
    if ( $newuser->password != md5($salt.strtolower($password)) )
      return 3;                             // bad password

    $newuser->updated = time();
    $newuser->updatorid = $newuser->objectid;
    $newuser->updatorName = $newuser->title;
    $newuser->save($foowd, FALSE);
    $foowd->user = $newuser;

    $session_userinfo = new input_session('userinfo', NULL, $user_info, true); 
    $session_userinfo->set($user_info);
    return 0;                               // logged in successfully
  }

  /**
   * Log out the user.
   *
   * @class smdoc_user
   * @method logout
   * @param object foowd The foowd environment object.
   * @param optional str authType The type of user authentication to use.
   * @return int 0 = cookie logged out successfully<br />
   *             1 = http logged out successfully<br />
   *             2 = ip auth, can not log out<br />
   *             3 = user already logged out<br />
   *             4 = http log out failed due to browser<br />
   */
  function logout(&$foowd, $authType = 'session') 
  {
    if ( $foowd->user->isAnonymous() ) 
      return 3; // user already logged out

    $foowd->user->updated = time();
    $foowd->user->updatorid = $foowd->user->objectid;
    $foowd->user->updatorName = $foowd->user->title;
    $foowd->user->save($foowd, FALSE);
      
    $foowd->user = smdoc_user::fetchAnonymousUser($foowd);

    $session_userinfo = new input_session('userinfo', NULL, NULL, true); 
    $session_userinfo->set(NULL);
        
    return 0; // logged out successfully
  }

  /**
   * Create a new user.
   *
   * @class smdoc_user
   * @method create
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
  function create(&$foowd, $className, $username, $password, $email) 
  {
    // check for duplicate title/objectid
    $objectid = crc32($username);
    $oldTable = smdoc_user::setTable($foowd);
    $query = $foowd->database->select($foowd, NULL, 
                                      array('objectid'), 
                                      array('objectid = '.$objectid), 
                                      NULL, NULL, 1);
    smdoc_user::setTable($foowd, $oldTable);

    if ( $query ) 
      return 4;        

    $object = new $className($foowd, $username, $password, $email);
    if ( $object->objectid != 0 && $object->save($foowd, FALSE) ) 
      return 0; // created ok
    else
      return 3; // eek, error creating user.
  }

  /**
   * Update the users properties.
   *
   * @param object foowd The foowd environment object.
   * @param str email The users new e-mail address.
   * @return bool TRUE on success.
   */
  function updateUser(&$foowd, $form) 
  {
    $changed = FALSE;

    if ($form['email'] != $this->email) 
    {
      $this->email = $form['email'];
      $changed = TRUE;
    }

    if ($changed) 
    {
      if ($this->save($foowd, FALSE)) 
        return TRUE;
      else 
        trigger_error('Could not update user.');
    }

    return FALSE;
  }

  /**
   * Get user a new password if it has been lost.
   *
   * @class smdoc_user
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
  function fetchPassword(&$foowd, $className, $username, $queryUsername = '', $id = '') 
  {
    if ( $username == '' ) 
      return 0;                             // nothing, display form

    $lostuser = $foowd->fetchObject(crc32(strtolower($username)), USER_CLASS_ID);

    if ( !isset($lostuser->title) || strtolower($lostuser->title) != strtolower($username) )
      return 4;                             // user does not exist
    if ( !isset($lostuser->email) )
      return 3;                             // user has no e-mail address

    // We have username only, send stage one email
    if ( $id == '' && $queryUsername == '' ) 
    {
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

      $salt = getConstOrDefault('PASSWORD_SALT', '');
      $lostuser->password = md5($salt.$newPassword);

      if ( $lostuser->save($foowd, FALSE) ) 
      {
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
          return 5;                         // password changed and e-mail sent
      }
      return 6;                             // could not send e-mail due to technical problem (or could not save new password)
    }
  }


/* Class methods */

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

    include_once($foowd->path.'/input.textbox.php');
    include_once(SM_PATH.'smdoc.input.password.php');
    include_once($foowd->path.'/input.form.php');
    
    $queryTitle = new input_querystring('title', REGEX_TITLE, NULL);
    $createUsername = new input_textbox('createUsername', REGEX_TITLE, $queryTitle->value, _("Username").':');
    $verifyPassword = new input_passwordbox('verifyPassword', REGEX_PASSWORD, NULL, _("Verify").':');
    $createPassword = new input_verify_passwordbox('createPassword', $verifyPassword, REGEX_PASSWORD, NULL, _("Password").':');
    $createEmail = new input_textbox('createEmail', REGEX_EMAIL, NULL, _("E-mail Address").':', NULL, NULL, NULL, FALSE);
    $createForm = new input_form('createForm', NULL, 'POST', _("Create"), _("Reset"));

    if ( $createForm->submitted() &&  $createUsername->value != '' )
    {
      if ( $createPassword->wasSet && $createPassword->value != '' ) 
      {
          $result = call_user_func(array($className, 'create'), $foowd, $className, 
                                   $createUsername->value,
                                   $createPassword->value,
                                   $createEmail->value);
      }
      else 
        $result = -1;

      switch ($result) 
      {
        case 0:
          $url = getURI(array('class' => $className,
                              'method' => 'login',
                              'ok' => USER_CREATE_OK,
                              'username' => htmlspecialchars($createUsername->value)));
          header('Location: ' . $url);
          return NULL;
        case -1: 
          $return['failure'] = _("Passwords must be at least 6 characters, and must match.");
          $verifyPassword->value = '';
          break;
        case 3: 
          $return['failure'] = _("Could not create user.");
          break;
        case 4:
          $return['failure'] = _("User already exists, please choose a new name.");
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
   * @class smdoc_user
   * @method class_login
   * @param object foowd The foowd environment object.
   * @param str className The name of the class.
   */
  function class_login(&$foowd, $className) 
  {
    $foowd->track('smdoc_user->class_login');

    include_once($foowd->path.'/input.textbox.php');
    include_once($foowd->path.'/input.form.php');

    $usernameQuery = new input_querystring('username', REGEX_TITLE, '');
    $loginUsername = new input_textbox('loginUsername', REGEX_TITLE, $usernameQuery->value, _("Username").':');
    $loginPassword = new input_passwordbox('loginPassword', REGEX_PASSWORD, NULL, _("Password").':');

    $loginForm = new input_form('loginForm', NULL, 'POST', _("Log In"), NULL);
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
      $result = 1;

    switch ($result) 
    {
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
      case 1:
      case 3:
        $url =  getURI(array('class'  => 'smdoc_user',
                             'method' => 'login',
                             'ok'     => USER_LOGOUT_OK));
        header('Location: ' . $url);
        return NULL;
    }
    trigger_error('Unexpected response when logging out user: ' . $result, E_USER_ERROR);
  }

/* Object methods */

  /**
   * Output the object.
   *
   * @param object foowd The foowd environment object.
   */
  function method_view(&$foowd) 
  {
    $foowd->track('smdoc_user->method_view');
    
    $return['username'] = $this->getTitle();

    $return['created'] = date(DATETIME_FORMAT, $this->created).' ('.timeSince($this->created).' ago)';
    $return['lastvisit'] =  date(DATETIME_FORMAT, $this->updated).' ('.timeSince($this->updated).' ago)';
    if ($foowd->user->objectid == $this->objectid) {
      $return['email'] = ($this->email) ? mungEmail($this->email) : '';
      $return['update'] = getURI(array('objectid' => $this->objectid, 'classid' => $this->classid, 'method' => 'update'));
      $return['SM_version'] = $this->smver_to_string();
      $return['IMAP_server'] = $this->imap_to_string();
      $return['SMTP_server'] = $this->smtp_to_string();
    }

    if ( $this->IRC_nick != '' )
      $return['irc_nick'] = $this->IRC_nick;
    if ( !empty($this->IM_nicks) )
      $return['IM_nicks'] = $this->IM_nicks;

    $foowd->track(); 
    return $return;
  }

  /**
   * Output a user update form and process its input.
   *
   * @param object foowd The foowd environment object.
   */
  function method_update(&$foowd) {
    $foowd->track('smdoc_user->method_update');
    
    include_once($foowd->path.'/input.form.php');
    include_once($foowd->path.'/input.dropdown.php');
    include_once($foowd->path.'/input.textbox.php');
    include_once(SM_PATH.'smdoc.input.password.php');
    
    $updateForm = new input_form('updateForm', NULL, 'POST', _("Update"));

    $email = new input_textbox('email', REGEX_EMAIL, $this->email, _("E-mail").': ', NULL, NULL, NULL, FALSE);

    $verify = new input_passwordbox('verify', REGEX_PASSWORD, '', _("Verify").': ', NULL, NULL, NULL, FALSE);
    $password = new input_verify_passwordbox('password', $verify, REGEX_PASSWORD, '', _("Change Password").': ', NULL, NULL, NULL, FALSE);

    $nicks = $this->IM_nicks;
    if ( !array_key_exists('MSN', $nicks) ) $nicks['MSN'] = '';
    if ( !array_key_exists('ICQ', $nicks) ) $nicks['ICQ'] = '';
    if ( !array_key_exists('AIM', $nicks) ) $nicks['AIM'] = '';
    if ( !array_key_exists('Y!', $nicks) )  $nicks['Y!'] = '';
    if ( !array_key_exists('WWW', $nicks) ) $nicks['WWW'] = '';

    $ircNick = new input_textbox('irc', $this->foowd_vars_meta['irc'], $this->IRC_nick, 'IRC: ', NULL, NULL, NULL, FALSE);
    $msnNick = new input_textbox('msn', $this->foowd_vars_meta['msn'], $nicks['MSN'], 'MSN: ', NULL, NULL, NULL, FALSE);
    $aimNick = new input_textbox('aim', $this->foowd_vars_meta['aim'], $nicks['AIM'], 'AIM: ', NULL, NULL, NULL, FALSE);
    $icqNick = new input_textbox('icq', $this->foowd_vars_meta['icq'], $nicks['ICQ'], 'ICQ: ', NULL, NULL, NULL, FALSE);
    $yahooNick = new input_textbox('ym', $this->foowd_vars_meta['yahoo'], $nicks['Y!'], 'Y!: ', NULL, NULL, NULL, FALSE);
    $www     = new input_textbox('www', $this->foowd_vars_meta['www'], $nicks['WWW'], 'WWW:', NULL, NULL, NULL, FALSE);

    $smtpServer = new input_dropdown('smtp', 0, $this->smtp_to_string(true), _("SMTP Server:"));
    $imapServer = new input_dropdown('imap', 0, $this->imap_to_string(true), _("IMAP Server:"));
    $smVersion  = new input_dropdown('smver', 0, $this->smver_to_string(true), _("SquirrelMail Version:"));

    // public fields
    $updateForm->addObject($ircNick);
    $updateForm->addObject($aimNick);
    $updateForm->addObject($icqNick);
    $updateForm->addObject($msnNick);
    $updateForm->addObject($yahooNick);
    $updateForm->addObject($www);

    // private fields
    $updateForm->addObject($email);
    $updateForm->addObject($password);
    $updateForm->addObject($verify);
    $updateForm->addObject($smtpServer);
    $updateForm->addObject($imapServer);
    $updateForm->addObject($smVersion);
 
    $return['form'] = &$updateForm;
    $result = 0;
  
    if ( $updateForm->submitted() )
    {
      if ( $verify->wasSet && !$password->wasSet )
          $result = 2;                      // both password and verify were not set or did not match
      else
      {
        if ( $password->wasSet )   $form['password'] = $password->value;
        if ( $email->wasSet )      $form['email'] = $email->value;
        if ( $ircNick->wasSet )    $form['irc'] = $ircNick->value;
        if ( $msnNick->wasSet )    $form['nick']['MSN'] = $msnNick->value;
        if ( $aimNick->wasSet )    $form['nick']['AIM'] = $aimNick->value; 
        if ( $icqNick->wasSet )    $form['nick']['ICQ'] = $icqNick->value;
        if ( $yahooNick->wasSet )  $form['nick']['Y!']  = $yahooNick->value;
        if ( $smtpServer->wasSet ) $form['smtp'] = $smtpServer->value;
        if ( $imapServer->wasSet ) $form['imap'] = $imapServer->value;
        if ( $smVersion->wasSet )  $form['smver'] = $smVersion->value;

        if ($this->updateUser($foowd, $form)) {
            $result = 1;
        } else {
            $result = 3;
        }
      }
    }

    switch($result)
    {
      case 1:
        $url = getURI(array('objectid' => $foowd->user->objectid, 
                            'classid' => USER_CLASS_ID,
                            'ok' => USER_UPDATE_OK));
        header('Location: ' . $url);
        break;
      case 2: 
        $return['failure'] = _("Passwords must match, please check your entries.");
        break;
      case 3:
        $return['failure'] = _("Could not update user.");
        break;
    }

    $foowd->track(); 
    return $return;
  }



// ----------------------------- disabled methods --------------

  /**
   * Output the object clone form and handle its input.
   *
   * @param object foowd The foowd environment object.
   */
  function method_clone(&$foowd) 
  {
    trigger_error('smdoc_user can not be cloned' , E_USER_ERROR);
  }

  /**
   * Output the object as XML.
   *
   * @param object foowd The foowd environment object.
   */
  function method_xml(&$foowd) 
  {
    trigger_error('method_xml does not apply to smdoc_user' , E_USER_ERROR);
  }

  /**
   * Clean up the archive versions of the object.
   *
   * @param object foowd The foowd environment object.
   * @return bool Returns TRUE on success.
   */
  function tidyArchive(&$foowd) 
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
  function clone(&$foowd, $title, $workspace) 
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

  /**
   * Check if the user is the anonymous user.
   *
   * @class smdoc_user
   * @method isAnonymous
   * @return bool Returns TRUE if the user is of the anonymous user class.
   */
  function isAnonymous() 
  {
    return FALSE;
  }
}


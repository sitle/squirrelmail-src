<?php
/* 
 * Modified by SquirrelMail Development
 * $Id$
 */

/**
 * Anonymous user class.
 *
 * Used to instanciate bogus user for anoymous access where only basic user
 * data is required and it would be a waste to pull a user from the database.
 * This is here rather than in class.user.php since it is possible to use
 * foowd without class.user.php and just with the anonymous user if you don't
 * need users.
 *
 * @author Paul James
 */
class foowd_anonuser extends foowd_object {

  /**
   * Constructs a new anonymous user.
   *
   * @param object foowd The foowd environment object.
   */
  function foowd_anonuser(&$foowd) {
    $foowd->track('foowd_anonuser->constructor');
    $this->title = $foowd->config_settings['user']['anon_user_name'];
    $this->objectid = NULL;
    $this->version = 1;
    $this->classid = -1063205124;
    $this->workspaceid = 0;
    $this->created = time();
    $this->creatorid = 0;
    $this->creatorName = 'System';
    $this->updated = time();
    $this->updatorid = 0;
    $this->updatorName = 'System';
    $this->email = NULL;
    $this->permissions = NULL;
    $this->foowd = &$foowd;
    $foowd->track();
  }

  /**
   * Whether the anonymous user is in a user group.
   *
   * @param str groupName Name of the group to check.
   * @return bool TRUE or FALSE.
   */
  function inGroup($groupName, $creatorid = NULL) { // *SQM
    if ($groupName == 'Everyone')
      return TRUE;
    if ( $groupName == 'Nobody')
      return FALSE;
    if ($this->foowd->config_settings['user']['anon_user_god'])
      return TRUE;

    return FALSE;
  }
  
  /**
   * Check the string is the users password.
   *
   * @param str password The password to check.
   * @param bool plainText The password is in plain text rather than an md5 hash.
   * @return bool Always returns TRUE.
   */
  function passwordCheck($password, $plainText = FALSE) { // there is no password for the anonymous user, so it must always match
    return TRUE;
  }
  
  /**
   * Check if the user is the anonymous user.
   *
   * @return bool Always returns TRUE.
   */
  function isAnonymous() { // yes this is the anonymous user, so we must be anonymous
    return TRUE;
  }
  
  /**
   * Override {@link foowd_object#save} to stop this object from being saved.
   *
   * @return bool Always returns FALSE.
   */
  function save() { // override save function since it should never be written to the database and is just instanciated as needed.
    return FALSE;
  }
}

?>

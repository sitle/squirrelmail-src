<?php
/*
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition/modification to the
 * Framework for Object Orientated Web Development (Foowd).
 */

/**
 * Specific user extensions for the SquirrelMail documentation site.
 *
 * $Id$
 *
 * @package smdoc
 * @subpackage user
 */

/** Class Descriptor/Meta information */
setClassMeta('smdoc_user', 'User');
setConst('USER_CLASS_ID', META_SMDOC_USER_CLASS_ID);
setConst('USER_CLASS_NAME', 'smdoc_user');

/** Include the user base class */
include_once(SM_DIR . 'class.user.php');

/**
 * The smdoc extended user class.
 *
 * Class for holding information about a user and providing methods for
 * manipulating and getting information on a user.
 *
 * @package smdoc
 * @subpackage user
 */
class smdoc_user extends base_user
{
  /**
   * Make a Foowd database table.
   *
   * When a database query fails due to a non-existant database table, this
   * method is envoked to create the missing table and execute the SQL
   * statement again.
   *
   * @param smdoc foowd Reference to the foowd environment object.
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
              `IRC` varchar(12) default \'\',
              PRIMARY KEY  (`objectid`),
              KEY `idxuser_updated` (`updated`),
              KEY `idxuser_title` (`title`)
            );';
    $result = $foowd->database->query($sql);
    $foowd->track();
    return $result;
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
   * #squirrelmail IRC channel nick
   * @var string
   */
  var $IRC;

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
   * @param smdoc foowd Reference to the foowd environment object.
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

    // Call parent constructor for base initialization
    parent::base_user($foowd, $username, $password, $email, $objectid);

    $this->show_email = false;
    $this->SM_version = 0;
    $this->IMAP_server = 0;
    $this->SMTP_server = 0;
    $this->IM_nicks = array();
    $this->IRC = '';

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
    $this->foowd_vars_meta['IRC'] = '/^[a-zA-Z0-9_]{1,12}$/';
    $this->foowd_vars_meta['MSN'] = REGEX_EMAIL;
    $this->foowd_vars_meta['ICQ'] = '/^[0-9]{3,16}$/';
    $this->foowd_vars_meta['AIM'] = '/^[a-zA-Z0-9_]{3,16}$/';
    $this->foowd_vars_meta['Y!'] = '/^[a-zA-Z0-9_]{1,32}$/';
    $this->foowd_vars_meta['WWW'] = '/^https?:\/\/[a-zA-Z0-9_\-\.]+\.[a-zA-Z]+[a-zA-Z0-9_\-\.\/~]*$/';

    // add indices
    $this->foowd_indexes['IMAP_server'] = array('name' => 'imap', 'type' => 'INT', 'unsigned' => TRUE, 'notnull' => FALSE, 'default' => 0);
    $this->foowd_indexes['SMTP_server'] = array('name' => 'smtp', 'type' => 'INT', 'unsigned' => TRUE, 'notnull' => FALSE, 'default' => 0);
    $this->foowd_indexes['SM_version'] = array('name' => 'sm_ver', 'type' => 'INT', 'unsigned' => TRUE, 'notnull' => FALSE, 'default' => 0);
    $this->foowd_indexes['IRC'] = array('name' => 'irc', 'type' => 'VARCHAR', 'length' => 12, 'notnull' => FALSE, 'default' => '');
  }

  /**
   * Create form elements for the update form from the objects member variables.
   *
   * @param  object form The form to add the form items to.
   * @param  array  error If error is encountered, add message to this array
   */
  function addUserItemsToForm(&$form, &$error)
  {
    $this->addContactItemsToForm($form);
    $this->addStatItemsToForm($form);
    parent::addUserItemsToForm($form, $error);
  }

  /**
   * Create form elements for the update form from the objects member variables.
   *
   * @param  object form  The form to add the form items to.
   * @param  array  error If error is encountered, add message to this array
   * @return mixed array of error codes or 0 for success
   */
  function addContactItemsToForm(&$form)
  {
    include_once(INPUT_DIR.'input.textbox.php');

    $nicks = $this->IM_nicks;   // get all nicks.

    unset($nicks['Email']);     // remove Email
    $nicks['IRC'] = $this->IRC; // add IRC

    if ( !isset($nicks['MSN']) ) $nicks['MSN'] = NULL;
    if ( !isset($nicks['AIM']) ) $nicks['AIM'] = NULL;
    if ( !isset($nicks['ICQ']) ) $nicks['ICQ'] = NULL;
    if ( !isset($nicks['Y!'] ) ) $nicks['Y!']  = NULL;
    if ( !isset($nicks['WWW']) ) $nicks['WWW'] = NULL;

    foreach ( $nicks as $prot => $nick )
    {
      $nickBox = new input_textbox($prot, $this->foowd_vars_meta[$prot], $nick, $prot, FALSE);
      $form->addToGroup('nick',$nickBox);

      // If form wasn't submitted, or if the submitted value is the same, continue to next.
      if ( !$form->submitted() ||
           !$nickBox->wasValid ||
           $nickBox->value == $nicks[$prot] )
        continue;

      // Otherwise, the value of the nick changed..
      if ( $prot == 'IRC' )
      {
        $this->IRC = $nickBox->value;
      }
      elseif ( empty($nickBox->value) )
        unset($this->IM_nicks[$prot]);
      else
        $this->IM_nicks[$prot] = $nickBox->value;

      $this->foowd_changed = TRUE;
    }
  }

  /**
   * Create form elements for the update form from the objects member variables.
   *
   * @param  object form  The form to add the form items to.
   * @param  array  error If error is encountered, add message to this array
   * @return mixed array of error codes or 0 for success
   */
  function addStatItemsToForm(&$form)
  {
    include_once(INPUT_DIR . 'input.dropdown.php');

    $smtpServer = new input_dropdown('SMTP_server', $this->SMTP_server, $this->smtp_to_string(true), 'SMTP Server');
    $imapServer = new input_dropdown('IMAP_server', $this->IMAP_server, $this->imap_to_string(true), 'IMAP Server');
    $smVersion  = new input_dropdown('SM_version', $this->SM_version, $this->smver_to_string(true), 'SquirrelMail Version');

    if ( $form->submitted() )
    {
      if ( $smtpServer->value != $this->SMTP_server )
        $this->set('SMTP_server', intval($smtpServer->value));
      if ( $imapServer->value != $this->IMAP_server )
        $this->set('IMAP_server', intval($imapServer->value));
      if ( $smVersion->value != $this->SM_version )
        $this->set('SM_version', intval($smVersion->value));
    }

    $form->addToGroup('stat',$smtpServer);
    $form->addToGroup('stat',$imapServer);
    $form->addToGroup('stat',$smVersion);
  }

// -----------------------------object methods --------------

  /**
   * Output the object.
   *
   * @param smdoc foowd Reference to the foowd environment object.
   */
  function method_view()
  {
    $this->foowd->track('smdoc_user->method_view');

    if ( $this->foowd->user->inGroup('Author', $this->creatorid) )
    {
      $this->foowd->template->assign('SM_version', $this->smver_to_string());
      $this->foowd->template->assign('IMAP_server', $this->imap_to_string());
      $this->foowd->template->assign('SMTP_server', $this->smtp_to_string());
    }

    $this->foowd->template->assign('show_email', $this->show_email);

    $nicks = $this->IM_nicks;
    if ( $this->IRC != '' )
      $nicks['IRC'] = $this->IRC;
    if ( !empty($nicks) )
      $this->foowd->template->assign('nicks', $nicks);

    parent::method_view();

    $this->foowd->track();
  }

}


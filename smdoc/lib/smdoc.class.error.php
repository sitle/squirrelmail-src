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

/* Class descriptor */
setClassMeta('smdoc_error', 'Error Display');
setConst('ERROR_CLASS_ID', META_SMDOC_ERROR_CLASS_ID);
setConst('ERROR_TITLE', _("Page Error"));


/** METHOD PERMISSIONS **/
setPermission('smdoc_error', 'class',  'create', 'Nobody');
setPermission('smdoc_error', 'object', 'admin', 'Nobody');
setPermission('smdoc_error', 'object', 'revert', 'Nobody');
setPermission('smdoc_error', 'object', 'delete', 'Nobody');
setPermission('smdoc_error', 'object', 'clone', 'Nobody');
setPermission('smdoc_error', 'object', 'permissions', 'Nobody');
setPermission('smdoc_error', 'object', 'history', 'Nobody');
setPermission('smdoc_error', 'object', 'diff', 'Nobody');

/**
 * Error class.
 *
 * Used for rendering error messages
 *
 * @package foowd
 * @class smdoc_error
 * @extends foowd_object
 */
class smdoc_error extends foowd_object {

    var $errorString;

    /**
	 * Constructs a new error object.
	 *
	 * @constructor smdoc_error
	 * @param object foowd The foowd environment object.
	 */
    function smdoc_error(&$foowd, 
                         $title = ERROR_TITLE, 
                         $errorString = '') {
        $foowd->track('smdoc_error->constructor');
        $this->title = $title;
        $this->errorString = $errorString;

        $this->objectid = NULL;
        $this->version = 1;
        $this->classid = META_SMDOC_ERROR_CLASS_ID;   
        $this->workspaceid = 0;
		$this->created = time();
		$this->creatorid = 0;
		$this->creatorName = 'System';
		$this->updated = time();
		$this->updatorid = 0;
		$this->updatorName = 'System';
        $this->permissions = NULL;

        $foowd->track();
    }

	/**
	 * Override {@link foowd_object::save} to stop this object from being saved.
	 *
	 * @class smdoc_error
	 * @method public save
	 * @param object foowd The foowd environment object.
	 * @param optional bool incrementVersion Increment the object version.
	 * @param optional bool doUpdate Update the objects details.
	 * @return bool Always returns FALSE.
	 */
	function save(&$foowd, $incrementVersion = TRUE, $doUpdate = TRUE) { 
		return FALSE;
	}

    function set(&$foowd, $member, $value = NULL) {
        return FALSE;
    }

    function delete(&$foowd) {
        return FALSE;
    }

	/**
	 * Output the object.
	 *
	 * @class foowd_object
	 * @method private method_view
	 * @param object foowd The foowd environment object.
	 */
	function method_view(&$foowd) {
		$foowd->track('smdoc_error->method_view');
        $foowd->template->assign('title', $this->title);
        $foowd->template->assign('failure', 'Page rendering error:');
        $foowd->template->assign('body', '<p align="center">'. $this->errorString. '</p>');

		$foowd->track();
    }
}

set_error_handler('smdocErrorCatch');

/**
 * Modified version of foowd error handling function.
 *
 * Upon an error being triggered, this function outputs a standard error
 * message and halts execution elegantly.
 *
 * @package foowd
 * @function foowdErrorCatch
 * @param int errorNumber The error code
 * @param str errorString Error description
 * @param str filename The filename in which the error occurred
 * @param int lineNumber The line number in which the error occurred
 * @param array context The context in which the error occurred
 */
function smdocErrorCatch($errorNumber, $errorString, $filename, $lineNumber, $context)
{
  switch ($errorNumber)
  {
    case E_USER_ERROR:
      $errorName = 'Error';
      break;
    case E_WARNING:
    case E_USER_WARNING:
      $errorName = 'Warning';
      break;
    case E_NOTICE:
    case E_USER_NOTICE:
      $errorName = 'Notice';
      break;
    default:
      $errorName = '#'.$errorNumber;
      break;
  }

  $errorString = '<strong>'. $errorName. ':</strong> ' . $errorString;
  $errorString .= ' in <strong>'. $filename. '</strong> on line <strong>';
  $errorString .= $lineNumber. '</strong>';

  if (isset($context['foowd']))
    $foowd = $context['foowd'];
  elseif (isset($context['this']))
    $foowd = $context['this'];

  if (headers_sent())
  {
    echo '<p>', $errorString, '</p>';
    if ( isset($foowd) && is_object($foowd) && get_class($foowd) == FOOWD_CLASS_NAME )
    {
      if ( $foowd->debug )
        $foowd->debug->display();
      $foowd->destroy();
    }
  }
  else
  {
    if ( isset($foowd) && is_object($foowd) && get_class($foowd) == FOOWD_CLASS_NAME )
    {
      $object = new smdoc_error($foowd, ERROR_TITLE, $errorString);
      $t = $object->method_view($foowd);
      $foowd->template->display($foowd->getTemplateName('smdoc_error', 'object_view'));
      $foowd->destroy();
    }
    else
    {
      echo '<h1>', ERROR_TITLE,'</h1>';
      echo '<p>', $errorString, '</p>';
    }
  }

  if ( $errorNumber == E_USER_ERROR ) { // fatal error, halt
    exit(); // self contained error, halt
  }
}

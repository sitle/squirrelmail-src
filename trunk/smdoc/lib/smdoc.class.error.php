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

/**
 * Error class.
 *
 * Used for rendering error messages
 *
 * @package foowd
 * @class smdoc_error
 * @extends foowd_object
 * @author Erin Schnabel
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
                         $title = DEFAULT_ERROR_TITLE, 
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
        
        $foowd->tpl->assign('PAGE_TITLE', $this->title);
        $foowd->tpl->assign_by_ref('CURRENT_OBJECT', $this);
        $foowd->tpl->assign_by_ref('STATUS_ERROR', $this->errorString);

        $error = new smdoc_display('error.tpl');
        $foowd->tpl->assign('BODY', $error);
		$foowd->track();
    }
}

set_error_handler('smdocErrorCatch');

setConst('DEFAULT_ERROR_TITLE', _("Page Error"));

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
  if (DEBUG)
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
  }

  if (headers_sent())
    echo '<p>', $errorString, '</p>';
  else
  {
    if (isset($context['foowd']))
      $foowd = $context['foowd'];
    elseif (isset($context['this']))
      $foowd = $context['this'];

    if (isset($foowd) && is_object($foowd) && get_class($foowd) == getConstOrDefault('FOOWD_CLASS_NAME','foowd') )
    {
      $errorObject = new smdoc_error($foowd, DEFAULT_ERROR_TITLE, $errorString);
      $errorObject->method_view($foowd);
      $foowd->destroy();
    }
    else
    {
      echo '<h1>',DEFAULT_ERROR_TITLE,'</h1>';
      echo '<p>', $errorString, '</p>';
    }
  }

  if ( $errorNumber == E_USER_ERROR ) { // fatal error, halt
    exit(); // self contained error, halt
  }
}

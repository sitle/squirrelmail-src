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

include_once(SM_DIR . 'smdoc.class.storage.php');

/* Class descriptor */
setClassMeta('smdoc_error', 'Error Display');
setConst('ERROR_CLASS_ID', META_SMDOC_ERROR_CLASS_ID);
setConst('ERROR_TITLE', _("Page Error"));


/** METHOD PERMISSIONS **/
setPermission('smdoc_error', 'object', 'view', 'Everyone');

/**
 * Error class.
 *
 * Used for rendering error messages
 *
 * @package foowd
 * @class smdoc_error
 * @extends foowd_object
 */
class smdoc_error extends smdoc_storage
{
    /**
     * String containing error message.
     * @var string
     */
    var $errorString;

    /**
	 * Constructs a new error object.
	 *
	 * @constructor smdoc_error
	 * @param object foowd The foowd environment object.
     * @param string title The error title
     * @param string errorString The error message.
	 */
    function smdoc_error(&$foowd, 
                         $title = ERROR_TITLE, 
                         $errorString = '') {
        $foowd->track('smdoc_error->constructor');

        parent::smdoc_storage($foowd, $title, NULL, FALSE);

        $this->errorString = $errorString;

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

  // If headers have not already been sent,
  // And we have something called foowd that is a FOOWD object,
  // and the foowd object has it's template member defined:
  if ( !headers_sent() && 
       isset($foowd) && is_object($foowd) && 
       get_class($foowd) == FOOWD_CLASS_NAME  &&  $foowd->template ) 
  { 
    $object = new smdoc_error($foowd, ERROR_TITLE, $errorString);
    $t = $object->method_view($foowd);
    $foowd->template->display($foowd->getTemplateName('smdoc_error', 'object_view'));
    $foowd->destroy();
  }
  else
    smdocErrorPrint($foowd, $errorString);

  if ( $errorNumber == E_USER_ERROR ) { // fatal error, halt
    exit(); // self contained error, halt
  }
}

function smdocErrorPrint(&$foowd, &$errorString)
{
  echo '<h1>', ERROR_TITLE,'</h1>';
  echo '<p>', $errorString, '</p>';
  if ( isset($foowd) && is_object($foowd) && get_class($foowd) == FOOWD_CLASS_NAME )
    $foowd->destroy();
}

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

/*
 * Callback function for class load/initialization error
 * -------------------------------------------------------------
 */
ini_set('unserialize_callback_func', 'loadClassCallback');

function loadClassCallback($className)
{
  global $FOOWD_LOADCLASSCALLBACK;

  if ( defined('DEFINITION_CLASS_ID') &&
       is_object($FOOWD_LOADCLASSCALLBACK) &&
       method_exists($FOOWD_LOADCLASSCALLBACK, 'loadClass'))
  {
    if ($FOOWD_LOADCLASSCALLBACK->loadClass($className))
      return TRUE;
  }

  loadDefaultClass($className);
}

/*
 * loadDefaultClass
 * -------------------------------------------------------------
 * load an incomplete class, it is just a foowd_object
 * clone to enable loading of objects whose class
 * definitions can not be found.
 * -------------------------------------------------------------
 */
function loadDefaultClass($className)
{
  setClassMeta($className, _("Incomplete Class"));
  eval('class '.$className.' extends foowd_object {}');
}


/*
 * Error Handling
 * -------------------------------------------------------------
 */
set_error_handler('foowdErrorCatch');
setConst('DEFAULT_ERROR_TITLE', _("Page Error"));

function foowdErrorCatch($errorNumber, $errorString, $filename, $lineNumber, $context)
{
  if (isset($context['foowd']))
    $foowd = $context['foowd'];
  elseif (isset($context['this']))
    $foowd = $context['this'];

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
    if (isset($foowd) && is_object($foowd) && get_class($foowd) == 'foowd')
    {
      $errorObject = new foowd_object($foowd, DEFAULT_ERROR_TITLE);
      $foowd->tpl->assign('PAGE_TITLE', DEFAULT_ERROR_TITLE);
      $foowd->tpl->assign_by_ref('CURRENT_OBJECT', $errorObject);
      $foowd->tpl->assign_by_ref('STATUS_ERROR', $errorString);
      
      $error = new smdoc_display('error.tpl');
      $foowd->tpl->assign('BODY', $error);
    }
    else
    {
      echo '<h1>',DEFAULT_ERROR_TITLE,'</h1>';
      echo '<p>', $errorString, '</p>';
    }
  }

  if (isset($foowd) && is_object($foowd) && get_class($foowd) == 'foowd')
    $foowd->destroy();

  exit(); // self contained error, halt
}

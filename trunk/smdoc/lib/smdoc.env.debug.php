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

include_once(FOOWD_DIR . 'env.debug.php');

/**
 * The Foowd debugging class.
 *
 * Handles tracking of program execution.
 *
 * @package smdoc
 */
class smdoc_debug extends foowd_debug 
{
  /**
   * Reference to foowd environment
   * @var object
   */
  var $foowd;

  /**
   * smdoc_debug constructor.
   * Defers to foowd_debug constructor.
   */
  function smdoc_debug(&$foowd) 
  {
    parent::foowd_debug();
    $this->foowd = &$foowd;
  }

  /**
   * Display the debugging information.
   */
  function display()
  {
    echo '<div class="debug_output">' . "\n"
       . '<a name="debug"><img src="templates/images/empty.png" alt="------------- debug ------------------------------------------" /></a>' . "\n"
       . '<div class="debug_output_heading">Debug Information</div>'. "\n"
       . '<pre>'
       . 'Total DB Executions: '  . $this->DBAccessNumber . '&nbsp;' . "\n"
       . 'Total Execution Time: ' . $this->executionTime(). ' seconds'. "\n"
       . '</pre>'
       . '<div class="debug_output_heading">Execution History</div>'. "\n"
       . '<pre>' . $this->trackString . '</pre>';

    if ( $this->foowd->config_settings['debug']['debug_var'] ) 
    {
      echo '<div class="debug_output_heading">Request</div>'. "\n";
      show($_REQUEST);
      echo '<div class="debug_output_heading">Session</div>'. "\n";
      show($_SESSION);
      echo '<div class="debug_output_heading">Cookie</div>'. "\n";
      show($_COOKIE);
    }

    if ( $this->foowd->config_settings['debug']['debug_ext'] ) 
    {
      echo '<div class="debug_output_heading">External Resources</div>'. "\n";
      global $EXTERNAL_RESOURCES;
      show($EXTERNAL_RESOURCES);       
      echo '<div class="debug_output_heading">Internal Resources</div>'. "\n";
      global $INTERNAL_LOOKUP;
      show($INTERNAL_LOOKUP);
    }    

    echo '</div>';
  }

  /**
   * Function execution tracking.
   *
   * @param str function The name of the function execution is entering.
   * @param array args List of arguments passed to the function.
   */
  function track($function, &$args) 
  {
    if ($function) 
    {
      $this->trackDepth++;
      $this->trackString .= $this->executionTime() . ' '
                         . str_repeat('|', $this->trackDepth - 1)
                         . '+-' . str_repeat ('-', $this->trackDepth - 1)
                         . ' ' .$function.'(';
      if ($args) 
      {
        $parameters = '';
        
        foreach ($args as $key => $arg) 
          $parameters .= $this->makeVarViewable($arg).', ';
        
        $this->trackString .= substr($parameters, 0, -2);
      }
      $this->trackString .= ')<br />';
    } 
    else 
    {
      $this->trackString .= $this->executionTime() . ' '
                         . str_repeat('|', $this->trackDepth - 1)
                         . '+-' . str_repeat ('-', $this->trackDepth - 1)
                         . '<br />';
      $this->trackDepth--;
    }
  }

  /**
   * Add message to debugging output.
   *
   * @param str string The message to add.
   */
  function msg($string) 
  {
    $this->trackString .= $this->executionTime() . ' '
                       . str_repeat('|', $this->trackDepth).' '
                       . htmlspecialchars($string).'<br />';
  }

  /**
   * Calculate the current execution time.
   *
   * @access private
   * @return int The time in microseconds.
   */
  function executionTime() 
  {
    return sprintf("%.3f", round($this->getTime() - $this->startTime, 3));
  }
}


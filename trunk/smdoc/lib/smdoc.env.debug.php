<?php
/*
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition/modification to the
 * Framework for Object Orientated Web Development (Foowd).
 */

/**
 * Debug implementation.
 * Lightly modified from original Foowd implementation.
 *
 * $Id$
 * @package smdoc
 */

/**
 * The Foowd debugging class.
 *
 * Handles tracking of program execution.
 *
 * @package smdoc
 */
class smdoc_debug
{
  /**
   * Function execution tracking data string.
   *
   * @var string
   */
  var $trackString = '';

  /**
   * Depth of the function execution tracking.
   *
   * @var int
   */
  var $trackDepth = 0;

  /**
   * Number of database accesses.
   *
   * @var int
   */
  var $DBAccessNumber = 0;

  /**
   * Time execution started.
   *
   * @var int
   */
  var $startTime;

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
    $this->startTime = $this->getTime();
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

    echo '<br /></div></body></html>';
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
   * Add SQL string to debugging output and increment database access count.
   *
   * @param str SQLString The SQL string to add.
   */
  function sql($SQLString) 
  { 
    $this->msg($SQLString);
    $this->DBAccessNumber++;
  }

  /**
   * Convert constants, objects and arrays into strings ready for displaying.
   *
   * @access private
   * @param mixed arg The variable to output.
   * @return str Converted variable.
   */
  function makeVarViewable($arg) 
  {
    if ($arg == NULL) 
      return 'NULL';
    elseif ($arg === TRUE)
      return 'TRUE';
    elseif ($arg === FALSE)
      return 'FALSE';
    elseif (is_object($arg))
      return 'object('.get_class($arg).')';
    elseif (is_array($arg))
      return 'array('.$this->flattenArray($arg).')';
    else
      return $arg;
  }

  /**
   * Convert an array into a comma separated string.
   *
   * @access private
   * @param array array The array to convert.
   * @return str Converted array.
   */
  function flattenArray($array) 
  {
    $result = '';
    foreach ($array as $index => $var) 
    {
      if ( $result != '' )
        $result .= ', ';
      $result .= $index.'='.$this->makeVarViewable($var);
    }
    return $result;
  }
  
  /**
   * Get the current time.
   *
   * @access private
   * @return int The time in microseconds.
   */
  function getTime() 
  {
    $microtime = explode(' ', microtime());
    return $microtime[0] + $microtime[1];
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


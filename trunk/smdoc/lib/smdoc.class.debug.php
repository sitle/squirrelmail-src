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
 * Debug class
 * -------------------------------------------------------------
 * Class containing methods/member variables maintaining
 * debug information.
 * -------------------------------------------------------------
 */
class smdoc_debug
{
  var $debugTrackString = '', 
      $debugTrackDepth = 0, 
      $debugDBAccessNumber = 0, 
      $debugStartTime;  

  function getTime() 
  {
    $microtime = explode(' ', microtime());
    return $microtime[0] + $microtime[1];
  }
  
  function executionTime($startTime) 
  {
    return $this->getTime() - $startTime;
  }

  /*
   * Static smdoc_debug Constructor
   * -------------------------------------------------------------
   *  $debug_enabled - TRUE if debug is enabled.
   * -------------------------------------------------------------
   */
  function &new_smdoc_debug($debug_enabled)
  {
    $debug_enabled = getVarConstOrDefault($debug_enabled, 'DEBUG', FALSE);
    if ( $debug_enabled )
      return new smdoc_debug();
      
    return NULL;      
  }

  /*
   * smdoc_debug Constructor
   * -------------------------------------------------------------
   */
  function smdoc_debug()
  {
    $this->debugStartTime = $this->getTime();
  }

    
  /*
   * track
   * -------------------------------------------------------------
   * Trace execution time, and additional optional parameters.
   * Calling track with parameters starts a trace block,
   * no parameters closes the block.
   *   $function - name of function, NULL to close block
   *   $parameters - optional function parameters.
   * -------------------------------------------------------------
   */
  function track($function = NULL, $parameters = NULL) 
  {
    if ($function) 
    {
      /*
       * If function specified, start a function call block
       */
      $this->debugTrackDepth++;
      $this->debugTrackString .= str_repeat('|', $this->debugTrackDepth - 1);
      $this->debugTrackString .= '/- '.round( $this->executionTime($this->debugStartTime), 3);

      /*
       * If additional arguments printed, add them to signature
       */
      if ( $parameters != NULL && count($parameters) > 0 ) 
      {
        $args = func_get_args();
        array_shift($args);  // shift off $function
        $parameters = '';
        foreach ($args as $key => $arg) 
        {
          if ($arg == NULL) 
            $args[$key] = 'NULL';
          elseif ($arg === TRUE) 
            $args[$key] = 'TRUE';
          elseif ($arg === FALSE)
            $args[$key] = 'FALSE';
    
          $parameters .= $args[$key].', ';               
        }
        $parameters = substr($parameters, 0, -2);
      } 
      else 
        $parameters = '';

      $this->debugTrackString .= ' '.$function.'('.$parameters.')<br />';
    } 
    else 
    {
      /*
       * Else if no function specified, close function call block
       */
      $this->debugTrackString .= str_repeat('|', $this->debugTrackDepth - 1);
      $this->debugTrackString .= '\- '.round($this->executionTime($this->debugStartTime), 3);
      $this->debugTrackString .= '<br />';
      $this->debugTrackDepth--;
    }
  }


  /*
   * DBTrack
   * -------------------------------------------------------------
   * Trace execution of SQL queries, echoing query string
   * -------------------------------------------------------------
   */
  function DBTrack($SQLString) 
  {
    $this->debugDBAccessNumber++;
    $this->debugTrackString .= str_repeat('|', $this->debugTrackDepth);
    $this->debugTrackString .= '  '.round($this->executionTime($this->debugStartTime), 3);
    $this->debugTrackString .= ' '.htmlspecialchars($SQLString).'<br />';
  }

  /*
   * debugDisplay
   * -------------------------------------------------------------
   * Display collected debug information
   * -------------------------------------------------------------
   */
  function debugDisplay(&$foowd) 
  {
    $debug = new smdoc_display('debug.tpl');
    $debug->assign('DB_ACCESS_COUNT', 
                    $this->debugDBAccessNumber);
    $debug->assign('EXECUTION_TIME',
                    round($this->executionTime($this->debugStartTime), 3));
    $debug->assign('DEBUG_TRACK_STRING',
                    $this->debugTrackString);
    
    $foowd->tpl->assign('DEBUG', $debug);
  }
}
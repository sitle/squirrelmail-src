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

setConst('DEBUG_CLASS', 'smdoc_debug');
include_once(PATH . 'env.debug.php');

class smdoc_debug extends foowd_debug {


  function smdoc_debug() {
    parent::foowd_debug();
  }

  function &factory($enabled) {
    if (getVarConstOrDefault($enabled, 'DEBUG', FALSE)) {
      return new smdoc_debug();
    } else {
      return FALSE;
    }
  }

	/**
	 * Function execution tracking.
	 *
	 * @class foowd_debug
	 * @method track
	 * @param str function The name of the function execution is entering.
	 * @param array args List of arguments passed to the function.
	 */
	function track($function, &$args) { // execution tracking
        if ($function) {
            $this->trackDepth++;
            $this->trackString .= $this->executionTime() . ' '
                               . str_repeat('|', $this->trackDepth - 1)
                               . '/- '.$function.'(';
            if ($args) { // get parameters if given
                $parameters = '';
                foreach ($args as $key => $arg) {
                    $parameters .= $this->makeVarViewable($arg).', ';
                }
                $this->trackString .= substr($parameters, 0, -2);
            }
            $this->trackString .= ')<br />';
        } else {
            $this->trackString .= $this->executionTime() . ' '
                               . str_repeat('|', $this->trackDepth - 1)
                               . '\- <br />';
            $this->trackDepth--;
        }
	}

	/**
	 * Add message to debugging output.
	 *
	 * @class foowd_debug
	 * @method msg
	 * @param str string The message to add.
	 */
	function msg($string) { // write debug message
		$this->trackString .= $this->executionTime() . ' '
                           . str_repeat('|', $this->trackDepth).' '
                           . htmlspecialchars($string).'<br />';
	}

	/**
	 * Calculate the current execution time.
	 *
	 * @class foowd_debug
	 * @method executionTime
	 * @return int The time in microseconds.
	 */
	function executionTime() { // calculate execution time
		return sprintf("%.3f", round($this->getTime() - $this->startTime, 3));
	}

  function display(&$foowd)
  {
    echo '<div class="debug_output">'
       . '<div class="debug_output_heading">Debug Information</div>'. "\n"
       . '<pre>'
       . 'Total DB Executions: '  . $this->DBAccessNumber . '&nbsp;' . "\n"
       . 'Total Execution Time: ' . $this->executionTime(). ' seconds'. "\n"
       . '</pre>'
       . '<div class="debug_output_heading">Execution History</div>'. "\n"
       . '<pre>' . $this->trackString . '</pre>';

    $show_var = getConstOrDefault('DEBUG_VAR', FALSE);
    if ( $show_var ) 
    {
      echo '<div class="debug_output_heading">Request</div>'. "\n";
      show($_REQUEST);
      echo '<div class="debug_output_heading">Session</div>'. "\n";
      show($_SESSION);
      echo '<div class="debug_output_heading">Cookie</div>'. "\n";
      show($_COOKIE);
    }
    echo '</div><br />';
  }

}


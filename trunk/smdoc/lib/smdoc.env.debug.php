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
    }
    echo '</div><br />';
  }

}


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
    $debug = $foowd->tpl->factory('debug.tpl');
    $debug->assign('DB_ACCESS_COUNT',
                    $this->DBAccessNumber);
    $debug->assign('EXECUTION_TIME', $this->executionTime());
    $debug->assign('DEBUG_TRACK_STRING',
                    $this->trackString);

    $foowd->tpl->assign('DEBUG', $debug);
  }

}


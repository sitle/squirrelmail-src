<?php
/*
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition/modification to the 
 * Framework for Object Orientated Web Development (Foowd).
 */

/**
 * Manage input via querystring ($_GET).
 * 
 * $Id$
 * @package smdoc
 * @subpackage input
 */

/** Include base input library functions and input base class */
require_once(INPUT_DIR . 'input.lib.php');

/**
 * Input querystring class.
 * Replacement for FOOWD input_querystring
 * 
 * This class defines an input querystring, it handles input validation, value
 * persistancy, and displaying the object.
 *
 * @package smdoc
 * @subpackage input
 */
class input_querystring extends input_base
{
  /**
   * Constructs a new querystring object.
   *
   * @param str name The name of the querystring object.
   * @param optional str regex The validation regular expression.
   * @param optional str value The initial contents value.
   */
  function input_querystring($name, $regex = NULL, $value = NULL)
  {
    parent::input_base($name, $regex, $value, FALSE, SQ_GET);
  }
}

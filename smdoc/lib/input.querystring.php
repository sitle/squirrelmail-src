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

require_once(SM_PATH . 'input.lib.php');

/**
 * Input querystring class.
 * Replacement for FOOWD input_querystring
 * 
 * This class defines an input querystring, it handles input validation, value
 * persistancy, and displaying the object.
 *
 * @package foowd/input
 * @class input_querystring
 */
class input_querystring extends input_base
{
  /**
   * Constructs a new base object.
   *
   * @constructor input_querystring
   * @param str name The name of the querystring object.
   * @param optional str regex The validation regular expression.
   * @param optional str value The initial contents value.
   */
  function input_querystring($name, $regex = NULL, $value = NULL)
  {
    parent::input_base($name, $regex, $value, SQ_GET);
  }
}

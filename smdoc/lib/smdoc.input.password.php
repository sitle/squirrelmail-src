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

include_once(PATH.'input.textbox.php');

class input_verify_passwordbox extends input_passwordbox {

  var $otherbox;

  /**
   * Constructs a new textbox object.
   *
   * @constructor input_textbox
   * @param str name The name of the textbox.
   * @param object box Textbox to verify against.
   * @param optional str regex The validation regular expression.
   * @param optional str value The initial contents value.
   * @param optional str caption The caption to display by the textbox.
   * @param optional int size The width of the textbox.
   * @param optional int maxlength The maximum length of input.
   * @param optional str class The CSS class for this textbox.
   * @param optional bool required Whether the texbox is allowed to contain no value.
   */
  function input_verify_passwordbox($name, &$box, $regex = NULL, $value = NULL, $caption = NULL, $size = NULL, $maxlength = NULL, $class = NULL, $required = TRUE) {
    $this->otherbox = &$box;
    parent::input_textbox($name, $regex, $value, $caption, $size, $maxlength, $class, $required);
  } 

  /**
   * Sets the value of the textbox.
   *
   * @class input_textbox
   * @method set
   * @param str value The value to set the textbox to.
   * @return bool TRUE on success.
   */
  function set($value) 
  {
    if (get_magic_quotes_gpc()) 
      $value = stripslashes($value); 

    if ( $this->regex == NULL || preg_match($this->regex, $value) )
    {
      // value passed regex requirements, check against other box
      if ( $this->otherbox->wasSet && 
           $value == $this->otherbox->value )
      {
        $this->value = $value;
        $this->wasSet = TRUE;
        return TRUE;
      }
    }
    $this->wasSet = FALSE;
    return FALSE;
  }

}

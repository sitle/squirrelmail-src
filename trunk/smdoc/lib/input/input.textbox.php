<?php
/*
 * Copyright (c) 2003-2004 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition/modification to the 
 * Framework for Object Orientated Web Development (Foowd).
 */

/** 
 * Manage input from form textboxes.
 *
 * $Id$
 *
 * @package smdoc
 * @subpackage input
 */

/** Include base input library functions and input base class */
require_once(INPUT_DIR . 'input.lib.php');

/** Define maximum and minimum size for textbox. */
if (!defined('INPUT_TEXTBOX_SIZE_MIN')) define('INPUT_TEXTBOX_SIZE_MIN', 4);
if (!defined('INPUT_TEXTBOX_SIZE_MAX')) define('INPUT_TEXTBOX_SIZE_MAX', 50);

/**
 * Input textbox class.
 *
 * This class defines an input textbox, it handles input validation, value
 * persistancy, and displaying the object.
 *
 * @package smdoc
 * @subpackage input
 */
class input_textbox extends input_base
{
  /**
   * The textboxes caption.
   *
   * @var string
   */
  var $caption;

  /**
   * The textbox type.
   *
   * @var string
   */
  var $type;
  
  /**
   * Constructs a new textbox object.
   *
   * @param string name The name of the textbox.
   * @param string regex The validation regular expression.
   * @param string value The initial contents value.
   * @param string caption The caption to display by the textbox.
   * @param bool required Whether the texbox is allowed to contain no value.
   */
  function input_textbox($name, $regex = NULL, $value = NULL, $caption = NULL, $required = TRUE) 
  {
    $this->type = 'text';
    $this->caption = $caption;
    parent::input_base($name, $regex, $value, $required);
  }
  
  /**
   * Display the textbox.
   */
  function display($class = NULL, $size = NULL) 
  {
    $maxlength = getRegexLength($this->regex, 16);
    if ( $size == NULL )
      $size = ($maxlength == 0) ? INPUT_TEXTBOX_SIZE_MAX : $maxlength;

    ensureIntInRange($size, INPUT_TEXTBOX_SIZE_MIN, INPUT_TEXTBOX_SIZE_MAX);

    if ( $this->form->submitted() && 
         ( ( $this->required && !$this->wasSet ) || 
           ( $this->wasSet && !$this->wasValid ) ) )
      $class = 'error';

    $type  = ' type="'.$this->type.'"';
    $name  = ' name="'.$this->name.'"';
    $value = ' value="'.htmlentities($this->value).'"';
    $size  = ' size="'.$size.'"';
    $class = ( $class == NULL ) ? ''  : ' class="'.$class.'"';
    $maxlength = ( $maxlength == 0 )  ? ''   : ' maxlength="'.$maxlength.'"';
    $required  = ( $this->required )  ? ' *' : '';

    echo '<input'.$type.$name.$value.$size.$maxlength.$class.' />'.$required;
  }
}

//----------- input_password ---------------------------------------------------

/**
 * Input password textbox class.
 *
 * This class defines an input password textbox. It differs from the standard
 * textbox by hiding the input of the user.
 *
 * @package smdoc
 * @subpackage input
 */
class input_passwordbox extends input_textbox 
{
  /**
   * Textbox to verify contents against
   *
   * @var object
   */
  var $verify;

  /**
   * Constructs a new passwordbox object.
   *
   * @param string name The name of the textbox.
   * @param string regex The validation regular expression.
   * @param string value The initial contents value.
   * @param string caption The caption to display by the textbox.
   * @param bool required Whether the texbox is allowed to contain no value.
   */
  function input_passwordbox($name, $regex = NULL, $value = NULL, $caption = NULL, $required = TRUE) 
  {
    parent::input_textbox($name, $regex, $value, $caption, $required);
    $this->type='password';
  }
}

//------------ input_hiddenbox --------------------------------------------------

/**
 * Input hidden textbox class.
 *
 * This class defines an input hidden textbox. It differs from the standard
 * textbox by no being visible to the user and thus not accepting user input.
 *
 * @package smdoc
 * @subpackage input
 */
class input_hiddenbox extends input_textbox 
{
  /**
   * Constructs a new hidden form object.
   *
   * @param string name The name of the textbox.
   * @param string regex The validation regular expression.
   * @param string value The initial contents value.
   * @param string caption The caption to display by the textbox.
   * @param bool required Whether the texbox is allowed to contain no value.
   */
  function input_hiddenbox($name, $regex = NULL, $value = NULL, $required = TRUE) 
  {
    parent::input_textbox($name, $regex, $value, NULL, $required);
    $this->type='hidden';
  }

  /**
   * Display the hidden textbox.
   */
  function display() 
  {
    $type  = 'type="'.$this->type.'" ';
    $name  = 'name="'.$this->name.'" ';
    $value = 'value="'.htmlentities($this->value).'" ';

    echo '<input '.$type.$name.$value.'" />';
  }
}

?>

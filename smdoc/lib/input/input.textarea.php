<?php
/*
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition/modification to the 
 * Framework for Object Orientated Web Development (Foowd).
 */

/**
 * Manage input from form text areas.
 *
 * $Id$
 *
 * @package smdoc
 * @subpackage input
 */

/** Include base input library functions and input base class */
require_once(INPUT_DIR . 'input.lib.php');

/** 
 * Define maximum width and height parameters for text areas.
 */
if (!defined('INPUT_TEXTAREA_WIDTH_MIN')) define('INPUT_TEXTAREA_WIDTH_MIN', 20);
if (!defined('INPUT_TEXTAREA_WIDTH_MAX')) define('INPUT_TEXTAREA_WIDTH_MAX', 80);
if (!defined('INPUT_TEXTAREA_HEIGHT_MIN')) define('INPUT_TEXTAREA_HEIGHT_MIN', 4);
if (!defined('INPUT_TEXTAREA_HEIGHT_MAX')) define('INPUT_TEXTAREA_HEIGHT_MAX', 20);

/**
 * Input textarea class.
 *
 * This class defines an input textarea, it handles input validation, value
 * persistancy, and displaying the object.
 *
 * @package smdoc
 * @subpackage input
 */
class input_textarea extends input_base 
{
  /**
   * The textareas caption.
   *
   * @var string
   */
  var $caption;

  /**
   * Constructs a new textarea object.
   *
   * @param string name The name of the textarea.
   * @param string regex The validation regular expression.
   * @param string value The initial contents value.
   * @param string caption The caption to display by the textarea.
   */
  function input_textarea($name, $regex = NULL, $value = NULL, $caption = NULL) 
  {
    $this->caption = $caption;
    parent::input_base($name, $regex, $value, FALSE);
  }
  
  /**
   * Display the textarea.
   */
  function display($class = NULL, $width = NULL, $height = NULL) 
  {
    $maxlength = getRegexLength($this->regex, INPUT_TEXTAREA_WIDTH_MAX);

    if ( $width == NULL )
      $width = ($maxlength == 0) ? INPUT_TEXTAREA_WIDTH_MAX : (int) ($maxlength / 2);

    if ( $width > INPUT_TEXTAREA_WIDTH_MAX ) 
      $width = INPUT_TEXTAREA_WIDTH_MAX;
    elseif ( $width < INPUT_TEXTAREA_WIDTH_MIN )
      $width = INPUT_TEXTAREA_WIDTH_MIN;

    if ( $height == NULL )
      $height = ($maxlength == 0) ? INPUT_TEXTAREA_HEIGHT_MAX : (int) ($maxlength / 10);

    if ( $height > INPUT_TEXTAREA_HEIGHT_MAX ) 
      $height = INPUT_TEXTAREA_HEIGHT_MAX;
    elseif ( $height < INPUT_TEXTAREA_HEIGHT_MIN )
      $height = INPUT_TEXTAREA_HEIGHT_MIN;

    $name  = 'name="'.$this->name.'" ';
    $value = 'value="'.htmlentities($this->value).'" ';
    $width = 'cols="'.$width.'" ';
    $height = 'rows="'.$height.'" ';
    $class  = ( $class == NULL ) ? ''  : 'class="'.$class.'" ';
    $maxlength = ( $maxlength == 0 )  ? ''   : 'maxlength="'.$maxlength.'" ';

    echo '<textarea '.$name.$width.$height.$class.'wrap="virtual" >'."\n"
         .htmlentities($this->value)."\n"
         .'</textarea>'."\n";
  }
}

?>

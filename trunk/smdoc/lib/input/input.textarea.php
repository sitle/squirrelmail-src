<?php
/*
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition to the Framework for Object Orientated Web Development (Foowd).
 *
 * It provides methods for managing groups and tracking permissions to 
 * consolidate operations using groups without using the groups class.
 *
 * $Id$
 */
require_once(INPUT_DIR . 'input.lib.php');

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
 * @package smdoc/input
 */
class input_textarea extends input_base 
{
  /**
   * The textareas caption.
   *
   * @type str
   */
  var $caption;

  /**
   * Constructs a new textarea object.
   *
   * @param str name The name of the textarea.
   * @param str regex The validation regular expression.
   * @param str value The initial contents value.
   * @param str caption The caption to display by the textarea.
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

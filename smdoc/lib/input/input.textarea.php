<?php
/*
 * Copyright (c) 2003-2004 The SquirrelMail Project Team
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
   * The maximum number of characters allowed in the textarea.
   * 
   * <p>This is not part of the HTML 4.0.1 spec, and is not directly
   * supported or displayed as part of the textarea. If a maximum
   * is specified, it is used as a hint to the template (in case it
   * wants to provide javascript hints or other information to the user),
   * and it can be used to calculate the width and height of the area 
   * at display time if the values are not otherwise provided by
   * the caller.
   *
   * <p>If maxlength is unspecified, it's value is FALSE.
   *
   * @var int
   */
  var $maxlength;


  /**
   * Constructs a new textarea object.
   *
   * @param string name The name of the textarea.
   * @param string regex The validation regular expression.
   * @param string value The initial contents value.
   * @param string caption The caption to display by the textarea.
   */
  function input_textarea($name, $regex = NULL, $value = NULL, $caption = NULL, $maxlength = FALSE) 
  {
    $this->name = $name;
    $this->regex = $regex;
    $this->required = FALSE;
    $this->form = NULL;
    $this->caption = $caption;
    $this->maxlength = $maxlength;
    $this->default = $value;
    
    if ( sqGetGlobalVar($name, $new_value, SQ_FORM) )
    {
      $this->wasSet = TRUE;
      $this->wasValid = $this->set($new_value);
    }

    // If either it wasn't set, or 
    // Value was not null and it wasn't a string..
    // Then set to the default.
    if ( !$this->wasSet || 
         ( $value != NULL && !is_string($value) ) )
      $this->value = $value;  
  }

  /**
   * Sets the value of the textarea.
   * If a maxlength was specified, ensures that the 
   * textarea contents do not exceed that length.
   *
   * @param string value The value to set.
   * @return bool TRUE on success.
   */
  function set($value)
  {
    if ( $value == NULL || is_string($value) )
    {
      $this->value = $value;

      if ( $this->maxlength === FALSE ||
           strlen($value) <= $this->maxlength )
        return TRUE;
    }

    return FALSE;
  }
  
  /**
   * Display the textarea.
   *
   * <p> Called by templates to render text area form elements.
   *
   * @param string class CSS class assigned to text area
   * @param int width Number of columns for text area - CSS can substitute
   * @param int height Number of rows for text area
   */
  function display($class = NULL, $width = NULL, $height = NULL) 
  {
    // If maxlength was given, use it. Otherwise, check if the regex specified
    // a maximum length,  and if that didn't use the max width * max height.
    $maxlength = ($this->maxlength !== FALSE) ? $this->maxlength : 
                 getRegexLength($this->regex, INPUT_TEXTAREA_WIDTH_MAX*INPUT_TEXTAREA_HEIGHT_MAX);

    if ( $this->wasSet && !$this->wasValid )
      $class .= ' error';

    $name  = 'name="'.$this->name.'" ';
    $id    = 'id="'.$this->name.'" ';
    $value = 'value="'.htmlentities($this->value).'" ';
    $class  = ( $class == NULL ) ? ''  : 'class="'.$class.'" ';

    // If we have no width, nor no CSS class to specify it for us 
    if ( $width == NULL  && $class == '' )
      $width = ($maxlength == 0) ? INPUT_TEXTAREA_WIDTH_MAX : (int) ($maxlength / 2);

    // make sure the width in range, and wrap it with pretty cols=""
    ensureIntInRange($width, INPUT_TEXTAREA_WIDTH_MIN, INPUT_TEXTAREA_WIDTH_MAX);
    $width = ( $width == NULL ) ? '' : 'cols="'.$width.'" ';

    // A height HAS to be specified - no CSS out, here.
    if ( $height == NULL )
      $height = ($maxlength == 0) ? INPUT_TEXTAREA_HEIGHT_MAX : (int) ($maxlength / 10);

    // ensure that the height is in range, and wrap it with requisite rows=""
    ensureIntInRange($height, INPUT_TEXTAREA_HEIGHT_MIN, INPUT_TEXTAREA_HEIGHT_MAX);

    $height = 'rows="'.$height.'" ';

    // and finally, display it! 
    echo '<textarea '.$name.$id.$width.$height.$class.'wrap="virtual" >'."\n"
         .htmlentities($this->value)."\n"
         .'</textarea>'."\n";
  }
}

?>

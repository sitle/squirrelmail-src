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
require_once(INPUT_DIR . 'input.lib.php');

/**
 * Input checkbox class.
 *
 * This class defines an input checkbox, it handles value persistancy, and
 * displaying the object.
 *
 * @package smdoc/input
 */
class input_checkbox extends input_base 
{
  /**
   * Whether the checkbox is checked.
   *
   * @type bool
   */
  var $checked;
  
  /**
   * The checkboxes caption.
   *
   * @type str
   */
  var $caption;

  /**
   * Constructs a new checkbox object.
   *
   * @param str name The name of the checkbox.
   * @param bool checked The initial checkbox state.
   * @param str caption The caption to display by the checkbox.
   */
  function input_checkbox($name, $checked = FALSE, $caption = NULL) 
  {
    $this->caption = $caption;
    $this->name = $name;
    $this->regex = NULL;
    $this->required = FALSE;
    $this->value = NULL;
    
    if ( sqGetGlobalVar($name, $new_value, SQ_FORM) )
    {
      $this->wasSet = TRUE;
      $this->wasValid = TRUE;
      $this->checked = TRUE;
    } 
    elseif ( $checked && count($_POST) == 0)
      $this->checked = TRUE;
    else
      $this->checked = FALSE;
    $this->value = $this->checked;
  }

  /**
   * Sets the value of the checkbox.
   *
   * @param bool value The value to set the checkbox to.
   * @return bool TRUE on success.
   */  
  function set($value) 
  {
    if (is_bool($value)) 
    {
      $this->checked = $value;
      $this->value = $value;
      return TRUE;
    }
    
    return FALSE;
  }

  /**
   * Display the checkbox.
   */
  function display($class = NULL, $id = NULL) 
  {
    $type  = 'type="checkbox" ';
    $name  = 'name="'.$this->name.'" ';
    $id    = 'id="'.(( $id == NULL ) ? $this->name : $id ).'" ';
    $title = ( $this->caption == NULL ) ? '' : 'title="'.$this->caption.'" ';
    $class = ( $class == NULL ) ? ''  : 'class="'.$class.'" ';
    $checked = ( $this->checked ) ? 'checked ' : '';
    
    echo '<input '.$type.$name.$id.$title.$class.$checked.'/>';
  }
}

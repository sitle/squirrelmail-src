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

/**
 * Input radio class.
 *
 * This class defines an input radio group, it handles input validation, value
 * persistancy, and displaying the object.
 *
 * @package smdoc/input
 */
class input_radio extends input_base 
{
  /**
   * The radio buttons in the radio group.
   *
   * @type array
   */
  var $buttons;

  /**
   * Constructs a new radio group.
   *
   * @param str name The name of the radio.
   * @param int value The initial value.
   * @param array buttons The buttons in the radio object.
   */
  function input_radio($name, $value = NULL, $buttons = NULL) 
  {
    $this->buttons = $buttons;
    parent::input_base($name, NULL, $value);    
  }
  
  /**
   * Sets the value of the radio group.
   *
   * @param str value The value to set the radio group to.
   * @return bool TRUE on success.
   */
  function set($value) 
  {
    reset($this->buttons);                  // go to beginning of button array
    if ( $value >= key($this->buttons) ) 
    {
      end($this->buttons);                  // go to end of button array
      if ($value <= key($this->buttons)) 
      {
        $this->value = $value;
        return TRUE;
      }
    }
    return FALSE;
  }
  
  /**
   * Display the radio group.
   */
  function display($class = NULL, $id = NULL) 
  {
    $type  = 'type="radio" ';
    $name  = 'name="'.$this->name.'" ';
    $class = ( $class == NULL ) ? ''  : 'class="'.$class.'" ';

    foreach ($this->buttons as $index => $button) 
    {
      $id  = 'id="'.(( $id == NULL ) ? $this->name : $id ).'_'.$index.'" ';
      $title = 'title="'.$button.'" ';
      $value = 'value="'.$index.'" ';
      $checked = ( $index == $this->value ) ? 'checked' : ''; 

      echo '<input '.$type.$name.$class.$id.$title.$value.$checked.'" />';
    }
  }

}

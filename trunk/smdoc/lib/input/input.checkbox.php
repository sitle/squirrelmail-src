<?php
/*
 * Copyright (c) 2003-2004 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition/modification to the 
 * Framework for Object Orientated Web Development (Foowd).
 *
 */

/**
 * Manage Checkbox Form input
 *
 * Modified by SquirrelMail Development
 * $Id$
 *
 * @package smdoc
 * @subpackage input
 */

/** Include base input library functions and input base class */
require_once(INPUT_DIR . 'input.lib.php');

/**
 * Input checkbox class.
 *
 * This class defines an input checkbox, it handles value persistancy, and
 * displaying the object.
 *
 * @package smdoc
 * @subpackage input
 */
class input_checkbox extends input_base 
{
  /**
   * Whether the checkbox is checked.
   */
  var $checked;
  
  /**
   * The checkboxes caption.
   */
  var $caption;

  /**
   * Constructs a new checkbox object.
   *
   * @param string name The name of the checkbox.
   * @param bool checked The initial checkbox state.
   * @param string caption The caption to display by the checkbox.
   */
  function input_checkbox($name, &$form, $checked = FALSE, $caption = NULL) 
  {
    $this->caption = $caption;
    $this->name = $name;
    $this->regex = NULL;
    $this->required = FALSE;
    $this->default = $checked;
    $this->wasSet = FALSE;
    $this->wasValid = FALSE;
    $this->default = $checked;

    if ( $form->submitted() )
    {
        $this->wasSet = TRUE;
        $this->wasValid = TRUE;
        
        $this->checked = sqGetGlobalVar($name, $new_value, SQ_FORM);
    }
    else
        $this->set($checked);

    $this->value = $this->checked;
  }

  function reset()
  {
    $this->set($this->default);
    $this->wasSet = FALSE;
    $this->wasValid = FALSE;
  }

  /**
   * Sets the value of the checkbox.
   *
   * @param bool value The value to set the checkbox to.
   * @return bool TRUE on success.
   */  
  function set($value) 
  {
    $ok = FALSE;

    if (is_bool($value)) 
    {
      $this->checked = $value;
      $ok = TRUE;
    } 
    elseif ( is_int($value) )
    {
      $this->checked = ( $value == 0 ? FALSE : TRUE );
      $ok = TRUE;
    }
    elseif ( is_string($value) && is_numeric($value) )
    {
      $this->checked = ( $value == '0' ? FALSE : TRUE );
      $ok = TRUE;
    }

    if ( $ok )
        $this->value = $this->checked;
    
    return $ok;
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
    $checked = ( $this->checked ) ? 'checked="checked" ' : '';
    
    echo '<input '.$type.$name.$id.$title.$class.$checked.'/>';
  }
}

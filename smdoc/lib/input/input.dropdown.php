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
 * Dropdown list class.
 *
 * This class defines a dropdown listbox, it handles input validation, value
 * persistancy, and displaying the object.
 *
 * @package smdoc/input
 */
class input_dropdown extends input_base
{
  /**
   * The dropdown lists caption.
   *
   * @type str
   */
  var $caption;

  /**
   * Array of list items.
   *
   * @type array
   */
  var $items;

  /**
   * Dropdown list allows multiple selection.
   *
   * @type bool
   */
  var $multiple;

  /**
   * Constructs a new dropdown list object.
   *
   * @param str name The name of the dropdown list.
   * @param str value The initial selected item.
   * @param array items List of items to choose from
   * @param str caption The caption to display by the dropdown list.
   * @param bool multiple Dropdown list allows multiple selection.
   */
  function input_dropdown($name, $value = NULL, $items = NULL, $caption = NULL, $multiple = FALSE) 
  {
    $this->items = $items;
    $this->caption = $caption;
    $this->multiple = $multiple;

    parent::input_base($name, NULL, $value);
  }    

  /**
   * Sets the value of the dropdown list.
   *
   * @param str value The item to select in the dropdown list.
   * @return bool TRUE on success.
   */
  function set($value)
  {
    if ( is_array($value) )
    {
      if ( $this->multiple )
      {
        foreach($value as $ord => $key)
        {
          if ( !isset($this->items[$key]) )
            return FALSE;
        }

        $this->value = $value;
        return TRUE;
      }
      else
      {
        if ( isset($value[0]) )
          return $this->set($value[0]); // recurse for single value
      }
    }
    else
    {
      if ( is_numeric($value) ) 
        $value = intval($value);

      if ( isset($this->items[$value]) ) 
      {
        if ( $this->multiple )
          $this->value[] = $value;
        else
          $this->value = $value;
        return TRUE;
      }
    }
    return FALSE;
  }


  /**
   * Display the dropdown list.
   */
  function display($class = NULL, $visibleItems = 1) 
  {
    $class = ( $class == NULL ) ? ''  : ' class="'.$class.'"';
    $multiple = ( $this->multiple ) ? ' multiple' : '';
    $size = ' size="'.$visibleItems.'"';
    $name  = ' name="'.$this->name.'[]"';


    echo ' <select',$multiple,$name,$size,$class,'>'."\n";

    foreach ($this->items as $val => $item) 
    {
      $value = ' value="'.$val.'"';
      $selected = '';
      
      if ( $this->value == $val ||
           ($this->multiple && is_array($this->value) && in_array($val, $this->value)) )
        $selected = ' selected="selected"';

      echo '<option',$selected,$value,'>',$item,'</option>'."\n";
    }
    echo '</select>'."\n";
  }

}

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
      if ( $multiple )
      {
        foreach($value as $val)
        {
          if ( !array_key_exists($val, $this->items) )
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
  function display() {
    echo $this->caption, ' <select name="', $this->name;
    if ($this->multiple) {
      echo '[]" multiple="multiple';
    }
    echo '" size="', $this->height, '" class="', $this->class, '">';
    foreach ($this->items as $value => $item) {
      echo '<option value="', $value, '"';
      if ($this->multiple && is_array($this->value)) {
        if (in_array($value, $this->value)) echo ' selected="selected"';
      } else {
        if ($this->value == $value) echo ' selected="selected"';
      }
      echo '>', $item, '</option>';
    }
    echo '</select>';
  }

}

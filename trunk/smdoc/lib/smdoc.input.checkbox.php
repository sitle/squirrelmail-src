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

include_once(PATH.'input.checkbox.php');

/**
 * Input checkbox class.
 *
 * This class defines an input checkbox, it handles value persistancy, and
 * displaying the object.
 *
 * @package foowd/input
 * @class input_checkbox
 */
class input_smdoc_checkbox extends input_checkbox {

  var $followtext;

  /**
   * Constructs a new checkbox object.
   *
   * @constructor input_checkbox
   * @param str name The name of the checkbox.
   * @param optional bool checked The initial checkbox state.
   * @param optional str caption The caption to display by the checkbox.
   * @param optional str class The CSS class for this checkbox.
   * @param optional str followtext Text to follow the checkbox.
   */
  function input_smdoc_checkbox($name, $checked = FALSE, $caption = NULL, $class = NULL, $followtext = NULL) 
  {
    parent::input_checkbox($name, $checked, $caption, $class);
    $this->followtext = $followtext;
  }

  /**
   * Display the checkbox.
   *
   * @class input_checkbox
   * @method display
   */
  function display() 
  {
    $title = ($this->caption) ? ' title="'. $this->caption. '"' : '';
    $class = ($this->class)   ? ' class="'. $this->class. '"' : '';
 
    echo '<input name="', $this->name, '" id="', $this->name, '" type="checkbox" ';
    if ($this->checked) echo 'checked="checked" ';
    echo $title, $class, ' />';
    if ($this->caption) echo '<label for="'.$this->name.'">'.$this->caption.'</label>';
    if ($this->followtext) echo $this->followtext;
  }

}

?>

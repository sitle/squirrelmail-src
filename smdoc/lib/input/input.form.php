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

define('FORM_DEFAULT_SUBMIT', _("Submit"));
define('FORM_DEFAULT_PREVIEW', _("Preview"));
define('FORM_DEFAULT_RESET', _("Reset"));
define('FORM_DEFAULT_CANCEL', _("Cancel"));

/**
 * Input form class.
 * Replacement for FOOWD input_form
 *
 * This class defines an input form and has methods for adding form objects to
 * the form, displaying the form, and requesting the forms state.
 *
 * @package smdoc/input
 */
class input_form 
{
  /**
   * The name of the form.
   *
   * @type str
   */
  var $name;

  /**
   * URI for form to submit to.
   *
   * @type str
   */
  var $location;

  /**
   * The submit method to use: SQ_POST or SQ_GET
   *
   * @type int
   */
  var $method;

  /**
   * Caption of the submit button.
   *
   * @type str
   */
  var $submit;

  /**
   * Cancel this action.
   *
   * @type str
   */
  var $cancel;

  /**
   * Caption of the reset button.
   *
   * @type str
   */
  var $reset;

  /**
   * Caption of the preview button.
   *
   * @type str
   */
  var $preview;

  /**
   * Form objects in this form.
   *
   * @type array
   */
  var $objects = array();

  /**
   * Constructs a new form object.
   *
   * @param str name The name of the form.
   * @param str location URI for form to submit to.
   * @param str method The submit method to use.
   * @param str submit Caption of the submit button.
   * @param str reset Caption of the reset button.
   * @param str preview Caption of the preview button.
   */
  function input_form($name, $location = NULL, $method = 'POST', 
                      $submit = FORM_DEFAULT_SUBMIT, 
                      $reset = FORM_DEFAULT_RESET, 
                      $preview = NULL) 
  {
    $this->name = $name;

    if ($location == NULL) 
    {
      $location = getURI();
      if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] != '') 
        $location .= '?'.$_SERVER['QUERY_STRING'];
    }

    if ( is_string($method) ) 
    {
      $method = ( $method == 'POST' ) ? SQ_POST : SQ_GET;
    }

    $this->location = $location;
    $this->method = $method;
    $this->submit = $submit;
    $this->reset = $reset;
    $this->preview = $preview;
  }
  
  /**
   * Add a form object to the form.
   *
   * @param object object The form object to add.
   * @return bool TRUE on success.
   */
  function addObject($object)
  {
    if ( is_object($object) && isset($object->name) )
    {
      $object->form =& $this;
      $this->objects[$object->name] =& $object;
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Has the form been submitted?
   *
   * @return bool TRUE if the form has been submitted.
   */
  function submitted() 
  {
    if ( sqGetGlobalVar($this->name.'_submit', $new_value, $this->method) )
      return TRUE;

    return FALSE;
  }

  /**
   * Has the form been previewed?
   *
   * @return bool TRUE if the form has been previewed.
   */
  function previewed() 
  {
    if ( sqGetGlobalVar($this->name.'_preview', $new_value, $this->method) )
      return TRUE;

    return FALSE;
  }
  
  /**
   * Display the form header. This method should be used in conjunction with
   * {@link input_form::display_end} and requires the form objects within the
   * form to be manually told to display themselves. This can be useful if you
   * need finer granularity over the forms look without having to sub-class.
   */
  function display_start($form_class = NULL) 
  {
    $method = ( $this->method == SQ_POST ) ? 'POST' : 'GET';

    $enctype = 'enctype="multipart/form-data" ';
    $method  = 'method="'.$method.'" ';
    $action  = 'action="'.$this->location.'" ';
    $name    = 'name="'.$this->name.'" ';
    $class = ( $form_class == NULL ) ? '' : 'class="'.$form_class.'" ';
 
    echo '<form '.$name.$action.$method.$enctype.$class.'>'."\n";
  }

  /**
   * Display the form footer. See {@link input_form::display_start} for more.
   */
  function display_buttons($button_class = NULL, $appendTypeToClass = FALSE) 
  {
    // Only append type to class if a class is defined
    if ( $button_class == NULL )
    {
      $appendTypeToClass = FALSE;
      $class = '';
    }
    else
    {
      $class = 'class="'.$button_class;
      $class .= $appendTypeToClass ? '' : '" ';
    }

    if ( $this->submit )
    {
      $button_class = $appendTypeToClass ? '_submit" ' : '';
      $name  = 'name="'.$this->name.'_submit" ';
      echo '<input type="submit" '.$class.$button_class.$name.'value="'.$this->submit.'" />';
    }
    if ( $this->preview )
    {
      $button_class = $appendTypeToClass ? '_preview" ' : '';
      $name  = 'name="'.$this->name.'_preview" ';
      echo '<input type="submit" '.$class.$button_class.$name.'value="'.$this->preview.'" />';
    }
    echo '&nbsp;&nbsp;';
    if ( $this->reset )
    {
      $button_class = $appendTypeToClass ? '_reset" ' : '';
      $name  = 'name="'.$this->name.'_reset" ';
      echo '<input type="reset" '.$class.$button_class.$name.'value="'.$this->reset.'" />';
    }

    $button_class = $appendTypeToClass ? '_cancel" ' : '';
    $name  = 'name="form_cancel" ';
    echo '<input type="submit" '.$class.$button_class.$name.'value="'.FORM_DEFAULT_CANCEL.'" />';
  }

  function display_end()
  {
    echo '</form>'."\n";
  }
}

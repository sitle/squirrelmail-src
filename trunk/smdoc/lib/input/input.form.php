<?php
/*
 * Copyright (c) 2003-2004 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition/modification to the 
 * Framework for Object Orientated Web Development (Foowd).
 */

/** 
 * Manage grouping of input elements in a form
 * that also manages form attributes (get/post, action, 
 * submit/reset/preview button names, etc.).
 *
 * $Id$
 * @package smdoc
 * @subpackage input
 */

/** Include base input library functions and input base class */
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
 * @package smdoc
 * @subpackage input
 */
class input_form 
{
  /**
   * The name of the form.
   *
   * @var string
   */
  var $name;

  /**
   * URI for form to submit to.
   *
   * @var string
   */
  var $location;

  /**
   * The submit method to use: SQ_POST or SQ_GET
   *
   * @var int
   */
  var $method;

  /**
   * Caption of the submit button.
   *
   * @var string
   */
  var $submit;
  
  /**
   * Additional submit buttons
   * @var array
   */
  var $other_submit = array();

  /**
   * Caption of the reset button.
   *
   * @var string
   */
  var $reset;

  /**
   * Caption of the cancel button.
   *
   * @var string
   */
  var $cancel;

  /**
   * Form objects in this form.
   *
   * @var array
   */
  var $objects = array();

  /**
   * Constructs a new form object.
   *
   * @param string name The name of the form.
   * @param string location URI for form to submit to.
   * @param mixed method The submit method to use ('get', 'post', SQ_GET, SQ_POST)
   * @param string submit Caption of the submit button.
   * @param string reset Caption of the reset button.
   * @param string cancel Caption of the cancel button.
   */
  function input_form($name, $location = NULL, $method = SQ_POST, 
                      $submit = FORM_DEFAULT_SUBMIT, 
                      $reset = FORM_DEFAULT_RESET,
                      $cancel = FORM_DEFAULT_CANCEL) 
  {
    $this->name = $name;

    if ($location == NULL) 
      $location = getURI($_GET);

    if ( is_string($method) ) 
      $method = ( strtolower($method) == 'post' ) ? SQ_POST : SQ_GET;
    else 
      $method = ( $method == SQ_POST ) ? SQ_POST : SQ_GET;

    $this->location = $location;
    $this->method = $method;
    $this->submit = $submit;
    $this->reset = $reset;
    $this->cancel = $cancel;
  }

  /**
   * Reset all form elements
   */
  function reset($group = NULL)
  {
    if ( $group == NULL )
        $this->_resetArray($this->objects);
    else
    {
        if ( !isset($this->objects[$group]) )
            return;
        $this->_resetArray($this->objects[$group]);
    }    
  }

  /**
   * Utility function
   * Reset contents of given array
   */
  function _resetArray(&$objArray)
  {
    if ( !is_array($objArray) || empty($objArray) )
        return;

    $keys = array_keys($objArray);
    foreach ( $keys as $k )
    {
        if ( is_array($objArray[$k]) )
            $this->resetArray($objArray[$k]);
        else
            $objArray[$k]->reset();
    }
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
   * Add to a group of elements
   * under one name ( nested array )
   * @param string    group Name of element group
   * @param object object The form object to add.
   * @return bool TRUE on success.
   */
  function addToGroup($group, $object)
  {
    if ( is_object($object) && isset($object->name) && isset($group) )
    {
      $object->form =& $this;
      $this->objects[$group][$object->name] =& $object;
      return TRUE;
    }

    return FALSE;
  }

  /** 
   * Add additional submit button
   */
  function addSubmitButton($name, $caption)
  {
    $this->other_submit[$name] = $caption;
  }

  /**
   * Has the form been submitted with one of the 'other' buttons?
   */
  function otherSubmitted($name)
  {
    if ( sqGetGlobalVar($this->name.'_'.$name, $new_value, $this->method) )
      return TRUE;

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
   * Has the form been cancelled?
   * Can be called statically. 
   * @return bool TRUE if the form has been cancelled
   */
  function cancelled() 
  {
    if ( sqGetGlobalVar('input_form_cancel', $new_value, SQ_FORM) )
      return TRUE;

    return FALSE;
  }

  /**
   * Clear form post values.
   */
  function clearPost()
  {
    foreach($_POST as $k => $v)
        unset($_POST[$k]);
  }
  
  /**
   * Display the form header. This method should be used in conjunction with
   * {@link input_form::display_end} and requires the form objects within the
   * form to be manually told to display themselves. This can be useful if you
   * need finer granularity over the forms look without having to sub-class.
   */
  function display_start($form_class = NULL) 
  {
    $method = ( $this->method == SQ_POST ) ? 'post' : 'get';

    $enctype = 'enctype="multipart/form-data" ';
    $method  = 'method="'.$method.'" ';
    $action  = 'action="'.$this->location.'" ';
    $name    = 'id="'.$this->name.'" ';
    $class = ( $form_class == NULL ) ? '' : 'class="'.$form_class.'" ';
 
    echo '<form '.$name.$action.$method.$enctype.$class.'>'."\n";
  }

  /**
   * Display the form footer. See {@link input_form::display_start} for more.
   */
  function display_buttons($button_class = NULL) 
  {
    // Only append type to class if a class is defined
    if ( $button_class != NULL )
      echo '<span class="'.$button_class.'">';

    if ( $this->submit )
    {
      $name  = 'name="'.$this->name.'_submit" ';
      echo '<input type="submit" '.$name.'value="'.$this->submit.'" />';
    }
    foreach ( $this->other_submit as $subname => $subcaption )
    {
      $name  = 'name="'.$this->name.'_'.$subname.'" ';
      echo '<input type="submit" '.$name.'value="'.$subcaption.'" />';
    }

    echo '&nbsp;&nbsp;';
    if ( $this->reset )
    {
      $name  = 'name="'.$this->name.'_reset" ';
      echo '<input type="reset" '.$name.'value="'.$this->reset.'" />';
    }
    
    // Cancel button has a generic name - pressing cancel on any form
    // would apply to all forms on the page... 
    if ( $this->cancel )
    {
      $name  = 'name="input_form_cancel" ';
      echo '<input type="submit" '.$name.'value="'.FORM_DEFAULT_CANCEL.'" />';
    }

    // Only append type to class if a class is defined
    if ( $button_class != NULL )
        echo '</span>'."\n";
  }

  function display_end()
  {
    echo '</form>'."\n";
  }
}

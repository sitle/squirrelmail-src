<?php
/*
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * $Id$
 */

/**
 * This templating system obtained and modified from
 * http://www.massassi.com/php/articles/template_engines/
 */
class Template
{
    var $vars = array();               // Array containing template variables
    var $template_dir = 'templates';   // Directory where templates are located.
    var $file;

    /**
     * Constructor
     */
    function Template($file = NULL) 
    {
      $this->file = $file;
    }

    function &factory($file = NULL) {
        return new Template($file);
    }

    /**
     * Set a template variable.
     *
     * @param array/string $tpl_var  name of variable
     *                               or array of name/value pairs
     * @param mixed        $value    element containing value
     */
    function assign($tpl_var, $value = null) 
    {
      if (is_array($tpl_var))
      {
        foreach ($tpl_var as $key => $val) 
        {
          if ($key != '')
              $this->vars[$key] = $val;
        }
      }
      elseif ( is_object($value) && get_class($value) == get_class($this) )
      {
        if ( isset($this->vars['CURRENT_OBJECT']) )
          $value->assign_by_ref('CURRENT_OBJECT', $this->vars['CURRENT_OBJECT']);
        if ( isset($this->vars['CURRENT_USER']) )
          $value->assign_by_ref('CURRENT_USER', $this->vars['CURRENT_USER']);
        if ( isset($this->vars['FOOWD_OBJECT']) )
          $value->assign_by_ref('FOOWD_OBJECT', $this->vars['FOOWD_OBJECT']);

        $this->vars[$tpl_var] = $value->fetch();
      }
      elseif ($tpl_var != '')
          $this->vars[$tpl_var] = $value;
    }

    /**
     * Set a template variable by reference.
     *
     * @param string $tpl_var the template variable name
     * @param mixed  $value   the referenced value to assign
     */
    function assign_by_ref($tpl_var, &$value) 
    {
      if (is_array($tpl_var))
      {
        foreach ($tpl_var as $key => $val) 
        {
          if ($key != '')
              $this->vars[$key] &= $val;
        }
      }
      elseif ( is_object($value) && get_class($value) == get_class($this) )
      {
        if ( isset($this->vars['CURRENT_OBJECT']) )
          $value->assign_by_ref('CURRENT_OBJECT', $this->vars['CURRENT_OBJECT']);
        if ( isset($this->vars['CURRENT_USER']) )
          $value->assign_by_ref('CURRENT_USER', $this->vars['CURRENT_USER']);
        if ( isset($this->vars['FOOWD_OBJECT']) )
          $value->assign_by_ref('FOOWD_OBJECT', $this->vars['FOOWD_OBJECT']);

        $this->vars[$tpl_var] &= $value->fetch();
      }
      elseif ($tpl_var != '')
        $this->vars[$tpl_var] =& $value;
    }

    /**
     * executes & displays the template results
     *
     * @param string $file  the template file name
     */
    function display($file = NULL)
    {
      $this->fetch($file, true);
    }


    /**
     * Fetch and display the template file
     *
     * @param string  $file       the template file name
     * @param boolean $display    display or return the template
     */
    function fetch($file = NULL, $display = false) 
    {
      $file = ($file == null) ? $this->file : $file;
      if ( $file == null )
        trigger_error('Template file not specified.', E_USER_ERROR);

      $filename = $this->template_dir . $file;

      $template =& $this->vars;           // export var array into local namespace
      if ( $display )
        include($filename);               // Include the file
      else
      {                                          
        ob_start();                       // Start output buffering
        include($filename);               // Include the file
        $contents = ob_get_contents();    // Get the contents of the buffer
        ob_end_clean();                   // End buffering and discard
        return $contents;                 // Return the contents
      }
      return true;
    }
}

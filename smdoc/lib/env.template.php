<?php
/*
Copyright 2003, Paul James

This file is part of the Framework for Object Orientated Web Development (Foowd).

This file contains some methods from the Smarty templating engine version
2.5.0 by Monte Ohrt <monte@ispi.net> and Andrei Zmievski <andrei@php.net>.

Foowd is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

Foowd is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Foowd; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/**
 * The Foowd template implementation.
 *
 * @package Foowd 
 */

/**
 * The Foowd template class.
 *
 * Basic template class for capturing values and pluging them into a template.
 * This class uses a similar API to Smarty.
 *
 * @author Paul James
 * @author Monte Ohrt <monte@ispi.net>
 * @author Andrei Zmievski <andrei@php.net>
 * @package Foowd
 */
class foowd_template 
{
  /**
   * The templates values array
   *
   * @var array
   */
  var $values = array();

  /**
   * The template directory to use
   *
   * @var string
   */
  var $template_dir = 'templates\default';

  /**
   * Assigns values to template variables
   *
   * @param array|string $tpl_var the template variable name(s)
   * @param mixed $value the value to assign
   */
  function assign($tpl_var, $value = NULL) {
    if (is_array($tpl_var)){
      foreach ($tpl_var as $key => $val) {
        if ($key != '') {
          $this->values[$key] = $val;
        }
      }
    } else {
      if ($tpl_var != '') {
        $this->values[$tpl_var] = $value;
      }
    }
  }

  /**
   * Assigns values to template variables by reference
   *
   * @param string $tpl_var the template variable name
   * @param mixed $value the referenced value to assign
   */
  function assign_by_ref($tpl_var, &$value) {
    if ($tpl_var != '') {
      $this->values[$tpl_var] = &$value;
    }
  }

  /**
   * Appends values to template variables
   *
   * @param array|string $tpl_var the template variable name(s)
   * @param mixed $value the value to append
   */
  function append($tpl_var, $value = NULL, $merge = FALSE) {
    if (is_array($tpl_var)) {
      foreach ($tpl_var as $_key => $_val) {
        if ($_key != '') {
          if(isset($this->values[$_key]) && !@is_array($this->values[$_key])) {
            settype($this->values[$_key],'array');
          }
          if($merge && is_array($_val)) {
            foreach($_val as $_mkey => $_mval) {
              $this->values[$_key][$_mkey] = $_mval;
            }
          } else {
            $this->values[$_key][] = $_val;
          }
        }
      }
    } else {
      if ($tpl_var != '' && isset($value)) {
        if(isset($this->values[$tpl_var]) && !@is_array($this->values[$tpl_var])) {
          settype($this->values[$tpl_var],'array');
        }
        if($merge && is_array($value)) {
          foreach($value as $_mkey => $_mval) {
            $this->values[$tpl_var][$_mkey] = $_mval;
          }
        } else {
          $this->values[$tpl_var][] = $value;
        }
      }
    }
  }

  /**
   * Appends values to template variables by reference
   *
   * @param string $tpl_var the template variable name
   * @param mixed $value the referenced value to append
   */
  function append_by_ref($tpl_var, &$value, $merge = FALSE) {
    if ($tpl_var != '' && isset($value)) {
      if(!@is_array($this->values[$tpl_var])) {
        settype($this->values[$tpl_var],'array');
      }
      if ($merge && is_array($value)) {
        foreach($value as $_key => $_val) {
          $this->values[$tpl_var][$_key] = &$value[$_key];
        }
      } else {
        $this->values[$tpl_var][] = &$value;
      }
    }
  }

  /**
   * Display the template
   *
   * @param string $file The template file to use
   */
  function display($file) {
    $t = &$this->values; // place values array directly in scope
    include($this->template_dir.$file);
  }

  /**
   * Return the results of applying a template.
   *
   * @param string $file The template file to use
   * @return string A string of the results
   */
  function fetch($file) {
    ob_start();
    $t = &$this->values; // place values array directly in scope
    include($this->template_dir.$file);
    $contents = ob_get_contents();
    ob_end_clean();
    return $contents;
  }

}

?>

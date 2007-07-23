<?php

/**
 * skeleton.class.php
 *
 * @copyright &copy; 1999-2007 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id$
 * @package squirrelmail
 */

define('SM_CONF_BOOL', 1);
define('SM_CONF_STRING', 2);
define('SM_CONF_INTEGER', 3);
define('SM_CONF_ARRAY', 4);
define('SM_CONF_TEXT', 5);
define('SM_CONF_ENUM', 6);
define('SM_CONF_ARRAY_ENUM', 7);
define('SM_CONF_KEYED_ENUM', 8);

define('SM_CONF_ARRAY_SIMPLE', 0);
define('SM_CONF_ARRAY_KEYS', 1);
define('SM_CONF_ARRAY_REINDEX', 2);

class SMConfigSkeleton 
{
  var $sm_sections;  
  var $sm_config_vars;
  var $sm_config_sections;
  var $sm_config_desc;
  var $sm_config_type;

  function get_section($name = null)
  {
    if(is_null($name))
    {
      return $this->sm_sections;
    }
    return $this->sm_sections[$name];
  }

  /*
  * Register a new section and its description
  */
  function register_section($name, $title)
  {
    $this->sm_sections[$name] = array(
       'title' => _($title), 
       'sub' => array(),
       'vars' => array(),
       'desc' => ''
    );
       
    if($pos = strrpos($name,'.'))
    {
      $this->sm_sections[substr($name,0,$pos)]['sub'][] = $name;
    } 
  }

  /*
  * Register a new configuration variable
  * Multiple variables can be registered at once by passing an array.
  */
  function register_variable($variable_name, $value=null)
  {
    $this->sm_config_vars[$variable_name] = $value;  
  }
  
  function add_section($variable_name, $section)
  {
    $this->sm_sections[$section]['vars'][] = $variable_name;
  }
  
  function remove_section($variable_name, $section)
  {
    $key = array_search($variable_name, $this->sm_sections[$section]['vars']);
    unset($this->sm_sections[$section]['vars'][$key]);
  }
  
  function add_type($variable_name, $type)
  {
    $this->sm_config_type[$variable_name] = explode(',', $type, 2);
  }
  
  function add_desc($variable_name, $desc)
  {
    if(substr($variable_name, 0, 4) == "sec.")
    { 
      $section = substr($variable_name, 4); 
      $this->sm_sections[$section]['desc'] = _($desc);
    }
    else
    {
      $this->sm_config_desc[$variable_name] = _($desc);
    }
  }
  
  function get_desc($variable_name)
  {
    return $this->sm_config_desc[$variable_name];
  }

  function get_type($variable_name)
  {
    return $this->sm_config_type[$variable_name];
  }

  function V($name)
  {
    return $this->sm_config_vars[$name];
  }
}




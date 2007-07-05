<?php

/**
 * config_class.php
 *
 * This is an abstract class to store configuration.
 * Different backends can be used to read configuration and preferences.
 * Supports extern configuration (for plugins)  
 *
 * TODO:
 * - Work in progress (!)
 * - Add phpDoc comments 
 *
 * @copyright &copy; 1999-2007 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id$
 * @package squirrelmail
 */

#
# How it works / various thoughts :
#   SQMConfig is but an abstract accessor / mutator class
#   SQMConfigFile is the filesystem backend to do the job
#
#   * The same class is used for configuration and user preferences
#   * Each variable can have "meta-data" (decription, type, categories)
#   * Most of the time, meta-data are not loaded to save memory and time
#   * Arrays are supported (including two-dimension associative arrays)
#   * Each variable has an optional section and subsection
#   * Plugins can register their own configuration variables and user params
#   * Script config and User option pages will be dynamically generated
#     (using sections, subsections and types / patterns)
#   * Variables can be made "private" (e.g forced user preferences)
#

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

class SQMConfig 
{
  var $sqm_sections;  
  var $sqm_config_vars;
  var $sqm_config_sections;
  var $sqm_config_desc;
  var $sqm_config_type;

  function SQMConfig()
  {
    $this->register_section('private', "");  
  }

  function get_section($name = null)
  {
    if(is_null($name))
    {
      return $this->sqm_sections;
    }
    return $this->sqm_sections[$name];
  }

  /*
  * Register a new section and its description
  */
  function register_section($name, $title)
  {
    $this->sqm_sections[$name] = array(
       'title' => _($title), 
       'sub' => array(),
       'vars' => array(),
       'desc' => ''
    );
       
    if($pos = strrpos($name,'.'))
    {
      $this->sqm_sections[substr($name,0,$pos)]['sub'][] = $name;
    } 
  }

  /*
  * Register a new configuration variable
  * Multiple variables can be registered at once by passing an array.
  */
  function register_variable($variable_name, $value=null)
  {
    $this->sqm_config_vars[$variable_name] = $value;  
  }
  
  function add_section($variable_name, $section)
  {
    $this->sqm_sections[$section]['vars'][] = $variable_name;
  }
  
  function remove_section($variable_name, $section)
  {
    $key = array_search($variable_name, $this->sqm_sections[$section]['vars']);
    unset($this->sqm_sections[$section]['vars'][$key]);
  }
  
  function add_type($variable_name, $type)
  {
    $this->sqm_config_type[$variable_name] = explode(',', $type, 2);
  }
  
  function add_desc($variable_name, $desc)
  {
    if(substr($variable_name, 0, 4) == "sec.")
    { 
      $section = substr($variable_name, 4); 
      $this->sqm_sections[$section]['desc'] = _($desc);
    }
    else
    {
      $this->sqm_config_desc[$variable_name] = _($desc);
    }
  }
  
  function get_desc($variable_name)
  {
    return $this->sqm_config_desc[$variable_name];
  }

  function get_type($variable_name)
  {
    return $this->sqm_config_type[$variable_name];
  }

  function V($name)
  {
    return $this->sqm_config_vars[$name];
  }
}

/*
* Filesystem backend for configuration/preference data
*/
class SQMConfigFile extends SQMConfig
{
  ## TODO : clean this function a little bit
  ## Reuse current code to compute SM_PATH
  function SQMConfigFile($confFile, $sections = false) 
  {
###########################################################################
define('SM_PATH', realpath(dirname(__FILE__)."/../"));
##########################################################################

    $raw_conf = parse_ini_file($confFile, true);
    foreach($raw_conf as $section => $variables)
    {
      if($section == 'types')
      { 
        foreach($variables as $name => $value)
        {
          $this->add_type($name,$value);
          $value = explode(",",$value);
          if($value[0] == SM_CONF_ARRAY)
          {
            if($value[1] == SM_CONF_ARRAY_REINDEX)
            {
              $newval = array();
              foreach($this->sqm_config_vars[$name] as $confvalue)
              {
                list($key, $rest) = explode(',', $confvalue, 2);
                $newval[$key] = $rest;
              }
              $this->sqm_config_vars[$name] = $newval;
            }
            elseif($value[1] == SM_CONF_ARRAY_KEYS)
            {
              $newval = array();
              foreach($this->sqm_config_vars[$name] as $j => $confvalue)
              {
                $slice = explode(',', $confvalue, count($value)-2);
                foreach($slice as $i=>$subdata)
                {
                  $newval[$j][$value[$i+2]] = $subdata;
                }
              }
              $this->sqm_config_vars[$name] = $newval;              
            }
          }
        }
      }
      elseif($section == 'sections')
      {
        foreach($variables as $name => $title)
        {
          $this->register_section($name, $title);
        }      
      }
      elseif($section == 'descriptions')
      {
        foreach($variables as $name => $desc)
        {
          $this->add_desc($name, $desc);
        }      
      }
      else
      {
        foreach($variables as $name => $value)
        {
          $this->register_variable($name, $value);
          if($sections){ $this->add_section($name, $section); }
        }
      }  
      unset($raw_conf[$section],$newval);  
    }    
  }
}

## TODO
class SQMConfigDB extends SQMConfig
{

}

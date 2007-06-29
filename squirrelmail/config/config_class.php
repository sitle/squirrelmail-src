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
#   * Each variable can have its "meta-data" (decription, type)
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

  /*
  * Register a new section and its description
  */
  function register_section($name, $section, $parent=null)
  {
    if(is_null($parent))
    {
      $this->sqm_sections[$name][$name] = _($section);
    }
    else
    {
      $this->sqm_sections[$parent][$name] = _($section); 
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
    $this->sqm_config_sections[$variable_name][] = $section;
  }
  
  function add_type($variable_name, $type)
  {
    $this->sqm_config_type[$variable_name] = explode(',', $type, 2);
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
/**
 * calculate SM_PATH and calculate the base_uri
 * assumptions made: init.php is only called from plugins or from the src dir.
 * files in the plugin directory may not be part of a subdirectory called "src"
 */
if (isset($_SERVER['SCRIPT_NAME'])) {
    $a = explode('/',$_SERVER['SCRIPT_NAME']);
} elseif (isset($HTTP_SERVER_VARS['SCRIPT_NAME'])) {
    $a = explode('/',$HTTP_SERVER_VARS['SCRIPT_NAME']);
} else {
    $error = 'Unable to detect script environment. '
	.'Please test your PHP settings and send PHP core config, $_SERVER '
	.'and $HTTP_SERVER_VARS to SquirrelMail developers.';
    die($error);
}
$sSM_PATH = '';
for($i = count($a) -2;$i > -1; --$i) {
    $sSM_PATH .= '../';
    if ($a[$i] === 'src' || $a[$i] === 'plugins') {
        break;
    }
}

$base_uri = implode('/',array_slice($a,0,$i)). '/';

define('SM_PATH',$sSM_PATH);
define('SM_BASE_URI', $base_uri);
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
      else
      {
        foreach($variables as $name => $value)
        {
          $this->register_variable($name, $value);
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


$conf = new SQMConfigFile("default_config.php");
$conf->SQMConfigFile("meta_config.php");

var_dump($conf);



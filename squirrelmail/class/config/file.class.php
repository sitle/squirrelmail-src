<?php

/**
 * file.class.php
 *
 * @copyright &copy; 1999-2007 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id$
 * @package squirrelmail
 */


require_once SM_PATH.'class/config/skeleton.class.php';

class SMConfigFile extends SMConfigSkeleton
{  
  function SMConfigFile($confFile=null, $sections=false) 
  {
    $raw_conf = @parse_ini_file($confFile, true);
    if(!is_array($raw_conf)) return;
    
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
              foreach($this->sm_config_vars[$name] as $confvalue)
              {
                list($key, $rest) = explode(',', $confvalue, 2);
                $newval[$key] = $rest;
              }
              $this->sm_config_vars[$name] = $newval;
            }
            elseif($value[1] == SM_CONF_ARRAY_KEYS)
            {
              $newval = array();
              foreach($this->sm_config_vars[$name] as $j => $confvalue)
              {
                $slice = explode(',', $confvalue, count($value)-2);
                foreach($slice as $i=>$subdata)
                {
                  $newval[$j][$value[$i+2]] = $subdata;
                }
              }
              $this->sm_config_vars[$name] = $newval;              
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

function parse_config_file($files, $load_metas)
{
  $objects = array();
  
  foreach($files as $file)
  {
    list($meta, $defaults, $config) = explode(",", $file);    
    $obj = new SMConfigFile();
    
    if($load_metas) $obj->SMConfigFile(SM_PATH."config/$meta", $load_metas);
    $obj->SMConfigFile(SM_PATH."config/$defaults", $load_metas);
    $obj->SMConfigFile(SM_PATH."config/$config", $load_metas);
    $objects[] = $obj;
  }

  return $objects;
}

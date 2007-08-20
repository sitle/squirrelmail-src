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
  function SMConfigFile($confFile=null, $metalevel = SM_CONF_DEFAULTS) 
  {
    
    if($metalevel == SM_CONF_CUSTOM)
    {
      $raw_conf = @parse_ini_file($confFile, false);
      if(!is_array($raw_conf)) return;
			$raw_conf = array("none" => $raw_conf);
    }
    else
    {
      $raw_conf = @parse_ini_file($confFile, true);
      if(!is_array($raw_conf)) return;
    }
    
    foreach($raw_conf as $section => $variables)
    {
      if($section == 'types')
      { 
        $this->sm_config_type = array_merge($this->sm_config_type, $variables);
      }
      elseif($section == 'sections')
      {
        foreach($variables as $name => $title)
        {
          $this->register_section($name, $title);
        }      
      }
      elseif($metalevel == SM_CONF_METAS)
      {
        foreach($variables as $name => $value)
        {
          $this->add_meta($name, $section, $value);
        }      
      }
      elseif($metalevel == SM_CONF_CUSTOM)
      {
        foreach($variables as $name => $value)
        {
          if(is_array($value))
          {
           $this->SetVar($name, array_merge(is_array($this->V($name)) ? $this->V($name) : array(), $value));
          }					 
          else
          {
           $this->SetVar($name, $value);
          }
        }
      }
      else
      {
        foreach($variables as $name => $value)
        {
          $this->register_variable($name, $value);
          if($metalevel==SM_CONF_DEFAULTS_SECTIONS){ $this->add_section($name, $section); }
        }
      }  
      unset($raw_conf[$section],$newval);  
    }    
  }
  
  function ApplyTypes()
  {
    $types = $this->sm_config_type;
    $this->sm_config_type = array();
    
    foreach($types as $name => $value)
    {
      $this->add_type($name,$value);
      $value = explode(",",$value);
      
      if($value[0] == SM_CONF_PATH)
      {
        $this->sm_config_vars[$name] = str_replace('SM_PATH', SM_PATH, $this->sm_config_vars[$name]);
      }
      
      if($value[0] == SM_CONF_ARRAY)
      {
        if(!is_array($this->sm_config_vars[$name]))
        {
          $this->sm_config_vars[$name] = array();
        }
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
  
  // This backend generates a new configuration file
  function Save($conf)
  {
    $file = '; This configuration file was created '.date('r').' <?php die(); ?>'."\n\n";
    foreach($this->sm_config_vars as $name => $value)
    {
      if($value == $conf->GetVar($name)) continue;
			$type = $conf->get_type($name);
			
      switch($type[0])
      {
        case SM_CONF_BOOL:
          $file .= "$name = ".($value ? 'yes' : 'no')."\n";
          break;
        case SM_CONF_PATH:
          if($value == str_replace(SM_PATH, 'SM_PATH', $conf->GetVar($name)))
					 break;
        
        case SM_CONF_STRING:
        case SM_CONF_ENUM:
        case SM_CONF_KEYED_ENUM:
        case SM_CONF_ARRAY_ENUM:
          $value = str_replace('"','',$value);
          $file .= "$name = \"$value\"\n";
          break;
        case SM_CONF_INTEGER:
				  $value = (int)$value;
					$file .= "$name = $value\n";
					break;
				case SM_CONF_ARRAY:
				  list($array_type, $params) = explode(',', $type[1], 2);

  				switch($array_type)
          {
           case SM_CONF_ARRAY_SIMPLE:
            foreach($value as $val)
            {
              if(!in_array($val, $conf->GetVar($name)))
              {
                $file .= $name."[] = \"$val\"\n";
              }
            }
           break;
           case SM_CONF_ARRAY_KEYS:
            foreach($value as $val)
            {
              if(!in_array($val, $conf->GetVar($name)))
              {
                $file .= $name.'[] = "'.join(',',$val)."\"\n";
              }
            }
           break;
         }				
			   break;
      }
    }
    
    header('Content-type: application/x-php');
    header('Content-Disposition: attachment; filename="config.php"');
    header('Content-Length: '. strlen($file));
    
    die($file);
  }
}

function parse_config_file($files, $load_metas, $def_only)
{
  $objects = array();
  
  foreach($files as $file)
  {
    list($meta, $defaults, $config) = explode(",", $file);    
    $obj = new SMConfigFile();
    
    if($load_metas) $obj->SMConfigFile(SM_PATH."config/$meta", SM_CONF_METAS);
    $obj->SMConfigFile(SM_PATH."config/$defaults", SM_CONF_DEFAULTS + $load_metas);
    if(!$def_only) $obj->SMConfigFile(SM_PATH."config/$config", SM_CONF_CUSTOM);
    $obj->ApplyTypes();
    $objects[] = $obj;
  }

  return $objects;
}

// Must add support for more config files
function configure_backend_file()
{
  return 'config_file[] = "meta_config.php,default_config.php,config.php"'."\n";
}

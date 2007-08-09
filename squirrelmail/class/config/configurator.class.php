<?php

/**
 * configurator.class.php
 *
 * @copyright &copy; 1999-2007 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id$
 * @package squirrelmail
 */


class SMConfigurator
{
  var $admin_password;
  var $squirrelmail_default_language;
  var $default_charset;
  var $sources;
  
  function SMConfigurator($load_meta = false)
  {
    $toplevel = parse_ini_file(SM_PATH.'config/toplevel_config.php', false);
    
    $this->admin_password = $toplevel['admin_password'];
    $this->squirrelmail_default_language = $toplevel['squirrelmail_default_language'];
    $this->default_charset = $toplevel['default_charset'];
    
    if(!file_exists(SM_PATH.'class/config/'.$toplevel['config_backend'].'.class.php'))
      die('The backend configured in your toplevel config file doesn\'t exist !');
    require_once SM_PATH.'class/config/'.$toplevel['config_backend'].'.class.php';
    
    $this->sources = call_user_func("parse_config_".$toplevel['config_backend'], $toplevel['config_file'], $load_meta);
  }
  
  function GetVar($name)
  {
   foreach($this->sources as $s)
   {
    $value = $s->V($name);
   }
   return $value;
  }
  
  function get_section($name = null)
  {
    if(is_null($name))
    {
      $sec = array();
      foreach($this->sources as $s)
      {
        $sec = array_merge($sec, $s->get_section($name));
      }
      return $sec;
    }
    foreach($this->sources as $s)
    {
      $sec = $s->get_section($name);
    }
    return $sec;
  }
  
}

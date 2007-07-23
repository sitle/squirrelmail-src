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
  var $sm_backends;
  var $admin_password;
  var $squirrelmail_default_language;
  var $default_charset;

  function SMConfigurator($load_meta = false)
  {
    $toplevel = parse_ini_file(SM_PATH.'config/toplevel.php', true);
    
    foreach($toplevel as $cat => $vars)
    {
      if($cat == "admin")
      {
        $this->admin_password = $vars['admin_password'];
        $this->default_charset = $vars['default_charset'];
        $this->squirrelmail_default_language = $vars['squirrelmail_default_language'];             
      }
      else
      {
        if(file_exists(SM_PATH."class/config/$cat.class.php"))
        {
          require_once SM_PATH."class/config/$cat.class.php";
          $class = "SMConfig$cat";
          $this->sm_backends[] = new $class($vars, $load_meta);  
        }
      }    
    }  
  }
}

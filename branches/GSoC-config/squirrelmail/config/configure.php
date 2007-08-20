#!/usr/bin/env php
<?php

/**
 * configure.php
 *
 * Command-line tool to configure SquirrelMail using the
 * new configuration engine.
 *
 * @copyright &copy; 1999-2007 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id$
 * @package squirrelmail
 */

define('SM_PATH', realpath(dirname(__FILE__).'/..').'/');

// Must be run from the command line
// Should be compatible with PHP 4.2
if(getenv('SERVER_NAME') || php_sapi_name() != 'cli')
{
  die('This script must be run from the command line !');
}

// PHP 4.2 compatible, no effect on PHP 4.3+
if(!defined('STDIN'))
{
 define('STDIN',fopen("php://stdin","r")); 
 define('STDOUT',fopen("php://stdout","r")); 
 define('STDERR',fopen("php://stderr","r")); 
}

function read_locale()
{
	do
	{
	  fwrite(STDOUT, "Local name [en_US] : ");
	  fscanf(STDIN, "%s\n", $locale);
	  
	  if(empty($locale))
	    return "en_US";

    if(!is_dir('../locale/'.$locale))
    {
      fwrite(STDOUT, "Can't find $locale in the locale/ folder !\n\n");
      $locale = "";
    }	  
	}
	while(!$locale);
	
	return $locale;
}

function read_string($prompt, $def='')
{
  fwrite(STDOUT, "$prompt [$def] : ");
  fscanf(STDIN, "%s\n", $in);

  return (empty($in) ? $def : $in);
}

function read_password()
{
  fwrite(STDOUT, "Administrator password [] : ");
  fscanf(STDIN, "%s\n", $pass);

  return (empty($pass) ? '' : md5($pass.'squirrelmail'));
}

function read_backend($sel = 'file')
{
  $backends = array();
  $dir = opendir('../class/config');
  
  while($f = readdir($dir))
  {
    if(ereg('^([a-z]*)\.class\.php$', $f, $regs))
    {
     if($regs[1] != 'skeleton' && $regs[1] != 'configurator')
       $backends[] = $regs[1];
    }
  }
  
  foreach($backends as $i => $b)
  {
   fwrite(STDOUT, "$i. $b\n");
  }

  do
	{  
    fwrite(STDOUT, "\nSelect a backend [$sel] : ");
	  fscanf(STDIN, "%s\n", $in);
	  if(empty($in)) $in=$sel;
	}
	while(!in_array($in, $backends));

  return $in;
}

if(!file_exists('toplevel_config.php'))
{
  if(!($fp = fopen('toplevel_config.php','w')))
  {
   fwrite(STDERR, "\nThis script needs write access to the config directory.");
   fwrite(STDERR, "\nRun it again using the account used to unpack SquirrelMail's package.\n\n");
   die(1);
  }

  fwrite(STDOUT, "\nWelcome to SquirrelMail !\n\n");
  fwrite(STDOUT, "If you have installed a translation package, please enter\n");
  fwrite(STDOUT, "the name of your locale : <language_COUNTRY>. Leave blank to\n");
  fwrite(STDOUT, "default to english.\n\n");

  $locale = read_locale();

  // TODO :
  //   Load locales at this point !

  fwrite(STDOUT, "\nSquirrelMail can be configured either from the command line,\n");
  fwrite(STDOUT, "or using the web-based configuration page.\n");
  fwrite(STDOUT, "If you want to use the web interface, you need to provide an\n");
  fwrite(STDOUT, "administrator password. This password only prevents outsiders to\n");
  fwrite(STDOUT, "access your settings. If you don't provide a password, the web\n");
  fwrite(STDOUT, "interface will be disabled. \n\n");
  
  $pass = read_password();

  if($pass)
  {
    fwrite(STDOUT, "\nYou may set the charset to use in the web interface.\n\n");  
    $charset = read_string('Default charset', 'iso-8859-1');
  }
  else
  {
    $charset = 'iso-8859-1';
  }
  
  fwrite(STDOUT, "\nBy default, SquirrelMail uses a configuration file to store its settings.\n");
  fwrite(STDOUT, "If you want to select another backend to store configuration variables, please\n");
  fwrite(STDOUT, "Select it in the list below :\n\n");

  $backend = read_backend();
  
  require_once "../class/config/$backend.class.php";
  
  fwrite($fp, '; This is the top-level configuration file of SquirrelMail. <?php die(); ?>'."\n\n");
  fwrite($fp, "admin_password = \"$pass\"\n");
  fwrite($fp, "squirrelmail_default_language = \"$locale\"\n");
  fwrite($fp, "default_charset = \"$charset\"\n");
  fwrite($fp, "config_backend = \"$backend\"\n");
  fwrite($fp, call_user_func("configure_backend_$backend"));
  fclose($fp);
  
  fwrite(STDOUT, "\nCongratulation, SquirrelMail is now ready to be configured.\n");
  if($pass)
  {
    fwrite(STDOUT, "You can now browse to the admin/ folder of your SquirrelMail installation.\n");
  }
  else
  {
    fwrite(STDOUT, "Run this tool again to start command line configuration.\n");
  }
  die(0);
}

// ADD COMMAND LINE CONFIGURATION TOOL HERE

  if(!is_writable('toplevel_config.php'))
  {
   fwrite(STDERR, "\nThis script needs write access to the config directory.");
   fwrite(STDERR, "\nRun it again using the account used to unpack SquirrelMail's package.\n\n");
   die(1);
  }


fwrite(STDERR, "Command line configuration not implemented.\n");


?>

<?php
   /*
    *  motd v1.2
    *  By Ben Brillat <brillat-sqplugin@mainsheet.org>
    *  (c) 2001 GNU GPL
    *
    *  This plugin allows you to display a "Message of the Day"
    *    on the login screen.
    *  The message is either read from config/motd.php or
    *    the $motd variable from the SquirrelMail config.
    */

   function squirrelmail_plugin_init_motd() {
      global $squirrelmail_plugin_hooks;
      $squirrelmail_plugin_hooks["login_bottom"]["motd"] = "login_include";
   }

   function login_include() {

      global $motd;
      $motd_file = "../config/motd.php";

      if(file_exists($motd_file)) {
        include($motd_file);
      }
      else {
	print "<hr width=\"50%\">";
	print $motd;
      }
   }

?>

<?php
   /*
    *  SquirrelMail Clock
    *  By Luke Ehresman <luke@squirrelmail.org>
    *  (c) 2000 (GNU GPL - see ../../COPYING)
    *
    *  This plugin puts a clock at the top of the folder listing.  This is
    *  most useful, especially if your webmail server is in a different time
    *  zone than you.  This lets you see what time it is for your server, and
    *  what all your dates are relative to.
    *
    *  If you need help with this, or see improvements that can be made, please
    *  email me directly at the address above.  I definately welcome suggestions
    *  and comments.  This plugin, as is the case with all SquirrelMail plugins,
    *  is not directly supported by the developers.  Please come to me off the
    *  mailing list if you have trouble with it.
    *
    */

   include "../plugins/sqclock/options.php";

   function squirrelmail_plugin_init_sqclock() {
      global $squirrelmail_plugin_hooks;

      $squirrelmail_plugin_hooks["left_main_before"]["sqclock"] = "sqclock";
      $squirrelmail_plugin_hooks["options_display_inside"]["sqclock"] = "show_options";
      $squirrelmail_plugin_hooks["options_display_save"]["sqclock"] = "save_options";
      $squirrelmail_plugin_hooks["loading_prefs"]["sqclock"] = "load_options";
   }

   function sqclock() {
      global $color;
      global $date_format, $hour_format;
      if ($date_format != 6) {
      ?>
         <center>
         <table cellpadding=0 cellspacing=0 border=0 bgcolor=<?php echo $color[10] ?>><tr><td>
         <table width=100% cellpadding=2 cellspacing=1 border=0 bgcolor="<?php echo $color[5] ?>"><tr><td align=center>
            <tt><?php 
               if ($hour_format == 1) {
                  if ($date_format == 4)
                     $hr = "G:i:s";
                  else
                     $hr = "G:i";
               } else {  
                  if ($date_format == 4)
                     $hr = "g:i:s a";
                  else   
                     $hr = "g:i a";
               }
               
               if ($date_format == 1)
                  echo date("m/d/y ".$hr, time()); 
               else if ($date_format == 2)
                  echo date("d/m/y ".$hr, time()); 
               else if ($date_format == 4 || $date_format == 5)
                  echo date($hr, time()); 
               else   
                  echo date("D, ".$hr, time()); 
            ?></tt>
         </td></tr></table>
         </td></tr></table>
         <br>
         </center>
      <?
      }
   }
?>

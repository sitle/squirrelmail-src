<?php
   /*
    *  Focus Change Plugin
    *  By Luke Ehresman <luke@squirrelmail.org>
    *  (c) 2000 (GNU GPL - see ../../COPYING)
    *
    *  This plugin uses JavaScript to change the focus of most of the forms in
    *  SquirrelMail.  It is pretty smart, especially on the compose form.  It
    *  knows where you probably want the focus.
    *
    *  If you need help with this, or see improvements that can be made, please
    *  email me directly at the address above.  I definately welcome suggestions
    *  and comments.  This plugin, as is the case with all SquirrelMail plugins,
    *  is not directly supported by the developers.  Please come to me off the
    *  mailing list if you have trouble with it.
    *
    *  View the README document for information on installing this.  Also view
    *  plugins/README.plugins for more information.
    *
    */

   function squirrelmail_plugin_init_new_window() {
      global $squirrelmail_plugin_hooks;

      $squirrelmail_plugin_hooks["subject_link"]["new_window"] = "add_target";
   }

   function add_target () {
      echo " target=\"_blank\"";
   }
?>

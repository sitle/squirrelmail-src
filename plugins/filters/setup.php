<?php
   /*
    *  Message and Spam Filter Plugin 
    *  By Luke Ehresman <luke@squirrelmail.org>
    *     Tyler Akins <tyler@boas.anthro.mnsu.edu>  
    *  (c) 2000 (GNU GPL - see ../../COPYING)
    *
    *  This plugin filters your inbox into different folders based upon given
    *  criteria.  It is most useful for people who are subscibed to mailing lists
    *  to help organize their messages.  The argument stands that filtering is
    *  not the place of the client, which is why this has been made a plugin for
    *  SquirrelMail.  You may be better off using products such as Sieve or
    *  Procmail to do your filtering so it happens even when SquirrelMail isn't
    *  running.
    *
    *  If you need help with this, or see improvements that can be made, please
    *  email me directly at the address above.  I definately welcome suggestions
    *  and comments.  This plugin, as is the case with all SquirrelMail plugins,
    *  is not directly supported by the developers.  Please come to me off the
    *  mailing list if you have trouble with it.
    *
    *  Also view plugins/README.plugins for more information.
    *
    */

   include "../plugins/filters/filters.php";

   function squirrelmail_plugin_init_filters() {
      global $squirrelmail_plugin_hooks;
      global $mailbox, $imap_stream, $imapConnection;

      $squirrelmail_plugin_hooks["left_main_before"]["filters"] = "start_filters";
      if ($mailbox == "INBOX")
         $squirrelmail_plugin_hooks["right_main_after_header"]["filters"] = "start_filters";
      $squirrelmail_plugin_hooks["options_link_and_description"]["filters"] = "show_option_link";
   }

   function show_option_link() {
      global $color
      ?>
      <table width=50% cellpadding=3 cellspacing=0 border=0 align=center>
         <tr>
            <td bgcolor="<? echo $color[9] ?>">
               <a href="../plugins/filters/options.php">Message Filters</a>
            </td>
         </tr>
         <tr>
            <td bgcolor="<? echo $color[0] ?>">
               Filtering enables messages with different criteria to be automatically
               filtered into different folders for easier organization.
            </td>
         </tr>
      </table>
      <?php
   }
?>

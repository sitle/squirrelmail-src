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

   function squirrelmail_plugin_init_focus_change() {
      global $squirrelmail_plugin_hooks;

      $squirrelmail_plugin_hooks["search_after_form"]["focus_change"] = "focus_search";
      $squirrelmail_plugin_hooks["login_bottom"]["focus_change"] = "focus_login";
      $squirrelmail_plugin_hooks["options_folders_bottom"]["focus_change"] = "focus_options_folders";
      $squirrelmail_plugin_hooks["options_highlight_bottom"]["focus_change"] = "focus_options_highlight";
      $squirrelmail_plugin_hooks["options_display_bottom"]["focus_change"] = "focus_options_display";
      $squirrelmail_plugin_hooks["options_personal_bottom"]["focus_change"] = "focus_options_personal";
      $squirrelmail_plugin_hooks["compose_bottom"]["focus_change"] = "focus_compose";
      $squirrelmail_plugin_hooks["folders_bottom"]["focus_change"] = "focus_folders";
      $squirrelmail_plugin_hooks["addrbook_html_search_below"]["focus_change"] = "focus_abook";
   }

   function focus_login() {
		global $newUsername;
		if ($newUsername)
      	echo "<script language=javascript>document.f.".$newUsername.".focus()</script>";
		else
      	echo "<script language=javascript>document.f.login_username.focus()</script>";
   }
   
   function focus_options_folders() {
      echo "<script language=javascript>document.f.folderprefix.focus()</script>";
   }
   
   function focus_options_highlight() {
      echo "<script language=javascript>document.f.identname.focus()</script>";
   }
   
   function focus_options_display() {
      echo "<script language=javascript>document.f.shownum.focus()</script>";
   }
   
   function focus_options_personal() {
      echo "<script language=javascript>document.f.full_name.focus()</script>";
   }
   
   function focus_search() {
      echo "<script language=javascript>document.s.what.focus()</script>";
   }
   
   function focus_compose() {
      global $send_to, $subject, $reply_subj, $forward_subj;
      if ($send_to && (!$subject && !$reply_subj && !$forward_subj))
         echo "<script language=javascript>document.compose.subject.focus()</script>";
      else if (!$send_to)
         echo "<script language=javascript>document.compose.send_to.focus()</script>";
      else if ($send_to && ($subject || $reply_subj || $forward_subj))
         echo "<script language=javascript>document.compose.body.focus()</script>";
      else   
         echo "<script language=javascript>document.compose.send_to.focus()</script>";
   }
   
   function focus_folders() {
      echo "<script language=javascript>document.cf.folder_name.focus()</script>";
   }
   
   function focus_abook() {
      echo "<script language=javascript>document.f.addrquery.focus()</script>";
   }
?>

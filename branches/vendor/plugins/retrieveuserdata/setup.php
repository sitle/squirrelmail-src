<?php
   /*
    *  SquirrelMail Retrieve User Data Plugin 0.4
    *  By Ralf Kraudelt <kraude@wiwi.uni-rostock.de>
    *
    *  This plugin retrieves the user's name ($full_name in the user's 
    *  preferences file) and email address ($email_address) from an external
    *  source. The access of a LDAP server is implemented, but you can
    *  add other sources by writing your own access function (see ldap.php).
    *
    *  There are some options placed in config.php:
    *  $retreive_data_from - file containing retrieve_data() function,
    *                        see ldap.h for an example
    *  $force - if you don't want your users to change their data, give it
    *           a value of 1
    *  $retrieve_on_every_login - the user's data are retrieved on every
    *                             login, set it to 1
    *  Additionally the configuration options for the data source are
    *  placed in config.php.
    *
    *  This plugin adds 3 values to the user's preferences file:
    *  $got_external_data - is set after a successful retrieval
    *  $full_name_backup - if you don't want your users to change their
    *                      full name, this one is used to get the user's name
    *                      instead of accessing your external source
    *  $email_address_backup - the same for the email address
    *   
    *  This plugin is written to minimize the access of your external data
    *  source. It's only used, if it never successfully retrieved the user's 
    *  data before ($got_external_data), and if you don't force the retrieval
    *  ($force).
    *
    *  If you need help with this, or see improvements that can be made, please
    *  email me directly at the address above.  I definately welcome suggestions
    *  and comments.  This plugin, as is the case with all SquirrelMail plugins,
    *  is not directly supported by the developers.  Please come to me off the
    *  mailing list if you have trouble with it.
    *
    *  5.12.2000  fixed missing OneTimePadDecrypt function by adding its
    *             include file
    *  5.10.2000  changed hook from login_veryfied to loading_prefs
    *             to work for users without a preferences file, v0.2
    *  14.1.2001  there is an error in the error log, when a user isn't 
    *             logged in (reload of signout.php), should be fixed
    *  6.2.2001   some code cleanup, fixed signout bug, v0.4
    */

   /*
    *  place plugin functions in SM
    */
   function squirrelmail_plugin_init_retrieveuserdata() {
      global $squirrelmail_plugin_hooks;

      $squirrelmail_plugin_hooks["loading_prefs"]["retrieveuserdata"] = "check_userdata";
      $squirrelmail_plugin_hooks["options_personal_save"]["retrieveuserdata"] = "force_userdata";
   }

   /*
    *  after successful login; check, if we ever retrieved the user's data or
    *  we sould always retrieve the user's data; catch them, if required 
    */
   function check_userdata() {
      global $data_dir, $username;

      include ("../plugins/retrieveuserdata/config.php");

      if (isset($username)) {
         $got_external_userdata = getPref($data_dir, $username, "got_external_userdata");

         // write initial value 
         if ($got_external_userdata != 1 && $got_external_userdata != 0) {
           $got_external_userdata = 0;
           setPref($data_dir, $username, "got_external_userdata", 0);
         }

         // avoid unnecessary access of external data source
         if (($got_external_userdata == 0) || ($retrieve_on_every_login == 1)) {
            retrieve_external_userdata();
         } 
      } 
   }
   
   /*
    *  after change of user's personal options; check, if we ever retrieved
    *  the user's data; if not, catch them; if we ever did, use the backed up
    *  data to fill in $full_name and $mail_address
    */
   function force_userdata() {
      global $data_dir, $username;

      include("../plugins/retrieveuserdata/config.php");

      if ($force != 0 && isset($username)) {
         $got_external_userdata = getPref($data_dir, $username, "got_external_userdata");
         if ($got_external_userdata == 0) {
            // if we have never got the user data before, ask someone
            retrieve_external_userdata();
         } else {
            // use backed up user data to avoid external data source access
            $common_name = getPref($data_dir, $username, "full_name_backup");
            $mail_address = getPref($data_dir, $username, "email_address_backup");
            set_userdata($common_name, $mail_address);
         }
      }
   }

   /*
    *  write user data to preferences file
    */
   function set_userdata($common_name, $mail_address) {
      global $data_dir, $username;

      setPref($data_dir, $username, "full_name", $common_name);
      setPref($data_dir, $username, "email_address", $mail_address);
   }

   /*
    *  write user data backup to preferences file
    */
   function set_userdata_backup($common_name, $mail_address) {
      global $data_dir, $username;

      setPref($data_dir, $username, "full_name_backup", $common_name);
      setPref($data_dir, $username, "email_address_backup", $mail_address);
   }

   /*
    *  catch user data from external source and write them to the 
    *  preferences file
    */
   function retrieve_external_userdata() {
     global $data_dir, $username, $password;

     include("../plugins/retrieveuserdata/config.php");
     include ("../plugins/retrieveuserdata/$retrieve_data_from");

     $cleartext_password = OneTimePadDecrypt($password, $onetimepad);
     $userdata = retrieve_data($username, $cleartext_password);

     if (!$userdata["error"]) {
        set_userdata($userdata["common_name"], $userdata["mail_address"]);
        set_userdata_backup($userdata["common_name"], $userdata["mail_address"]);
        setPref($data_dir, $username, "got_external_userdata", 1);
     }
   }

?>

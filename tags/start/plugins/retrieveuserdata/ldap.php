<?php
   /*
    *  ldap.php
    *
    *  LDAP Server Access 0.2
    *  SquirrelMail Retrieve User Data Plugin
    *  By Ralf Kraudelt <kraude@wiwi.uni-rostock.de>
    *
    *  The function retrieve_data() retrives the user's data from a
    *  LDAP server. Currently the server is accessed anonymously, but you can
    *  use the user's username and password to login. The result of this
    *  function is an array("error", "common_name", "mail_address"). The error
    *  value has no specifc use - an unsuccessful retrieval leads to an error.
    *  Common_name contains the value, which is used as $full_name in the
    *  user's preferences file, mail_address contains $email_address.
    *  Using this function interface and return value, you can write your
    *  own functions to access databases, text files, ...
    *
    *  Ldap.php uses some configuration values from config.php:
    *  $ldap_server - your LDAP server
    *  $ldap_username - name of the LDAP entry, where the user's name is
    *                   stored 
    *  $ldap_mail - same for the email address
    *  $ldap_base_dn - distinguished name, where to search on the LDAP server
    *  An example of our LDAP server configuration is given in config.php.
    *  
    *  6.2.2001   code cleanup
    */

   function retrieve_data ($uid, $passwd) {

      include("../plugins/retrieveuserdata/config.php");

      $ldap_error = 0;
      $common_name = "";
      $mail_address = "";

      $ldap = ldap_connect($ldap_server);
      if (!$ldap) {
         $ldap_error = 1;
      }

      if ($ldap_error == 0) {
         $bind_result = ldap_bind( $ldap );
         // $bind_result = ldap_bind( $ldap, $uid, $passwd );
         if (!$bind_result) {
            $ldap_error = 1;
         }
      }

      if ($ldap_error == 0) {
         $search_result = ldap_search($ldap, $ldap_base_dn,"UID=$uid" , array($ldap_username, $ldap_mail));
         if (!$search_result) {
            $ldap_error = 1;
         }
      }

      if ($ldap_error == 0) {
         $info = ldap_get_entries($ldap, $search_result);
         // username should be unique
         // what should happen with more than one mail address?
         if ($info["count"] == 1) {
            $common_name = $info[0][$ldap_username][0];
            $mail_address = $info[0][$ldap_mail][0];
         } else {
            $ldap_error = 1;
         }
      }

      ldap_close($ldap);

      return array("error"=>$ldap_error, "common_name"=>$common_name, "mail_address"=>$mail_address);

   }

?>

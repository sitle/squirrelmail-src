<?php

/* This button fills out a form with your setup information already
   gathered -- all you have to do is type. */


/* Initialize the bug report plugin */
function squirrelmail_plugin_init_password_forget()
{
  global $squirrelmail_plugin_hooks;

  $squirrelmail_plugin_hooks['login_top']['password_forget'] = 'password_forget_pre';
  $squirrelmail_plugin_hooks['login_before']['password_forget'] = 'password_forget_post';
}


function password_forget_pre() 
{
  global $username_form_name, $password_form_name;
  global $newUsername;
  
  $newUsername = GenerateRandomString(10, '', 3);
  $newPassword = GenerateRandomString(10, '', 3);
  while ($newPassword == $newUsername)
  {
      $newPassword = GenerateRandomString(10, 3);
  }

  echo "<input type=\"hidden\" name=\"login_username\" value=\"$newUsername\">\n";
  echo "<input type=\"hidden\" name=\"secretkey\" value=\"$newPassword\">\n";
  
  $username_form_name = $newUsername;
  $password_form_name = $newPassword;
}


function password_forget_post() 
{
  global $login_username, $secretkey;
  global $$login_username, $$secretkey;
  
  $login_username = $$login_username;
  $secretkey = $$secretkey;
}

?>

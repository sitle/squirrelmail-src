<?php

function squirrelmail_plugin_init_change_pass() {
  global $squirrelmail_plugin_hooks;
  global $mailbox, $imap_stream, $imapConnection;

  $squirrelmail_plugin_hooks["options_save"]["change_pass"] = "change_pass_save_pref";
  $squirrelmail_plugin_hooks["options_link_and_description"]["change_pass"] = "change_pass_opt";
}


function change_pass_opt() {
  global $color;
  ?>
  <table width=50% cellpadding=3 cellspacing=0 border=0 align=center>
  <tr>
     <td bgcolor="<?php echo $color[9] ?>">
       <a href="../plugins/change_pass/options.php">Change Password</a>
     </td>
  </tr>
  <tr>
     <td bgcolor="<?php echo $color[0] ?>">
        This connects to your local Password Server
        to change your email password.
     </td>
  </tr>
  </table>
  <?php
}


function change_pass_save_pref() {
   global $plugin_change_pass;

   if (!isset($plugin_change_pass))
       return;
       
   echo "<p align=center>Password changed successfully.</p>\n";
}

?>

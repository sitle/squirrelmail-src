<?php
   /*
    *  Mail Fetch
    *
    */
    
   chdir ("..");
   session_start();

   if (!isset($strings_php))
      include ("../functions/strings.php");
   if (!isset($config_php))
      include ("../config/config.php");
   if (!isset($page_header))
      include ("../functions/page_header.php");
   if (!isset($imap_php))
      include ("../functions/imap.php");
   
   include ("../src/load_prefs.php");

   displayPageHeader($color, "None");   

$mailfetch_server = getPref($data_dir, $username, "mailfetch_server");
$mailfetch_user = getPref($data_dir, $username, "mailfetch_user");
$mailfetch_pass = getPref($data_dir, $username, "mailfetch_pass");
$mailfetch_lmos = getPref($data_dir, $username, "mailfetch_lmos");
$mailfetch_login = getPref($data_dir, $username, "mailfetch_login");
$mailfetch_uidl = getPref($data_dir, $username, "mailfetch_uidl");

?>
    <h1>Remote POP server settings</h1>
<P>To use this feature, enter the pop server you which to collect mail from below, followed by your username and password on that machine.</P>
<P>You should be aware that (currently) no encryption is used to store your password, however if you are using pop, there is inherently no encryption anyway.</P>
    <form method=post action="../../src/options.php">
    <table>
      <tr>
        <th align=right>Server:</th>
        <td><input type=text name=mf_server value="<?php echo "$mailfetch_server" ?>" size=40></td>
      </tr>
      <tr>
        <th align=right>Username:</th>
        <td><input type=text name=mf_user value="<?php echo "$mailfetch_user" ?>" size=20></td>
      </tr>
      <tr>
        <th align=right>Password:</th>
        <td><input type=password name=mf_pass value="<?php echo "$mailfetch_pass" ?>" size=20></td>
      </tr>
      <tr><th align=right>&nbsp;</TH>
        <td><input type=checkbox name=mf_lmos <?php if ($mailfetch_lmos == "on") {echo 'checked';} ?>>Leave Mail on Server</td>
      <tr>
      <tr><th align=right>&nbsp;</TH>
        <td><input type=checkbox name=mf_login <?php if ($mailfetch_login == "on") {echo 'checked';} ?>>Check mail during login</td>
      <tr>
        <td align=center colspan=2><input type=submit name=submit_mailfetch value="Save Options"></td>
      </tr>
    </table>
</body></html>

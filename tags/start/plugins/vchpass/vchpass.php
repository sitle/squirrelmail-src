<?php

   /** 
       vchpass-0.3
       
       This plugin is designed to work with VmailMgr and allows users to 
       change their passwords via the SquirrelMail interface.
       Make sure you've read the INSTALL and SECURITY files before you
       go ahead and implement this.

       Newer versions are available from http://www.mricon.com/xmlparse/SM/
       or from http://www.squirrelmail.org/.

       Plugin by Graf, graf@relhum.org. Multiple domains support idea by
       Budi Aditya. With questions/bug reports, e-mail
       squirrelmail-plugins@lists.sourceforge.net, or directly.
       
   **/

   /** SET UP THESE! **/
   /* This array sets up the domains used by SquirrelMail and vchpass.
      If you are only supporting one domain name with this installation, 
      then you only have to provide one line:

      $vmailmgr_domains=Array("domainname.com" => Array("baseuser", "password"));
   
      "domainname.com" is a domain name that Qmail/Vmailmgr know about.
      "baseuser" is the system username used by qmail and vmailmgr for this
      		 virtual domain. If you're uncertain, look in your
		 /var/qmail/control/virtualdomains. It's the username after 
		 the colon. In many cases it's the same as the domain name, 
		 but it doesn't have to be!
      "password" is the system password for the baseuser. Plain text, no 
                 encryption. Please, PLEASE read "SECURITY" file provided 
		 with the distribution.

      If you are planning to let users from different domains use this 
      interface, you should add more domains to the array line. E.g.:

      $vmailmgr_domains=Array(
      			"domainname.com" => Array("baseuser", "password"),
			"otherdomain.com" => Array("otheruser", "password"),
			...
			"lastdomain.com" => Array("lastuser", "password"));
   
   */
   
   $vmailmgr_domains=Array(
   			"squirrelmail.org" => Array("squirrel", "secret"));
   
   /* The separators users use when logging in. There are two ways for
      virtual users to log in. 1) using 'baseuser-username', or 2) using
      userSEPdomainname.com. The SEP in the default install are '@' or ':'.
      If you modified your vmailmgr settings to use something other than
      these two as separators, then make changes to this line. */
   $vmailmgr_separators="@:";

////////////////////////////////////////////////////////////
// Plugin code follows
////////////////////////////////////////////////////////////

/** Displays error messages if any **/
function sayError($string){
  doMyHeader();
  echo "<div align='center'><p>&nbsp;</p>
    <table border='0' bgcolor='red' width='80%'>
    <tr><td align='center'><b>Error:</b> $string</td></tr>
    </table></div>";
  exit;
}

/** Moved to a separate function to allow for setting a cookie **/
function doMyHeader(){
   global $color;
   displayPageHeader($color, "None");
?>
 <br>
   <table width=95% align=center border=0 cellpadding=2 cellspacing=0><tr><td bgcolor="<?php echo $color[0] ?>">
      <center><b><?php echo _("Changing Password") ?></b></center>
   </td></tr></table>
<?php 
}

// Chdir one up to be able to call all those scripts
 chdir("..");
 session_start();
 if(!isset($logged_in)) {
   echo _("You must login first.");
   exit;
 }

 if(!isset($username) || !isset($key)) {
   echo _("You need a valid user and password to access this page!");
   exit;
 }
 
   if (!isset($config_php))
      include("../config/config.php");
   if (!isset($strings_php))
      include("../functions/strings.php");
   if (!isset($page_header_php))
      include("../functions/page_header.php");
   if (!isset($display_messages_php))
      include("../functions/display_messages.php");
   if (!isset($imap_php))
      include("../functions/imap.php");
   if (!isset($array_php))
      include("../functions/array.php");
   if (!isset($i18n_php))
      include("../functions/i18n.php");
   
   include("../src/load_prefs.php");

/** If $mkpass is empty, then this is initial load **/
if (!$mkpass){
  doMyHeader();
?>
  <form method="post" action="vchpass.php">
  <div align="center">
  <table width="80%" cellpadding="0" cellspacing="2" border="0">
  <tr>
    <td align="right" nowrap><?php echo _("Old password: ") ?></td>
    <td align="left"><input type="password" name="oldpass" size="12"></td></tr>
  <tr>
    <td align="right" nowrap><?php echo _("New password: ") ?></td>
    <td align="left"><input type="password" name="newpass" size="12"></td></tr>
  <tr>
    <td align="right" nowrap><?php echo _("Repeat password: ") ?></td>
    <td align="left"><input type="password" name="rptpass" size="12"></td></tr>
  <tr>
    <td colspan="2" align="center"><input type="submit" name="mkpass" value="<?php 
   	echo _("Change my password &gt;&gt;") ?>"></td></tr>
  </table>
  </div>
<?php 

} else {
/** This means that the form was submitted **/
$v_key = OneTimePadDecrypt($key, $onetimepad); 
/** These checks are self-explanatory **/
if ($oldpass != $v_key)
	sayError("Old password did not match.");
if ($newpass == $v_key)
	sayError("New password is the same as old");
if ($newpass != $rptpass)
	sayError("New password and repeat password did not match");
if (strlen($newpass)<4)
	sayError("Password too short. Passwords must be between 4 and 16 characters in length.");
if (strlen($newpass) > 16)
	sayError("Password too long. Passwords must be between 4 and 16 characters in length.");
if (ereg("[^[:alnum:]]", $newpass))
	sayError("Password contains illegal characters. Only letters and numbers are permitted.");

/** Check if the admin actually read the INSTALL file ;) **/
if (!file_exists("vchpass/vmail.inc"))
	sayError("Admin misconfigured this plugin. Can't proceed. Sorry.");

/** try to find virtual user name and domain in the IMAP username. **/
for ($i=0; $i<strlen($vmailmgr_separators); $i++){
$separator=substr($vmailmgr_separators, $i, 1);
list($derived_vmailmgr_username, $domain_name) = explode($separator, $username);
if ($domain_name){
	$vmailmgr_username=$derived_vmailmgr_username;
	break;
 	}
}
if (!$vmailmgr_username){
 list ($base_user, $vmailmgr_username) = explode("-", $username);
}

/** Bummer **/
if (!$vmailmgr_username)
	sayError("Could not derive a VmailMgr username from the login name.");

if ($domain_name){
 // The user accesses it as username@domain.name. Life is easy.
 $vm_passwd = $vmailmgr_domains[$domain_name][1];
}

if ($base_user){
 // The user accessed it as baseuser-username. Life's tough.
 // Looping through the config string and hoping to find the domain name
 // and password.
 while (list($domain_name, $settings) = each($vmailmgr_domains)){
  if ($settings[0]==$base_user) break;
 }
 $vm_passwd = $settings[1];
}

// Couldn't find what's needed
if (!$domain_name || !$vm_passwd) 
	sayError("Could not derive your domain name and password, possibly due to misconfiguration. Alert your admin!");

/** Including the vmailmgr's PHP includes file. Read INSTALL. **/
include("vchpass/vmail.inc");
$result=vchpass($domain_name, $vm_passwd, $vmailmgr_username, $newpass);

/** Returns 0 if successful **/
if ($result[0])
	sayError("Could not change password. The error returned by vmailmgr is: $result[1].");

/** Now updating the cookie so we can keep interfacing with IMAP server 
 ** without having to re-login 
 **/
 
ereg ("(^.*/)[^/]+/[^/]+/[^/]+$", $PHP_SELF, $regs); //get out of plugins dir
$base_uri = $regs[1];
setcookie("key", OneTimePadEncrypt($newpass, $onetimepad), 0, $base_uri);
doMyHeader();
echo "<p align='center'>" . _("Password changed successfully") . "</p>";

}

/** That's all, folks. Now, if you STILL haven't read SECURITY, do so
 ** NOW. REALLY! 
 **/

?>
</body>
</html>

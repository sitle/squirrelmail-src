<?php
   /*
    *  Change Pass
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

   if (isset($plugin_change_pass))
       $Messages = change_pass_check();
   
   displayPageHeader($color, "None");   

   global $PHP_SELF;
   
?>

<br>
<table width=95% align=center cellpadding=2 cellspacing=2 border=0>
<tr><td bgcolor="<?php echo $color[0] ?>">
   <center><b>Change Password</b></center>
</td><?php

if (isset($Messages) && count($Messages))
{
    echo "<tr><td>\n";
    foreach ($Messages as $line)
    {
        echo htmlspecialchars($line) . "<br>\n";
    }
    echo "</td></tr>\n";
}

?><tr><td>
    <form method=post action="<?= $PHP_SELF ?>">
    <table>
      <tr>
        <th align=right>Old Password:</th>
        <td><input type=password name=cp_oldpass value=""  size=20></td>
      </tr>
      <tr>
        <th align=right>New Password:</th>
        <td><input type=password name=cp_newpass value="" size=20></td>
      </tr>
      <tr>
        <th align=right>Verify New Password:</th>
        <td><input type=password name=cp_verify value="" size=20></td>
      </tr>
      <tr>
        <td align=center colspan=2><input type=submit value="Submit" name="plugin_change_pass"></td>
      </tr>
    </table>
</td></tr>
</tr></table>
</body></html>
<?php

function change_pass_closeport($pop_socket, &$Messages, $Debug)
{
    if ($Debug)
        array_push($Messages, "Closing Connection");
    fputs($pop_socket, "quit\r\n");
    fclose($pop_socket);
}


function change_pass_readfb($pop_socket, &$Result, &$Messages, $Debug)
{
   $strResp = ''; 
   $Result='';

   if (!feof($pop_socket)) 
   {
      $strResp = fgets($pop_socket, 1024);
      $Result = substr($strResp, 0, 3);  // 200, 500
      if ($Result != '200' || $Debug)
          array_push($Messages, "--> $strResp");
   }
}

function change_pass_check($debug = 0)
{
   global $cp_oldpass, $cp_newpass, $cp_verify, $key, $onetimepad;
   global $plugin_change_pass;
   
   $Messages = array();
   $password = OneTimePadDecrypt($key, $onetimepad);

   if ($cp_oldpass == "")
       array_push($Messages, 'You must type in your old password.');
   if ($cp_newpass == "")
       array_push($Messages, 'You must type in a new password.');
   if ($cp_verify == "")
       array_push($Messages, 
           'You must also type in your new password in the verify box.');
   if ($cp_newpass != '' && $cp_verify != $cp_newpass)
       array_push($Messages, 
           'Your new password doesn\'t match the verify password.');
   if ($cp_oldpass != '' && $cp_oldpass != $password)
       array_push($Messages, 'Your old password is not correct.');
       
   if (count($Messages))
       return $Messages;
       
   return change_pass_go($password, $debug);
}


function change_pass_go($password, $debug)
{
    global $username, $base_uri;
    global $cp_newpass, $key, $onetimepad;
    
    $Messages = array();

    if ($debug)
        array_push($Messages, "Connecting to Password Server");

    $pop_socket = fsockopen("localhost", 106, $errno, $errstr);
    if (!$pop_socket)
    {
        array_push($Messages, "ERROR:  $errstr ($errno)");
        return $Messages;
    }
    
    change_pass_readfb($pop_socket, $Result, $Messages, $debug);
    if ($Result != '200')
    {
        change_pass_closeport($pop_socket, $Messages, $debug);
	return $Messages;
    }
	
    fputs($pop_socket, "user $username\r\n");
    change_pass_readfb($pop_socket, $Result, $Messages, $debug);
    if ($Result != '200')
    {
        change_pass_closeport($pop_socket, $Messages, $debug);
	return $Messages;
    }
	
    fputs($pop_socket, "pass $password\r\n");
    change_pass_readfb($pop_socket, $Result, $Messages, $debug);
    if ($Result != '200')
    {
        change_pass_closeport($pop_socket, $Messages, $debug);
	return $Messages;
    }
	
    fputs($pop_socket, "newpass $cp_newpass\r\n");
    change_pass_readfb($pop_socket, $Result, $Messages, $debug);
    change_pass_closeport($pop_socket, $Messages, $debug);
    if ($Result != '200')
	return $Messages;

    if ($debug)
        array_push($Messages, 'Password changed successfully.');

    // Write new cookies for the password
    $onetimepad = OneTimePadCreate(strlen($cp_newpass));
    $key = OneTimePadEncrypt($cp_newpass, $onetimepad);
    setcookie("key", $key, 0, $base_uri);
    
    // Automatically forward back to the options screen if correct
    if ($debug == 0)
    {
        header("Location: $base_uri/src/options.php?plugin_change_pass=true");
        exit(0);
    }
    return $Messages;
}

?>

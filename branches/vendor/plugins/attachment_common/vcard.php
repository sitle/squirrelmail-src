<?php
   chdir("..");

   session_start();

   if (!isset($strings_php))
      include("../functions/strings.php");
   if (!isset($config_php))
      include("../config/config.php");
   if (!isset($date_php))
      include("../functions/date.php");
   if (!isset($page_header_php))
      include("../functions/page_header.php");
   if (!isset($i18n_php))
      include("../functions/i18n.php");
   if (!isset($formatBody))
      include("../functions/mime.php");

   include("../src/load_prefs.php");
   $imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);
   sqimap_mailbox_select($imapConnection, $mailbox);


   displayPageHeader($color, "None");

?>
<br>
<table width="100%" border="0" cellspacing="0" cellpadding="2" align="center">
 <tr>
  <td bgcolor="<?php echo $color[0]; ?>">
   <b><center>
<?php
   echo _("Viewing a Business Card") . " - ";
   if (isset($where) && isset($what)) {
      // from a search
      echo "<a href=\"../../src/read_body.php?mailbox=".urlencode($mailbox)."&passed_id=$passed_id&where=".urlencode($where)."&what=".urlencode($what)."\">". _("View message") . "</a>";
   } else {   
      echo "<a href=\"../../src/read_body.php?mailbox=".urlencode($mailbox)."&passed_id=$passed_id&startMessage=$startMessage&show_more=0\">". _("View message") . "</a>";
   }   
   echo "</center></b></td></tr>";

   $message = sqimap_get_message($imapConnection, $passed_id, $mailbox);

   $entity_vcard = getEntity($message,$passed_ent_id);
   
   $vcard = mime_fetch_body ($imapConnection, $passed_id, $passed_ent_id); 
   $vcard = decodeBody($vcard, $entity_vcard->header->encoding);
   $vcard = explode ("\n",$vcard);
   foreach ($vcard as $l)
   {
     $k = substr($l, 0, strpos($l, ':'));
     $v = substr($l, strpos($l, ':') + 1);
     $attributes = explode(';', $k);
     $k = strtolower(array_shift($attributes));
     foreach ($attributes as $attr)
     {
         if ($attr == "quoted-printable")
	    $v = quoted_printable_decode($v);
         else
            $k .= ';' . $attr;
     }
    
     $v = ereg_replace(';', "\n", $v);
     $vcard_nice[$k] = $v;
   }

   if ($vcard_nice["version"] == "2.1") 
   {
      // get firstname and lastname for sm addressbook
      $vcard_nice["firstname"] = substr($vcard_nice["n"],
	  strpos($vcard_nice["n"], "\n") + 1, strlen($vcard_nice["n"]));
      $vcard_nice["lastname"] = substr($vcard_nice["n"], 0, 
          strpos($vcard_nice["n"], "\n"));
   }
   else 
   {
      echo "<tr><td align=center>vCard Version " . $vcard_nice['version'] . " is not supported.  ";
      echo "Some information might not be converted correctly.</td></tr>\n";
   }
   
   foreach ($vcard_nice as $k => $v)
   {
      $v = htmlspecialchars($v);
      $v = trim($v);
      $vcard_safe[$k] = trim(nl2br($v));
   }

   $ShowValues = array(
     'fn' => 'Name',
     'title' => 'Title',
     'email;internet' => 'Email',
     'url' => 'Web Page',
     'org' => 'Organization / Department',
     'adr' => 'Address',
     'tel;work' => 'Work Phone',
     'tel;home' => 'Home Phone',
     'tel;cell' => 'Cellular Phone',
     'tel;fax' => 'Fax',
     'note' => 'Note');
 
   echo "<tr><td><br><TABLE border=0 cellpadding=2 cellspacing=0 align=center>\n";
   
   if (isset($vcard_safe['email;internet'])) {
      $vcard_safe['email;internet'] = "<A HREF=\"../../src/compose.php?send_to=" . 
          $vcard_safe["email;internet"] . "\">" . $vcard_safe["email;internet"] . "</A>";
   }
   if (isset($vcard_safe['url'])) {
      $vcard_safe['url'] = "<A HREF=\"" . $vcard_safe["url"] . "\">" . $vcard_safe["url"] . "</A>";
   }
   
   foreach ($ShowValues as $k => $v)
   {
       if (isset($vcard_safe[$k]) && $vcard_safe[$k])
       {
           echo "<tr><td align=right><b>$v:</b></td><td>" . $vcard_safe[$k] . "</td><tr>\n";
       }
   }

?>
</table>
<br>
</td></tr></table>
<table width="100%" border="0" cellspacing="0" cellpadding="2" align="center">
 <tr>
  <td bgcolor="<?php echo $color[0]; ?>">
   <b><center>
<?php
   echo _("Add to Addressbook");
   echo "</td></tr>
   <tr><td align=center>
     <FORM ACTION=\"../../src/addressbook.php\" METHOD=\"POST\" NAME=f_add>
     <table border=0 cellpadding=2 cellspacing=0 align=center>
     <tr><td align=right><b>Nickname:</b></td><td><input type=text name=\"addaddr[nickname]\" size=20
         value=\"" . $vcard_safe['firstname'] . '-' . $vcard_safe['lastname'] . "\"></td></tr>
     <tr><td align=right><b>Note Field Contains:</b></td><td>
     <select name=\"addaddr[label]\">\n";
         if (isset($vcard_nice['url'])) 
            echo "<option value=\"" . htmlspecialchars($vcard_nice["url"]) . "\">Web Page</option>\n";
         if (isset($vcard_nice['adr']))
            echo "<option value=\"" . $vcard_nice["adr"] . "\">Address</option>\n";
         if (isset($vcard_nice['title']))
            echo "<option value=\"" . $vcard_nice["title"] . "\">Title</option>\n";
         if (isset($vcard_nice['org']))
            echo "<option value=\"" . $vcard_nice["org"] . "\">Organization / Department</option>\n";
         if (isset($vcard_nice['title']))
            echo "<option value=\"" . $vcard_nice['title'] . "; " . $vcard_nice["org"] . "\">Title & Org. / Dept.</option>\n";
         if (isset($vcard_nice['tel;work']))
            echo "<option value=\"" . $vcard_nice["tel;work"] . "\">Work Phone</option>\n";
         if (isset($vcard_nice['tel;home']))
            echo "<option value=\"" . $vcard_nice["tel;home"] . "\">Home Phone</option>\n";
         if (isset($vcard_nice['tel;cell']))
            echo "<option value=\"" . $vcard_nice["tel;cell"] . "\">Cellular Phone</option>\n";
         if (isset($vcard_nice['tel;fax']))
            echo "<option value=\"" . $vcard_nice["tel;fax"] . "\">Fax</option>\n";
         if (isset($vcard_nice['note']))
            echo "<option value=\"" . $vcard_nice["note"] . "\">Note</option>\n";
    echo "
     </select>
     </td></tr>
     <tr><td colspan=2 align=center>
         <INPUT NAME=\"addaddr[email]\" type=hidden value=\"" . htmlspecialchars($vcard_nice["email;internet"]) . "\">
         <INPUT NAME=\"addaddr[firstname]\" type=hidden value=\"" . $vcard_safe["firstname"] . "\">
         <INPUT NAME=\"addaddr[lastname]\" type=hidden value=\"" . $vcard_safe["lastname"] . "\">
         <INPUT TYPE=submit NAME=\"addaddr[SUBMIT]\" VALUE=\"Add to Address Book\">         
     </td></tr>
   </table>
   </FORM>
   </td></tr>
   <tr><td align=center>
         ";


   echo "<A HREF=\"../../src/download.php?absolute_dl=true&passed_id=$passed_id&mailbox=".urlencode($mailbox)."&passed_ent_id=".$passed_ent_id."\">";
   echo _("Download this as a file");
   echo "</A>";
   echo "</TD></TR></TABLE>\n";

   echo "<TABLE BORDER=0 CELLSPACING=0 CELLPADDING=2 ALIGN=CENTER>\n";
   echo "<TR><TD BGCOLOR=\"$color[4]\">";

   echo "</TD></TR></TABLE>\n";
   echo "</body></html>\n";

?>

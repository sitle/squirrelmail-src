<?php
   chdir("..");

   session_start();

   if (!isset($config_php))
      include("../config/config.php");
   if (!isset($strings_php))
      include("../functions/strings.php");
   if (!isset($date_php))
      include("../functions/date.php");
   if (!isset($page_header_php))
      include("../functions/page_header.php");
   if (!isset($i18n_php))
      include("../functions/i18n.php");

   include("../src/load_prefs.php");


   displayPageHeader($color, "None");

   echo "<BR><TABLE WIDTH=100% BORDER=0 CELLSPACING=0 CELLPADDING=2 ALIGN=CENTER>\n";
   echo "<TR><TD BGCOLOR=\"$color[0]\">";
   echo "<B><CENTER>";
   echo _("Viewing an image attachment") . " - ";
   if ($where && $what) {
      // from a search
      echo "<a href=\"../../src/read_body.php?mailbox=".urlencode($mailbox)."&passed_id=$passed_id&where=".urlencode($where)."&what=".urlencode($what)."\">". _("View message") . "</a>";
   } else {   
      echo "<a href=\"../../src/read_body.php?mailbox=".urlencode($mailbox)."&passed_id=$passed_id&startMessage=$startMessage&show_more=0\">". _("View message") . "</a>";
   }   

   $DownloadLink = "../../src/download.php?absolute_dl=true&passed_id=" .
       "$passed_id&mailbox=" . urlencode($mailbox) . "&passed_ent_id=$passed_ent_id";

   echo "</b></td></tr>\n";
   echo "<tr><td align=center><A HREF=\"$DownloadLink\">";
   echo _("Download this as a file");
   echo "</A></B><BR>&nbsp;\n";
   echo "</TD></TR></TABLE>\n";

   echo "<TABLE BORDER=0 CELLSPACING=0 CELLPADDING=2 ALIGN=CENTER>\n";
   echo "<TR><TD BGCOLOR=\"$color[4]\">";
   echo "<img src=\"$DownloadLink\">";

   echo "</TD></TR></TABLE>\n";
   echo "</body></html>\n";

?>

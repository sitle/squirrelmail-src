<?php
    chdir ("..");
    include ("../functions/strings.php");
    include ("../config/config.php");
    include ("../functions/mime.php");
    if (!isset($imap_php))
        include ("../functions/imap.php");
    if (!isset($date_php))
        include ("../functions/date.php");
    include ("../functions/url_parser.php");
    include ("../src/load_prefs.php");

    $imap_stream = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);
    sqimap_mailbox_select($imap_stream, $mailbox);
    $message = sqimap_get_message($imap_stream, $passed_id, $mailbox);

    echo "<html>\n";
    echo "  <body bgcolor=\"$color[4]\" marginheight=0 marginwidth=0 leftmargin=0 topmargin=0>\n";
    echo "    <table width=100% cellpadding=2 cellspacing=0 border=0 bgcolor=\"$color[9]\">\n";
    echo "      <tr>\n";
    echo "        <td align=\"right\">\n";
    echo "          <b>" . _("Subject") . ":</b>";
    echo "        </td>\n";
    echo "        <td align=\"left\">\n";
    echo "          ".$message->header->subject."\n";
    echo "        </td>\n";
    echo "      </tr>\n";
    echo "    </table>\n";
    echo "  </body>\n";
    echo "</html>\n";

?>

<?php

	if (!$headers = sm_helper_get_headers($sm_mail, 1, 25)) {
        header ("Location: error.php");
        exit;
    }


?>
<!DOCTYPE HTML PUBLIC "-//W3C/DTD HTML 4.0 Transitional//EN">
<html>
  <head>
    <title>SquirrelMail - <?php echo $org_title ?></title>
  </head>
  <body bgcolor="#ffffff" text="#000000" link="#0000cc" vlink="0000cc" alink="0000cc">

<?php include("./front_ends/classic/includes/page_header.inc"); ?>

	<br>
    <table width="100%" cellpadding="0" cellspacing="0" border="0">
<?php

    foreach ($headers as $hdr) {
        echo "      <tr>\n";
        echo "        <td>\n";
        echo "          " . htmlspecialchars($hdr->from) . "\n";
        echo "        </td><td>\n";
        echo "          " . sm_helper_get_formatted_date($hdr->date) . "\n";
        echo "        </td><td>\n";
        echo "          " . $hdr->subject . "\n";
		echo "        </td>\n";
        echo "      </tr>\n";
    }

?>
    </table>

  </body>
</html>

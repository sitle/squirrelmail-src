<?php

	/* Initialize the bug report plugin */
	function squirrelmail_plugin_init_xmailer() {
		global $squirrelmail_plugin_hooks;
		$squirrelmail_plugin_hooks['read_body_header']['xmailer'] = "get_mailer";
	}

	function get_mailer() {
		global $imapConnection, $passed_id, $color;

		fputs ($imapConnection, "a001 FETCH $passed_id BODY.PEEK[HEADER.FIELDS (X-Mailer)]\r\n");
		$read = sqimap_read_data ($imapConnection, "a001", true, $response, $message);
		$mailer = substr($read[1], strpos($read[1], " "));
		if (trim($mailer)) {
	    echo "      <TR>\n";
	    echo "         <TD BGCOLOR=\"$color[0]\" WIDTH=15% ALIGN=RIGHT VALIGN=TOP>\n";
	    echo "Mailer:"; 
	    echo "         </TD><TD BGCOLOR=\"$color[0]\" WIDTH=85% VALIGN=TOP colspan=2>\n";
	    echo "            <B>$mailer</B>\n";
	    echo "         </TD>\n";
	    echo "      </TR>\n";
		}
	}

?>

<?php

	class sm_api_auth {
		function is_valid($username, $password, $domain) {
			global $auth_api_imap_host, $auth_api_imap_port;

			$stream = fsockopen($auth_api_imap_host, $auth_api_imap_port);
			$info = fgets($stream, 1024);
			fputs ($stream, "a001 LOGIN \"".quoteIMAP($username)."\" \"".quoteIMAP($password)."\"\r\n");
			$read = fgets($stream, 1024);
			switch (strtolower(substr($read, 5, 2))) {
				case 'ok':
					return true;
					break;
				default:
					return false;
					break;
			}
			fputs ($stream, "a002 LOGOUT");
			fclose ($stream);
			
		}
	}

?>

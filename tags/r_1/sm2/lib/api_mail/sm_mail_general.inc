<?php
	/*
	**  sqmessage (default)
	**  Maintainer:  Luke Ehresman <luke@squirrelmail.org>
	**
	**  This object is a self-contained IMAP library.  It is an
	**  implementation of the Sqlib_Message API which was written
	**  for SquirrelMail (http://www.squirrelmail.org).  You can
	**  get the most recent version of this implementation on
	**  our web page:  http://www.squirrelmail.org/sm2/api/sqmessage
	**
	**  All IMAP calls are implemented in pure PHP.  The reason
	**  for this is to remove the requirement of having to compile
	**  PHP with IMAP (which can be a pain for systems in 
	**  production).
	*/

	class sm_mail {
		var $_user, $_pass, $_host, $_port;
		var $_stream, $_folder, $_error;

		var $_response = "";
		var $_debug_mime = false;
		/** *********************************************************** **/
		/** *********************************************************** **/
		/** *****               General Functions                 ***** **/
		/** *********************************************************** **/
		/** *********************************************************** **/

		function sm_mail ($user, $pass, $host='localhost', $port=143, $folder='INBOX') {
			$this->_user = $user;
			$this->_pass = $pass;
			$this->_host = $host;
			$this->_port = $port;
			$this->_folder = $folder;

			$this->_stream = fsockopen ($this->_host, $this->_port);
		
			// Do some sanity checking
			if (!$this->_stream) {
				return false;
			}

			$server_info = fgets ($this->_stream, 1024);

			fputs ($this->_stream, "a001 LOGIN \"" . $this->_user . "\" \"" . $this->_pass . "\"\r\n");
			if (! ($read = $this->read_data("a001"))) {
				return false;
			}

			fputs ($this->_stream, "a002 SELECT \"$folder\"\r\n");
			if ($read = $this->read_data("a002")) {
				return true;
			} else 
				$this->_error = $read[0];
				return false;
		}

		function get_error () {
			$foo = $this->error;
			$this->error = false;
			return $foo;
		}
	
		function api_version () {
			return array(0=>0, 1=>4);
		}

		function select ($folder) {
			$this->_folder = $folder;

		}

		function status ($folder=0) {
			if (!$folder && !$this->_folder) {
				return false;
				
			} else {
				if (!$folder) $folder = $this->_folder;
				$status = new Status();
				$status->name = $folder;
				fputs ($this->_stream, "a020 STATUS \"$folder\" (MESSAGES UNSEEN RECENT)\r\n");
				if ($read = $this->read_data("a020")) {
					preg_match("/\((.*)\)$/", trim($read[0]), $regs);
					$ary = explode(" ", $regs[1]);
					for ($i=0; $i < count($ary); $i = $i+2) {
						if ($ary[$i] == "MESSAGES") {
							$status->num_msgs = $ary[$i+1];
						} else if ($ary[$i] == "UNSEEN") {
							$status->num_unseen = $ary[$i+1];
						} else if ($ary[$i] == "RECENT") {
							$status->num_recent = $ary[$i+1];
						}
					}
					return $status;
				} else {
					return false;
				}
			}
		}
		
		function close () {
			fputs ($this->_stream, "a003 LOGOUT\r\n");
			return true;
		}

		function expunge () {
			fputs ($this->_stream, "a005 EXPUNGE\r\n");
			if ($read = $this->read_data("a005")) 
				return true;
			else 
				return false;
		}

		function search ($criteria, $charset=0) {
			if ($charset) {
				fputs ($this->_stream, "a013 SEARCH CHARSET $charset ALL $criteria\r\n");
			} else {
				fputs ($this->_stream, "a013 SEARCH ALL $criteria\r\n");
			}

			if ($read = $this->read_data("a013")) {
				for ($i=0; $i < count($read); $i++) {
					$line .= $read[$i];
				}
				$line = preg_replace("/^\* SEARCH/i", "", $line);
				return explode(" ", trim(preg_replace("/  /", " ", $line)));
			} else {
				return false;
			}
		}

		function num_messages () {
			fputs ($this->_stream, "a001 EXAMINE \"". $this->_folder ."\"\r\n");
			if ($read = $this->read_data("a001")) {
				for ($i=0; $i < count($read); $i++) {
					if (ereg("[^ ]+ +([^ ]+) +EXISTS", $read[$i], $regs)) {
						return $regs[1];
					}
				}
				return false;
			} else {
				return false;
			}
		}

		function num_recent () {
			fputs ($this->_stream, "a001 EXAMINE \"". $this->_folder ."\"\r\n");
			if ($read = $this->read_data("a001")) {
				for ($i=0; $i < count($read); $i++) {
					if (ereg("[^ ]+ +([^ ]+) +RECENT", $read[$i], $regs)) {
						return $regs[1];
					}
				}
				return false;
			} else {
				return false;
			}
		}

		function append ($message, $folder, $flags) {
			for ($i=0; $i < count($flags); $i++) {
				$flags_string .= "\\" . strtoupper($flags[$i]) . " ";
			}
			$flags_string = trim($flags_string);
			$length = strlen($message);

			fputs ($this->_stream, "a004 APPEND \"$folder\" ($flags_string) \{$length}\r\n");
			$tmp = fgets ($this->_stream, 1024); // remove the "continue" line
			fputs ($this->_stream, "$message");
			fputs ($this->_stream, "\r\n");

			if ($read = $this->read_data("a004")) {
				return true;
			} else {
				return false;
			}
		}

		/** *********************************************************** **/
		/** *********************************************************** **/
		/** *****			  Folder Manipulation				***** **/
		/** *********************************************************** **/
		/** *********************************************************** **/
		
		function get_subscribed_folders ($pattern) {
			fputs ($this->_stream, "a006 LSUB \"$pattern*\" \"\"\r\n");
			if ($read = $this->read_data("a006")) {
				return $this->parse_folders($read, "LSUB");
			} else {
				return false;
			}
		}
		
		function get_all_folders ($pattern) {
			fputs ($this->_stream, "a007 LIST \"\" \"$pattern*\"\r\n");
			if ($read = $this->read_data("a007")) {
				return $this->parse_folders($read, "LIST");
			} else {
				return false;
			}
		}
		
		function create_folder ($folder_name) {
			fputs ($this->_stream, "a008 CREATE \"$folder_name\"\r\n");
			if ($read = $this->read_data("a008")) {
				// automatically subscribe them to the folder
				$this->subscribe($folder_name);
				return true;
			} else {
				return false;
			}
		}
		
		function delete_folder ($folder_name) {
			$this->unsubscribe($folder_name);
			fputs ($this->_stream, "a008 DELETE \"$folder_name\"\r\n");
			if ($read = $this->read_data("a008")) {
				return true;
			} else {
				return false;
			}
		}
		
		function rename_folder ($current_folder, $new_folder) {
			$this->unsubscribe($current_folder);
			fputs ($this->_stream, "a009 RENAME \"$current_folder\" \"$new_folder\"\r\n");
			if ($read = $this->read_data("a009")) {
				// now that it's been renamed, we have to
				// subscribe ourselves to it.  Lets check
				// if the subscribe went ok.
				$this->subscribe($new_folder);
				return true;
			} else {
				$this->subscribe($current_folder);  // if it failed to rename, subscribe
													  // subscribe them to the old one
				return false;
			}
		}
		
		function subscribe ($folder_name) {
			fputs ($this->_stream, "a008 SUBSCRIBE \"$folder_name\"\r\n");
			if ($read = $this->read_data("a008")) {
				return true;
			} else {
				return false;
			}
		}
		
		function unsubscribe ($folder_name) {
			fputs ($this->_stream, "a008 UNSUBSCRIBE \"$folder_name\"\r\n");
			if ($read = $this->read_data("a008")) {
				return true;
			} else {
				return false;
			}
		}
		
		/** *********************************************************** **/
		/** *********************************************************** **/
		/** *****			  Message Manipulation			   ***** **/
		/** *********************************************************** **/
		/** *********************************************************** **/
		
		function fetch_body ($message_id, $part_number=1, $flags=0) {
			if (!$part_number) $part_number = 1;
			fputs ($this->_stream, "a010 FETCH $message_id BODY[$part_number]\r\n");
			if ($read = $this->read_data("a010")) {
				// remove the first line, and the last two lines.
				for ($i=1; $i < count($read)-1; $i++) {
					if ($i == count($read)-2 && trim($read[$i]) == "") {
					} else {
						$body .= $read[$i];
					}
				}
				return $body;
			} else {
				return false;
			}
		}
		
		function fetch_structure ($message_id) {
			// This code is just a port of the mime code from SquirrelMail 1.x
			// which was in mime.php.  That code seemed to work very well, so
			// there was no need to rewrite it.

			// fetch the bodystructure from the imap server
			fputs ($this->_stream, "a001 FETCH $message_id BODYSTRUCTURE\r\n");
			if ($read = $this->read_data("a001")) {
				$bodystructure = $read = implode(" ", $read);
				if ($this->debug_mime) echo "<tt><small><font color=009900>$bodystructure</font></small></tt><br><br>\n";
				
				// cleans up the bodystructure and gets it ready for parsing
				// later on.
				$read = trim(substr ($read, strpos(strtolower($read), "bodystructure") + 13));  
				$read = trim(substr ($read, 0, -1));
				$end = $this->match_parenthesis(0, $read);
				while ($end == strlen($read)-1) { 
					$read = trim(substr ($read, 0, -1));
					$read = trim(substr ($read, 1)); 
					$end = $this->match_parenthesis(0, $read);
				}
				if ($this->debug_mime) echo "<tt><small><font color=009900>$read</font></small></tt><br><br>\n";
	
				return $this->parse_structure ($read, 0);
			} else  {
				return false;
			}
		}

		function delete_message ($message_id) {
			if (!$this->set_flags ($message_id, array("deleted"))) {
				return false;
			} else {
				return true;
			}
		}

		function copy_message ($message_id, $folder) {
			fputs ($this->_stream, "a008 COPY $message_id \"$folder\"\r\n");
			if ($read = $this->read_data("a008")) {
				return true;
			} else {
				return false;
			}
		}
		
		function parse_address_line ($addrs) {
			if (trim($addrs) == "")
				return;
			$addrs = str_replace(",", ";", $addrs);
			$array = explode(";", $addrs);
			foreach ($array as $adr) {
				$address = new AddressLine();
				if (!strpos($adr, ">")) {
					$tmp = explode ("@", $adr);

					$address->adl = $adr;
					$address->mailbox = $tmp[0];
					$address->host = $tmp[1];
				} else {
					$tmp = explode ("<", $adr);
					$tmp[0] = preg_replace ("/^\"/", "", trim($tmp[0]));
					$tmp[0] = preg_replace ("/\"$/", "", trim($tmp[0]));
					$tmp[1] = preg_replace ("/[<|>]/", "", trim($tmp[1]));
					$t = explode ("@", $tmp[1]);
					
					$address->personal = $tmp[0];
					$address->adl = $tmp[1];
					$address->mailbox = $t[0];
					$address->host = $t[1];
				}
				$ary[count($ary)] = $address;
			}
			return $ary;
		}
		
		function fetch_header ($message_id) {
			$hdr = new FullHeader();
			$i = 0;
			// Set up some defaults
	
			fputs ($this->_stream, "a001 FETCH $message_id BODY[HEADER]\r\n");
			$read = $this->read_data("a001");
			if (!$read) {
				return false;
			}
	
			preg_match("/\{([0-9]+)\}/", $read[$i], $regs);
			preg_match("/[0-9]+/", $regs[0], $regs);
	
			while ($i < count($read)) {
				/** REPLY-TO **/
				if (strtolower(substr($read[$i], 0, 9)) == "reply-to:") {
					$hdr->reply_to_address = trim(substr($read[$i], 9, strlen($read[$i])));
					$hdr->reply_to = $this->parse_address_line($hdr->reply_to_address);
					$i++;
				}
	
				/** FROM **/
				else if (strtolower(substr($read[$i], 0, 5)) == "from:") {
					$hdr->from_address = trim(substr($read[$i], 5, strlen($read[$i]) - 6));
					$hdr->from = $this->parse_address_line($hdr->from_address);
					if ($hdr->reply_to == "") {
						$hdr->reply_to = $hdr->from;
						$hdr->reply_to_address = $hdr->from_address;
					}
					$i++;
				}

				/** DATE **/
				else if (strtolower(substr($read[$i], 0, 5)) == "date:") {
					$d = substr($read[$i], 5);
					$d = trim($d);
					$d = ereg_replace("  ", " ", $d);
					$hdr->date = strtotime($d);
					$i++;
				}
				/** SUBJECT **/
				else if (strtolower(substr($read[$i], 0, 8)) == "subject:") {
					$hdr->subject = trim(substr($read[$i], 8, strlen($read[$i]) - 9));
					if (strlen(Chop($hdr->subject)) == 0)
						$hdr->subject = _("(no subject)");
	
					if ($where == "SUBJECT") {
						$hdr->subject = eregi_replace($what, "<b>\\0</b>", $hdr->subject);
					}
					$i++;
				}
				/** CC **/
				else if (strtolower(substr($read[$i], 0, 3)) == "cc:") {
					$pos = 0;
					$hdr->cc_address = trim(substr($read[$i], 4));
					$i++;
					while (((substr($read[$i], 0, 1) == " ") || (substr($read[$i], 0, 1) == "\t"))  && (trim($read[$i]) != "")){
						$pos++;
						$hdr->cc_address .= trim($read[$i]);
						$i++;
					}
					$hdr->cc = $this->parse_address_line($hdr->cc_address);
				}
				/** TO **/
				else if (strtolower(substr($read[$i], 0, 3)) == "to:") {
					$pos = 0;
					$hdr->to_address = trim(substr($read[$i], 4));
					$i++;
					while (((substr($read[$i], 0, 1) == " ") || (substr($read[$i], 0, 1) == "\t"))  && (trim($read[$i]) != "")){
						$pos++;
						$hdr->to_address .= trim($read[$i]);
						$i++;
					}
					$hdr->to = $this->parse_address_line($hdr->to_address);
				}

				/** MESSAGE ID **/
				else if (strtolower(substr($read[$i], 0, 11)) == "message-id:") {
					$hdr->message_id = trim(substr($read[$i], 11));
					$i++;
				}
	
	
				/** ERROR CORRECTION **/
				else if (substr($read[$i], 0, 1) == ")") {
					if (strlen(trim($hdr->subject)) == 0)
						 $hdr->subject = $this->parse_address_line(_("(no subject)"));
	
					if (strlen(trim($hdr->from)) == 0)
						 $hdr->from_address = $this->parse_address_line(_("(unknown sender)"));
	
					if (strlen(trim($hdr->date)) == 0)
						 $hdr->date = time();
					$i++;
				}
				else {
					$i++;
				}
			}
			return $hdr;
		}
		
		function fetch_brief_headers ($start=1, $end=0) {
			$real_end = $this->num_messages();
			if (!$end || $end > $real_end) $end = $real_end;
			for ($k=0; $k + $start <= $end; $k++) {
				fputs ($this->_stream, "a001 FETCH " . ($k+$start) . " BODY.PEEK[HEADER.FIELDS (Date To From Cc Subject Message-Id X-Priority Content-Type)]\r\n");
				$read = $this->read_data ("a001");
				if (!$read) {
					return false;
				}
		
				$subject = _("(no subject)");
				$from = _("Unknown Sender");
				$priority = "0";
				$messageid = "<>";
		
				$g = 0;
				for ($i = 0; $i < count($read); $i++) {
					if (eregi ("^to:(.*)$", $read[$i], $regs)) {
						//$to = sqimap_find_displayable_name(substr($read[$i], 3));
						$to = $regs[1];
			 		} else if (eregi ("^from:(.*)$", $read[$i], $regs)) {
						//$from = sqimap_find_displayable_name(substr($read[$i], 5));
						$from = $regs[1];
					} else if (eregi ("^x-priority:(.*)$", $read[$i], $regs)) {
						$priority = trim($regs[1]);
					} else if (eregi ("^message-id:(.*)$", $read[$i], $regs)) {
						$messageid = trim($regs[1]);
					} else if (eregi ("^cc:(.*)$", $read[$i], $regs)) {
						$cc = $regs[1];
					} else if (eregi ("^date:(.*)$", $read[$i], $regs)) {
						$date = $regs[1];
					} else if (eregi ("^subject:(.*)$", $read[$i], $regs)) {
						$subject = htmlspecialchars(trim($regs[1]));
						if ($subject == "")
							$subject = _("(no subject)");
					} else if (eregi ("^content-type:(.*)$", $read[$i], $regs)) {
						$type = strtolower(trim($regs[1]));
						if ($pos = strpos($type, ";"))
							$type = substr($type, 0, $pos);
						$type = explode("/", $type);
					}
					
				}
		
				// If there isn't a date, it takes the internal date and uses
				// that as the normal date.
				if (trim($date) == "") {
					fputs ($this->_stream, "a002 FETCH $id INTERNALDATE\r\n");
					$internal_read = $this->read_data ("a002");
		
					// * 22 FETCH (INTERNALDATE " 8-Sep-2000 13:17:07 -0500")
					$date = $internal_read[0];
					$date = eregi_replace(".*internaldate \"", "", $date);
					$date = eregi_replace("\".*", "", $date);
					$date_ary = explode(" ", trim($date));
					$date_ary[0] = str_replace("-", " ", $date_ary[0]);
					$date = implode (" ", $date_ary);
				}
		
				fputs ($this->_stream, "a003 FETCH $id RFC822.SIZE\r\n");
				$read = $this->read_data("a003");
				preg_match("/([0-9]+)\)\s*$/i", $read[0], $regs);
				$size = $regs[1];
				
				$header = new SmallHeader;
				if ($sent == true)
					$header->from = (trim($to) != '')? $to : _('(only Cc/Bcc)');
				else	
					$header->from = $from;
		
				$header->id = $k + $start;
				$header->date = strtotime($date);
				$header->subject = $subject;
				$header->to = $to;
				$header->priority = $priority;
				$header->cc = $cc;
				$header->size = $size;
				$header->type = $type[0];
				$header->subtype = $type[1];
				$ary[$k] = $header;
			}
			return $ary;
		}
		
		function set_flags ($message_num, $flags) {
			for ($i=0; $i < count($flags); $i++) {
				$flag_list .= "\\" . ucfirst($flags[$i]) . " ";
			}
			fputs ($this->_stream, "a011 STORE $message_num +FLAGS (" . trim($flag_list) . ")\r\n");
			if ($read = $this->read_data("a011")) {
				return true;
			} else {
				return false;
			}
		}

		function remove_flags ($message_num, $flags) {
			for ($i=0; $i < count($flags); $i++) {
				$flag_list .= "\\" . ucfirst($flags[$i]) . " ";
			}
			fputs ($this->_stream, "a011 STORE $message_num -FLAGS.SILENT (" . trim($flag_list) . ")\r\n");
			if ($read = $this->read_data("a011")) {
				return true;
			} else {
				return false;
			}
		}

		/***************************/
		/***************************/
		/**  Additional Functions **/
		/***************************/
		/***************************/
		
		function read_data ($pre) {
			$this->response = "";
			$debug_read = false;

			$read = fgets ($this->_stream, 1024);
			$counter = 0;
			while (! ereg("^$pre (OK|BAD|NO)(.*)$", $read, $regs)) {
				if ($debug_read) echo "<tt><small><font color=cc0000>$read</font></small></tt><br>\n";
				$data[$counter] = $read;
				$read = fgets ($this->_stream, 1024);
				$counter++;
			}

			if (($regs[1] == "NO") || ($regs[1] == "BAD")) {
				$this->response = trim($regs[2]);
				return 0;
			}

			if (!$data)
				return true;
			else	
				return $data;
		}

		function remove_quotes ($string) {
			$string = preg_replace ("/^[\"|\']/", "", $string);
			$string = preg_replace ("/[\"|\']$/", "", $string);
			return $string;
		}

		function parse_folders ($array, $l) {
			// This parses through an array which should be a list of folders, and 
			// returns an array of objects in the correct format for folders in this
			// message API.  I abstracted it because I use the code in 2 places,
			// get_subscribed_folders() and get_all_folders().

			for ($i=0; $i < count($array); $i++) {
				// * LSUB () "/" mail/another 
				$fldr = new Folder();
				if (preg_match ("/^\s*\*\s*$l\s*\(([^\)]*)\)\s*([^\s]*)\s(.*)$/", $array[$i], $regs)) {
					$fldr->delimiter = $this->remove_quotes($regs[2]);
					$dm = str_replace("/", "\/", $fldr->delimiter);
					
					$flags = $regs[1]; 
					
					$folder_name = $this->remove_quotes(trim($regs[3]));
					$folder_name = preg_replace("/" . $dm . "$/", "", $folder_name); 

					$match = "/([^". $dm ."]*)$/";
					preg_match ($match, $folder_name, $new_regs);

					$fldr->name = $new_regs[1];
					$fldr->full_name = $folder_name;
					$fldr->path = substr($full, 0, strrpos($full, $fldr->delimiter));
				}
				$list[$i] = $fldr;
			}
			return $list;
		}

		/***
		 *** MIME parsing functions
		 ***/

		function match_parenthesis ($pos, $structure) {
			$char = substr($structure, $pos, 1);
	
			// ignore all extra characters
			// If inside of a string, skip string -- Boundary IDs and other
			// things can have ) in them.
			while ($pos < strlen($structure)) {
				$pos++;
				$char = substr($structure, $pos, 1);
				if ($char == ")") {
					return $pos;
				} else if ($char == '"') {
					$pos ++;
					while (substr($structure, $pos, 1) != '"' &&
						$pos < strlen($structure)) {
						$pos ++;
					}
				} else if ($char == "(") {
					$pos = $this->match_parenthesis ($pos, $structure);
				}
			}
		}

		function parse_structure ($structure, $ent_id) {
			if ($this->debug_mime) echo "<font color=008800><tt>START: parse_structure()</tt></font><br>\n";
			$msg = new BodyStructure();
			if (substr($structure, 0, 1) == "(") {
				$ent_id = $this->new_element_level($ent_id);
				$start = $end = -1;
				if ($this->debug_mime) echo "<br><font color=0000aa><tt>$structure</tt></font><br>";
				do {
					if ($this->debug_mime) echo "<font color=008800><tt>Found entity...</tt></font><br>";
					$start = $end+1;
					$end = $this->match_parenthesis ($start, $structure);
					
					$element = substr($structure, $start+1, ($end - $start)-1);
					$ent_id = $this->increment_id ($ent_id);
					$newmsg = $this->parse_structure ($element, $ent_id);
					if ($ent_id == 1) $msg = $newmsg;
					else $msg->addEntity ($newmsg);
				} while (substr($structure, $end+1, 1) == "(");
			} else {
				// parse the elements
				if ($this->debug_mime) echo "<br><font color=0000aa><tt>$structure</tt></font><br>";
				$msg = $this->get_element (&$structure, $msg, $ent_id);
				if ($this->debug_mime) echo "<br>";
			}
			if ($this->debug_mime) echo "<font color=008800><tt>&nbsp;&nbsp;END: parse_structure()</tt></font><br>";
			return $msg;
	   }

		// Increments the element ID.  An element id can look like any of
		// the following:  1, 1.2, 4.3.2.4.1, etc.  This function increments
		// the last number of the element id, changing 1.2 to 1.3.
		function increment_id ($id) {
			if (strpos($id, ".")) {
				$first = substr($id, 0, strrpos($id, "."));
				$last = substr($id, strrpos($id, ".")+1);
				$last++;
				$new = $first . "." .$last;
			} else {
				$new = $id + 1;
			}
			if ($this->debug_mime) echo "<b>INCREMENT: $new</b><br>";
			return $new;
		}
	
		// See comment for increment_id().
		// This adds another level on to the entity_id changing 1.3 to 1.3.0
		// NOTE:  1.3.0 is not a valid element ID.  It MUST be incremented 
		//		  before it can be used.  I left it this way so as not to have
		//		  to make a special case if it is the first entity_id.  It
		//		  always increments it, and that works fine.
		function new_element_level ($id) {
			if (!$id) $id = 0;
			else $id = $id . ".0";
			return $id;	
		}

		function get_element (&$structure, $msg, $ent_id) {
			$elem_num = 1;
			$msg->id = $ent_id;
			
			while (strlen($structure) > 0) {
				$structure = trim($structure);
				$char = substr($structure, 0, 1);
	
				if (strtolower(substr($structure, 0, 3)) == "nil") {
					$text = "";
					$structure = substr($structure, 3);
				} else if ($char == "\"") {
					// loop through until we find the matching quote, and return that as a string
					$pos = 1;
					$char = substr($structure, $pos, 1);
					while ($char != "\"" && $pos < strlen($structure)) {
						$text .= $char;
						$pos++;
						$char = substr($structure, $pos, 1);
					}	
					$structure = substr($structure, strlen($text) + 2);
				} else if ($char == "(") {
					// comment me
					$end = $this->match_parenthesis (0, $structure);
					$sub = substr($structure, 1, $end-1);
					$this->get_props($properties, $sub);
					$structure = substr($structure, strlen($sub) + 2);
				} else {
					// loop through until we find a space or an end parenthesis
					$pos = 0;
					$char = substr($structure, $pos, 1);
					while ($char != " " && $char != ")" && $pos < strlen($structure)) {
						$text .= $char;
						$pos++;
						$char = substr($structure, $pos, 1);
					}
					$structure = substr($structure, strlen($text));
				}
				if ($this->debug_mime) echo "<tt>$elem_num : $text</tt><br>";
	
				// This is where all the text parts get put into the header
				switch ($elem_num) {
					case 1: 
						$msg->type = strtolower($text);
						if ($this->debug_mime) echo "<tt>type = ".strtolower($text)."</tt><br>";
						break;
					case 2: 
						$msg->subtype = strtolower($text);
						if ($this->debug_mime) echo "<tt>subtype = ".strtolower($text)."</tt><br>";
						break;
					case 5:
						$msg->description = $text;
						if ($this->debug_mime) echo "<tt>description = $text</tt><br>";
						break;
					case 6:
						$msg->encoding = strtolower($text);
						if ($this->debug_mime) echo "<tt>encoding = ".strtolower($text)."</tt><br>";
						break;
					case 7:
						$msg->num_bytes = $text;
						if ($this->debug_mime) echo "<tt>num_bytes = $text</tt><br>";
						break;
					default:
						if ($msg->type == "text" && $elem_num == 8) {
							// This is a plain text message, so lets get the number of lines
							// that it contains.
							$msg->num_lines = $text;
							if ($this->debug_mime) echo "<tt>num_lines = $text</tt><br>";
	
						} else if ($msg->type == "message" && $msg->subtype == "rfc822" && $elem_num == 8) {
							// This is an encapsulated message, so lets start all over again and 
							// parse this message adding it on to the existing one.
							$structure = trim($structure);
							if (substr($structure, 0, 1) == "(") {
								$e = $this->match_parenthesis (0, $structure);
								$structure = substr($structure, 0, $e);
								$structure = substr($structure, 1);
								$m = $this->parse_structure($structure, $msg->entity_id);
								
								// the following conditional is there to correct a bug that wasn't
								// incrementing the entity IDs correctly because of the special case
								// that message/rfc822 is.  This fixes it fine.
								if (substr($structure, 1, 1) != "(") 
									$m->entity_id = $this->increment_id($this->new_element_level($ent_id));
									
								// Now we'll go through and reformat the results.
								if ($m->parts) {
									for ($i=0; $i < count($m->parts); $i++) {
										$msg->addEntity($m->parts[$i]);
									}
								} else {
									$msg->addEntity($m);
								}
								$structure = ""; 
							}
						}
						break;
				}
				$elem_num++;
				$text = "";
			}
			// loop through the additional properties and put those in the various headers
			if ($msg->type != "message") {
				for ($i=0; $i < count($properties); $i++) {
					$msg->parameters[(count($msg->parameters))] = $properties[$i];
					if ($this->debug_mime) echo "<tt>".$properties[$i]->attribute." = " . $properties[$i]->value . "</tt><br>";
				}
			}
	
			return $msg;
		}

		// This gets properties in a nested parenthesisized list.  For example,
		// this would get passed something like:  ("attachment" ("filename" "luke.tar.gz"))
		// This returns an array called $props with all paired up properties.
		// It ignores the "attachment" for now, maybe that should change later 
		// down the road.  In this case, what is returned is:
		function get_props (&$props, $structure) {
			while (strlen($structure) > 0) {
				$structure = trim($structure);
				$char = substr($structure, 0, 1);
	
				if ($char == "\"") {
					$pos = 1;
					$char = substr($structure, $pos, 1);
					while ($char != "\"" && $pos < strlen($structure)) {
						$tmp .= $char;
						$pos++;
						$char = substr($structure, $pos, 1);
					}	
					$structure = trim(substr($structure, strlen($tmp) + 2));
					$char = substr($structure, 0, 1);
	
					if ($char == "\"") {
						$pos = 1;
						$char = substr($structure, $pos, 1);
						while ($char != "\"" && $pos < strlen($structure)) {
							$value .= $char;
							$pos++;
							$char = substr($structure, $pos, 1);
						}	
						$structure = trim(substr($structure, strlen($tmp) + 2));
						
						$k = count($props);
						$props[$k] = new Property(strtolower($tmp), $value);
					} else if ($char == "(") {
						$end = $this->match_parenthesis (0, $structure);
						$sub = substr($structure, 1, $end-1);
						$this->get_props($props, $sub);
						$structure = substr($structure, strlen($sub) + 2);
					}
					return $props;
				} else if ($char == "(") {
					$end = $this->match_parenthesis (0, $structure);
					$sub = substr($structure, 1, $end-1);
					$this->get_props($props, $sub);
					$structure = substr($structure, strlen($sub) + 2);
					return $props;
				} else {
					return $props;
				}
			}
		}
	} 

	class Folder {
		var $name;
		var $full_name;
		var $path;
		var $delimiter;
		var $attributes;
	}

	class Status {
		var $name, $num_msgs=0, $num_recent=0, $num_unread=0;
	}

	class BodyStructure {
		var $type, $subtype, $encoding, $description, $id, $num_lines, $num_bytes;
		var $disposition, $parameters, $parts;
		
		function addEntity ($msg) {
			$this->parts[count($this->parts)] = $msg;
		}
	}

	class Property {
		var $attribute, $value;
		function Property ($a, $v) {
			$this->attribute = $a;
			$this->value = $v;
		}
	}

	class SmallHeader {
		var $id, $subject, $date, $from, $size, $flags; 
	}

	class FullHeader {
		var $to_address, $to;
		var $from_address, $from;
		var $cc_address, $cc;
		var $bcc_address, $bcc;
		var $replay_to_address, $reply_to;
		var $sender_address, $sender;
		var $return_path_address, $return_path;
		var $date, $subject, $id, $num_bytes;
	}

	class AddressLine {
		var $personal, $adl, $mailbox, $host;
	}
?>

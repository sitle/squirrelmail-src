<?php
   /*
    *  Message and Spam Filter Plugin 
    *  By Luke Ehresman <luke@squirrelmail.org>
    *     Tyler Akins <tyler@boas.anthro.mnsu.edu>  
    *  (c) 2000 (GNU GPL - see ../../COPYING)
    *
    *  This plugin filters your inbox into different folders based upon given
    *  criteria.  It is most useful for people who are subscibed to mailing lists
    *  to help organize their messages.  The argument stands that filtering is
    *  not the place of the client, which is why this has been made a plugin for
    *  SquirrelMail.  You may be better off using products such as Sieve or
    *  Procmail to do your filtering so it happens even when SquirrelMail isn't
    *  running.
    *
    *  If you need help with this, or see improvements that can be made, please
    *  email me directly at the address above.  I definately welcome suggestions
    *  and comments.  This plugin, as is the case with all SquirrelMail plugins,
    *  is not directly supported by the developers.  Please come to me off the
    *  mailing list if you have trouble with it.
    *
    *  Also view plugins/README.plugins for more information.
    *
    */

   function start_filters() {
      global $username, $key, $imapServerAddress, $imapPort, $imap, $imap_general, $filters;
      global $imap_stream, $imapConnection;

      // Detect if we have already connected to IMAP or not.
      if (!$imap_stream && !$imapConnection) {
         $imap_stream = sqimap_login($username, $key, $imapServerAddress, $imapPort, 10);
         $previously_connected = false;
      } else if ($imapConnection) {
         $imap_stream = $imapConnection;
         $previously_connected = true;
      } else {
         $previously_connected = true;
      }

      // Filter spam from inbox before we sort them into folders
      spam_filters($imap_stream);
      user_filters($imap_stream);
      
      if (!$previously_connected)
         sqimap_logout($imap_stream);
   }

   function user_filters($imap_stream) {
      $filters = load_filters();
      if (! $filters) return;
      
      sqimap_mailbox_select($imap_stream, "INBOX");
      for ($i=0; $i < count($filters); $i++) {
         if ($filters[$i]["where"] == "To or Cc") {
            /*
             *  If it's "TO OR CC", we have to do two searches, one for TO
             *  and the other for CC.  This probably could be made into 
             *  slimmer code, but I doubt any performance boost would be
             *  noticed.  I left it this way for readability.
             */
            fputs ($imap_stream, "a00$i SEARCH ALL TO \"" . $filters[$i]["what"] . "\"\r\n");
            $read = sqimap_read_data ($imap_stream, "a00$i", true, $response, $message);
            for ($r=0; $r < count($read) && substr($read[$r], 0, 8) != "* SEARCH"; $r++) {}
            if ($response == "OK") {
               $ids = explode(" ", $read[$r]);
               for ($j=2; $j < count($ids); $j++) {
                  if (sqimap_mailbox_exists ($imap_stream, $filters[$i]["folder"])) {
                     sqimap_messages_copy ($imap_stream, trim($ids[$j]), trim($ids[$j]), $filters[$i]["folder"]);
                     sqimap_messages_flag ($imap_stream, trim($ids[$j]), trim($ids[$j]), "Deleted");
                  }
               }
            }
            fputs ($imap_stream, "a00$i SEARCH ALL CC \"" . $filters[$i]["what"] . "\"\r\n");
            $read = sqimap_read_data ($imap_stream, "a00$i", true, $response, $message);
            for ($r=0; $r < count($read) && substr($read[$r], 0, 8) != "* SEARCH"; $r++) {}
            if ($response == "OK") {
               $ids = explode(" ", $read[$r]);
               for ($j=2; $j < count($ids); $j++) {
                  if (sqimap_mailbox_exists ($imap_stream, $filters[$i]["folder"])) {
                     sqimap_messages_copy ($imap_stream, trim($ids[$j]), trim($ids[$j]), $filters[$i]["folder"]);
                     sqimap_messages_flag ($imap_stream, trim($ids[$j]), trim($ids[$j]), "Deleted");
                  }
               }
            }
         } else {
            /*
             *  If it's a normal TO, CC, SUBJECT, or FROM, then handle it normally.
             */
            fputs ($imap_stream, "a00$i SEARCH ALL " . $filters[$i]["where"] . " \"" . $filters[$i]["what"] . "\"\r\n");
            $read = sqimap_read_data ($imap_stream, "a00$i", true, $response, $message);
            for ($r=0; $r < count($read) && substr($read[$r], 0, 8) != "* SEARCH"; $r++) {}
            if ($response == "OK") {
               $ids = explode(" ", $read[$r]);
               for ($j=2; $j < count($ids); $j++) {
                  if (sqimap_mailbox_exists ($imap_stream, $filters[$i]["folder"])) {
                     sqimap_messages_copy ($imap_stream, trim($ids[$j]), trim($ids[$j]), $filters[$i]["folder"]);
                     sqimap_messages_flag ($imap_stream, trim($ids[$j]), trim($ids[$j]), "Deleted");
                  }
               }
            }
         }
      }
      sqimap_mailbox_expunge($imap_stream, "INBOX");
   }   

   function spam_filters($imap_stream) {
      global $data_dir, $username;

      $filters_spam_scan = getPref($data_dir, $username, "filters_spam_scan");
      $filters_spam_folder = getPref($data_dir, $username, "filters_spam_folder");
      $filters = load_spam_filters();
      
      $run = 0;
      
      foreach ($filters as $Key=> $Value)
      {
          if ($Value['enabled'])
              $run ++;
      }
      
      if ($run == 0)
      {
          return;
      }
      
      sqimap_mailbox_select($imap_stream, "INBOX");
      
      fputs($imap_stream, "A3999 FETCH 1:* (FLAGS BODY.PEEK[HEADER.FIELDS (RECEIVED)])\r\n");
      
      $read = sqimap_read_data ($imap_stream, 'A3999', true, $response, $message);
      
      if ($response != 'OK')
          return;

      //return;

      $i = 0;
      while ($i < count($read))
      {
          $Chunks = split(' ', $read[$i]);
          if ($Chunks[0] != '*')
          {
              $i ++;
              next;
          }
          $MsgNum = $Chunks[1];
          
          $i ++;
          $IsSpam = 0;
          $Scan = 1;
          
          if ($filters_spam_scan == 'new')
          {
              if (is_int(strpos($Chunks[4], '\Seen')))
              {
                  $Scan = 0;
              }
          }

          while ($read[$i][0] != ')')
          {
              if ($Scan)
              {
                  $read[$i] = ereg_replace('[^0-9\.]', ' ', $read[$i]);
                  $elements = split(' ', $read[$i]);
                  foreach ($elements as $value)
                  {
                      if (ereg('([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})', $value, $regs))
                      {
                          if (filters_spam_check_site($regs[1], $regs[2], $regs[3], $regs[4], $filters))
                          {
                              $IsSpam ++;
                          }
                      }
                  }
              }
              $i ++;
          }
          $i ++;
          
          if ($IsSpam)
          {
              if (sqimap_mailbox_exists ($imap_stream, $filters_spam_folder)) 
              {
                  sqimap_messages_copy ($imap_stream, $MsgNum, $MsgNum, $filters_spam_folder);
                  sqimap_messages_flag ($imap_stream, $MsgNum, $MsgNum, 'Deleted');
              }
          }
      }
      
      sqimap_mailbox_expunge($imap_stream, "INBOX");
   }   

   function filters_spam_check_site($a, $b, $c, $d, &$filters)
   {
      foreach ($filters as $key => $value)
      {
          if ($filters[$key]['enabled'])
          {
              if ($filters[$key]['dns'])
              {
                  if (checkdnsrr("$d.$c.$b.$a." . $filters[$key]['dns'], 'ANY'))
                  {
                      return 1;
                  }
              }
          }
      }
      return 0;
   }
   
   function load_filters() {
      global $data_dir, $username;
      $filters = array();
      for ($i=0; $fltr = getPref($data_dir, $username, "filter$i"); $i++) {
         $ary = explode(",", $fltr);
         $filters[$i]["where"] = $ary[0];
         $filters[$i]["what"] = $ary[1];
         $filters[$i]["folder"] = $ary[2];
      }
      return $filters;
   }

   function load_spam_filters() {
      global $data_dir, $username;
      
      $filters['MAPS RBL']['prefname'] = 'filters_spam_maps_rbl';
      $filters['MAPS RBL']['name'] = 'MAPS Realtime Blackhole List';
      $filters['MAPS RBL']['link'] = 'http://www.mail-abuse.org/rbl/';
      $filters['MAPS RBL']['dns'] = 'rbl.maps.vix.com';
      $filters['MAPS RBL']['comment'] = 
'This list contains servers that are verified spam senders.
It is a pretty reliable list to scan spam from.';
      
      $filters['MAPS RSS']['prefname'] = 'filters_spam_maps_rss';
      $filters['MAPS RSS']['name'] = 'MAPS Relay Spam Stopper';
      $filters['MAPS RSS']['link'] = 'http://www.mail-abuse.org/rss/';
      $filters['MAPS RSS']['dns'] = 'relays.mail-abuse.org';
      $filters['MAPS RSS']['comment'] =
'Servers that are configured (or misconfigured) to allow spam to be relayed 
through their system will be banned with this.  Another good one to use.';

      $filters['MAPS DUL']['prefname'] = 'filters_spam_maps_dul';
      $filters['MAPS DUL']['name'] = 'MAPS Dial-Up List';
      $filters['MAPS DUL']['link'] = 'http://www.mail-abuse.org/dul/';
      $filters['MAPS DUL']['dns'] = 'dul.maps.vix.com';
      $filters['MAPS DUL']['comment'] =
'Dial-up users are often filtered out since they should use their ISP\'s 
mail servers to send mail.  Spammers typically get a dial-up account and 
send spam directly from there.';

      $filters['ORBS']['prefname'] = 'filters_spam_orbs';
      $filters['ORBS']['name'] = 'Open Relay Blackhole System';
      $filters['ORBS']['link'] = 'http://www.orbs.org/';
      $filters['ORBS']['dns'] = 'relays.orbs.org';
      $filters['ORBS']['comment'] =
'A list of systems that are reported to be open relays, but may not have 
had spam filter through their systems yet.  A lot of false positives can
be found when using this filter, but you\'re pretty certain to not get any
spam.  Use with caution.';
      
      foreach ($filters as $Key => $Value)
      {
          $filters[$Key]['enabled'] = getPref($data_dir, $username,
              $filters[$Key]['prefname']);
      }
      
      return $filters;
   }

   function remove_filter ($id) {
      global $data_dir, $username;
      $filename = "$data_dir$username.pref";
      $file = fopen($filename, "r");
      for ($i=0; !feof($file); $i++) {
         $pref[$i] = fgets($file, 1024);
         if (substr($pref[$i], 0, strpos($pref[$i], "=")) == $string) {
            $i--;
         }
      }
      fclose($file);

      for ($i=0,$j=0,$q=0; $i < count($pref); $i++) {
         if (substr($pref[$i], 0, 6) == "filter") {
            if ($j != $id) {
               $fltr[$q] = substr($pref[$i], strpos($pref[$i], "=")+1);
               $q++;
            }
            $j++;
         }
      }

      $file = fopen($filename, "w");
      for ($i=0; $i < count($pref); $i++) {
         if (substr($pref[$i], 0, 6) != "filter") {
            fwrite($file, "$pref[$i]", 1024);
         }  
      }
      for ($i=0; $i < count($fltr); $i++) {
         fwrite($file, "filter$i=$fltr[$i]");
      }       
      fclose ($file);
   }
?>

<?php
   /*
    *  Mail Fetch
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

   include ("../plugins/mail_fetch/class.POP3.php3");   
   include ("../src/load_prefs.php");

function Status($msg)
{
    echo htmlspecialchars($msg) . "<br>\n";
    flush();
}


   displayPageHeader($color, "None");   

   $pop3 = new POP3($mailfetch_server, 60);

   echo "<h1>Fetching from $mailfetch_server</h1>\n";
   echo "<p>";

   if (!$pop3->connect($mailfetch_server)) {
      Status("Ooops,  $pop3->ERROR");
      exit;
   }
   
   Status("Opening IMAP server");
   $imap_stream = sqimap_login($username, $key, $imapServerAddress, $imapPort, 10);

   Status("Opening POP server");
   $Count = $pop3->login($mailfetch_user, $mailfetch_pass);
   if( (!$Count) || ($Count == -1)) {
     Status("Login Failed: $pop3->ERROR"); 
     exit;
   }

//   register_shutdown_function($pop3->quit());

  $msglist = $pop3->uidl();

  $i = 1;
  for ($j = 1; $j < sizeof($msglist); $j++) {
    if ($msglist["$j"] == $mailfetch_uidl) {
      $i = $j+1;
      break;
    }
  }

  if ($Count < $i) {
    Status("Login OK: No new messages");
    $pop3->quit();
    exit;
  }
  if ($Count == 0) {
    Status("Login OK: Inbox EMPTY");
    $pop3->quit();
    exit;
  } else {
    $newmsgcount = $Count - $i + 1;
    Status("Login OK: Inbox contains [$newmsgcount] messages");
  }

  Status("Fetching UIDL...");
  // Faster to get them all at once
  $mailfetch_uidl = $pop3->uidl();

  // Shouldn't this be changed to say something like this?  We really
  // don't care if UIDL is supported if we are going to delete the
  // messages anyway, right?
  //
  // if (! is_array($mailfetch_uidl) && $mailfetch_lmos == 'on')
  // {
  //    Status("Server does not support UIDL.");
  // }
  //
  // Just in case I'm wrong ...
  
  if (! is_array($mailfetch_uidl) && $mailfetch_lmos != "on") {
      Status("Server does not supprt UIDL, Leaving messages on server.");
      $mailfetch_lmos = "on";
  }
  if ($mailfetch_lmos == "on")
  {
    Status("Leaving Mail on Server...");
  }
  else
  {
    Status("Deleting messages from server...");
  }
 
  for (; $i <= $Count; $i++) {
    Status("Fetching message $i");
    $Message = "";
    $MessArray = $pop3->get($i);

    if ( (!$MessArray) or (gettype($MessArray) != "array")) {
      Status("oops, $pop3->ERROR");
      exit;
    }

     while (list($lineNum, $line) = each ($MessArray)) {
       $Message .= $line;
     }
     fputs($imap_stream, "A3$i APPEND INBOX {" . (strlen($Message) - 1) . "}\r\n");
     $Line = fgets($imap_stream, 1024);
     if (substr($Line, 0, 1) == '+')
     {
       fputs($imap_stream, $Message);
       sqimap_read_data($imap_stream, "A3$i", false, $response, $message);
       Status("Message appended to mailbox");
 
       if ($mailfetch_lmos != "on") {
         $pop3->delete($i);
         Status("Message deleted from Remote Server!");
       }
     } else {
       echo "$Line";
       Status("Error Appending Message!");
     }
   }   

   Status("Closing POP");
   Status("Logging out from IMAP");
   sqimap_logout($imap_stream);
   if (is_array($mailfetch_uidl))
   {
       Status("Saving UIDL");
       setPref($data_dir,$username,"mailfetch_uidl",
           array_pop($mailfetch_uidl));
   }

   Status("Done");
   echo "</p>";

?>

<?php
/*
 *  notify.php - reminder notification script
 *
 *  Copyright (c) 2002 Jeff Hinrichs <jeffh@delasco.com>
 *  Licensed under the GNU GPL. For full terms see the file COPYING.
 * 
 *  Reminder (*.rem) file format:
 *  notifyTime|eventTime|Length|Priority|Title|Body
 *
 */

// Include plugin config data
include_once('config.php');
include_once('../../config/config.php');

// Figure out if we were run remotely
if ($HTTP_SERVER_VARS["REMOTE_ADDR"] == $HTTP_SERVER_VARS["SERVER_ADDR"]){
  $ranRemotely = 0;
} else {
  $ranRemotely = 1;
};
// If remote request and remote disallowed then die
if ($ranRemotely){
  if ($allowRemote==0){ 
    die("Remote Access Not Allowed");
  };
};
// Should debug output be listed
$debug = 0;	//assume NO
if ($ranRemotely){
  $debug = $remoteDebug;
} else {
  $debug = $localDebug;
};

// Figure out what time and year we are in
$hostTime = date('YmdHi');
$year = substr($hostTime,0,4);

function sendReminder($user,$line,$domain,$fromName,$fromAcct,$replyName,$replyAcct){
// Extract Data from $line and notify user by email
  list($notifyTime,$eventTime,$Length,$Priority,$Title,$Body) = split('[|]',$line);
  $evtDate = substr($eventTime,4,2)."/".substr($eventTime,6,2)."/".substr($eventTime,0,4);
  $evtTime = substr($eventTime,8,2).":".substr($eventTime,10,2);

  $to       = $user."@".$domain;

  $subject  = "[Reminder] $Title";

  $message .= "Event: $Title\r\n\r\n";
  $message .= "Event Date: $evtDate\r\n";
  $message .= "Event Time: $evtTime\r\n\r\n";
  $message .= "Event Desc: \r\n$Body\r\n\r\n";

  $headers .= "From: ".$fromName ." <".$fromAcct."@".$domain.">\r\n";
  $headers .= "Reply-To: ".$replyName." <".$replyAcct."@".$domain.">\r\n";
  $headers .= "X-Notify.php: SquirrelMail-1.2.7 notify.php-1.3\r\n";

  // send the notification via email
  $success  = mail( $to, $subject, $message, $headers );

};
if ($debug){
  echo "<b>Settings</b><br>\n";
  echo 'REMOTE_ADDR: '. $HTTP_SERVER_VARS["REMOTE_ADDR"]."<br>\n";
  echo 'SERVER_ADDR: '. $HTTP_SERVER_VARS["SERVER_ADDR"]."<br>\n";
  echo 'ranRemotely: '. $ranRemotely."<br>\n";
  echo 'localDebug : '. $localDebug."<br>\n";
  echo 'remoteDebug: '. $remoteDebug."<br>\n";
  echo 'allowRemote: '. $allowRemote."<br>\n";
  echo 'debug      : '. $debug."<br>\n";
  echo "Send from: \"$fromName\" &lt;$fromAcct@$domain&gt;<br>\n";
  echo "Reply To : \"$replyName\" &lt;$replyAcct@$domain&gt;<br>\n";
  echo "Host Time: $hostTime<br>\n";
  echo "<hr>\n";
};
//Loop through files in data_dir
// when a .rem file for the current year is found, process it
if ($dir = @opendir($data_dir)) {
  while (($file = readdir($dir)) !== false) {
    if (stristr($file,".$year.rem") ){
      if ($debug) { echo "$file<br>\n"; };
      $fcontents = file ($data_dir .$file);
      // Sort from oldest to newest so we have to scan the fewest entries
      sort($fcontents);
      reset($fcontents);
      // For each entry in file
      while (list ($line_num, $line) = each ($fcontents)) {

        if ($debug){ echo "<li>", htmlspecialchars ($line), "<br>\n"; };

        // If notifyTime > now then break out of loop
        if (substr($line,0,12)>$hostTime){break;};

        // If notifyTime = now, then let them know
        if (substr($line,0,12)==$hostTime){
	  if ($debug){ echo "event occurs<br>"; };
          $user = substr($file,0,strpos($file,"."));
          sendReminder($user,$line,$domain,$fromName,$fromAcct,$replyName,$replyAcct);
 	};
     };
  };
}  
  closedir($dir);
};
?>

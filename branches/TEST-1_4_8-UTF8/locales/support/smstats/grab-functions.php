<?php

////////////////////////////////////////////////////////////////////////////
// $Id$
//
// Description: common functions for GUI statistic grabbing program
//
////////////////////////////////////////////////////////////////////////////


//
// array callback for replacing a special element
//
function my_replace($n) {
	global $kdefake;
	
	if ($n=="kdefake") {
		return $kdefake;
	} else {
		return $n;
	}
}


//
// send email for success operation
//
function send_ok($message="") {
  global $rev, $adminemail, $prog;

  mail($adminemail,"OK $prog ($rev)","$message\n");
}

//
// send email for failed operation
//
function send_err($message="") {
  global $rev, $adminemail, $prog;

//  mail($adminemail,"ERROR $prog ($rev)","$message\n");
  echo "ERROR $prog ($rev): $message\n";
}


//
// display debug message to STDOUT according with debug level
//
function debug($level=0,$message="") {
  global $debug;
    
  if ($level <= $debug) {
    echo $message ."\n";
  }
}
              

//
// open connection to MySQL server for named database
//
function initdb($host,$user,$pass,$db) {
  if ($conn_handler = @mysql_connect($host,$user,$pass)) {
    if (@mysql_select_db($db,$conn_handler)) {
      return $conn_handler;
    } else {
      send_err("Cannot select '$db' database!");
      exit();
    }
  } else {
    send_err("Cannot connect to SQL server!");
    exit();
  }
}

//
// close database connection
//                                
function closedb($conn_handler) {
  if (@mysql_close($conn_handler)) {
    return ;
  } else {
    send_err("Cannot close SQL server connection!");
    exit();
  }
}

//
// get statistics from PO file
//
function getstats($file) {
  global $msgfmt;
  
  $translated=0;
  $untranslated=0;
  $fuzzy=0;
  $total=0;
  $error=0;
  
  $escfile=escapeshellarg($file);
  @exec("$msgfmt -o /dev/null --statistics $escfile 2>&1",$output,$ret);

  if ($ret==0) {
    if (preg_match("/t$/",$file)) {
      if (preg_match("/^\s*(\d+)\s*translated[^\d]+(\d+)\s*fuzzy[^\d]+(\d+)\s*untranslated/",$output[0],$m)) {
      } else if (preg_match("/^\s*(\d+)\s*translated[^\d]+(\d+)\s*untranslated[^\d]/",$output[0],$m)) {
        $m[3]=$m[2];
        $m[2]=0;
      }
      $translated   = $m[1]+0;
      $fuzzy        = $m[2]+0;
      $untranslated = $m[3]+0;
      // ugly hack for POTs ;)
      if ($fuzzy==1) {
        $fuzzy=0;
      }
    } else {
      // new version of msgfmt make life harder :-/
      if (preg_match("/^\s*(\d+)\s*translated[^\d]+(\d+)\s*fuzzy[^\d]+(\d+)\s*untranslated/",$output[0],$m)) {
      } else if (preg_match("/^\s*(\d+)\s*translated[^\d]+(\d+)\s*fuzzy[^\d]/",$output[0],$m)) {
      } else if (preg_match("/^\s*(\d+)\s*translated[^\d]+(\d+)\s*untranslated[^\d]/",$output[0],$m)) {
        $m[3]=$m[2];
        $m[2]=0;
      } else if (preg_match("/^\s*(\d+)\s*translated[^\d]+/",$output[0],$m)) {
      }
      $translated   = $m[1]+0;
      $fuzzy        = $m[2]+0;
      $untranslated = $m[3]+0;
    }
  } else {
    $error=1;
  }

  return array($error,$translated,$fuzzy,$untranslated);
}


//
// return asociative array with ISO language codes and team long names
//
function get_config1($file) {
  if (!is_readable($file)) return array();
  $lines=@file($file);
  $vector=array();
  for($i=0;$i<count($lines);$i++) {
    if (preg_match('/^\s*$/',$lines[$i])) continue;
    if (preg_match('/^\s*#/',$lines[$i])) continue;
    list($lang,$name) = explode("=",$lines[$i]);
    $lang=trim($lang);
    $name=trim($name);
    $vector[$lang]=$name;
  }
  return $vector;
}
                                    
//
// return asociative array with filenames/packages and percentages
//
function get_config2($file) {
  if (!is_readable($file)) return array();
  $lines=@file($file);
  $vector=array();
  for($i=0;$i<count($lines);$i++) {
    if (preg_match('/^\s*$/',$lines[$i])) continue;
    if (preg_match('/^\s*#/',$lines[$i])) continue;
		preg_match('/^\s*([^\s]+)\s+([^\s]+)/',$lines[$i],$m);
    array_push($vector,trim($lines[$i]));
  }
  return $vector;
}
                                    

?>

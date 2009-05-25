<?php

////////////////////////////////////////////////////////////////////////////
// $Id$
//
// Description: common functions for statistics cleanup
//
////////////////////////////////////////////////////////////////////////////

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

  mail($adminemail,"ERROR $prog ($rev)","$message\n");
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

?>

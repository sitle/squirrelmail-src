#!/usr/bin/php -q
<?php

////////////////////////////////////////////////////////////////////////////
// $Id$
//
// Description: statistics cleanup
//
////////////////////////////////////////////////////////////////////////////

include("cleanup-functions.php");
include("cleanup-config.php");


// ************* PRELIMINARY TASKS, DATA INIT *************
set_time_limit(0);

// get for which revision we generate stats
if ($argv[1]=="") {
  $rev="HEAD";
} else {
  $rev=$argv[1];
};

// open database connection
$dbh=initdb($sql_host,$sql_user,$sql_pass,$sql_db);

debug(10,"cleanup statistics");
$res=@mysql_query("DELETE FROM essential WHERE rev='$rev' AND " .
                  "sdate < DATE_SUB(NOW(),INTERVAL '60' DAY)"
                  ,$dbh);
if (!$res) {                  
  send_err("SQL error: delete by sdate for $rev branch.");
  die();
}

$deleted=@mysql_affected_rows($dbh);
debug(10,"deleted $deleted records for $rev branch");

closedb($dbh);

// send_ok("Deleted $deleted records for $rev branch.");

?>

#!/usr/bin/php -q
<?php

////////////////////////////////////////////////////////////////////////////
// $Id$
//
// Description: GUI messages history statistics rendering
//
////////////////////////////////////////////////////////////////////////////



include("includes/jpgraph.php");
include("includes/jpgraph_line.php");
include("history-functions.php");
include("history-config.php");


// ************* PRELIMINARY TASKS, DATA INIT *************
set_time_limit(0);

// get for which revision we generate stats
if ($argv[1]=="") {
  $rev="HEAD";
} else {
  $rev=$argv[1];
};

$outbyrev1     = "$outdir/$rev/essential.png";
$outbyrev2     = "$outdir/$rev/essential-big.png";
$outbypackage  = "$outdir/$rev/packages.php";
$outfullinfo   = "$outdir/$rev/fullinfo.php";
$outtop10      = "$outdir/$rev/top10.php";
$outessential  = "$outdir/$rev/essential.php";
$outgeneral    = "$outdir/$rev/general.php";


// preliminary checks
if (!is_dir($outdir)) {
  send_err("Cannot acces $outdir directory!");
  exit();
}

// data initialization
$rundate = date("Y-m-d H:i",mktime());

// open database connection
$dbh=initdb($sql_host,$sql_user,$sql_pass,$sql_db);

// get last day with statistic data
debug(10,"getting last day with statistic data");
$res=@mysql_query("SELECT DISTINCT sdate FROM essential " .
                    "ORDER BY sdate DESC LIMIT 0,1"
                    ,$dbh);
if (!$res) {
  send_err("SQL error: get date from esential");
  exit();
}
$row=mysql_fetch_row($res);
$currdate=$row[0];



// ************* DATA PROCESSING *************
debug(10,"getting team codes");
$m_teams=array();
$res=@mysql_query("SELECT teamcode,teamname FROM teams WHERE rev='$rev' ORDER by teamcode",$dbh);
if (!$res) {
  send_err("SQL error: teams");
  exit();
}
while ($row=mysql_fetch_array($res)) {
  $m_teams[$row['teamcode']]=$row['teamname'];
}

debug(10,"getting packages");
$m_packages=array();
$res=@mysql_query("SELECT package FROM sum WHERE rev='$rev' GROUP BY package ORDER by package",$dbh);
if (!$res) {
  send_err("SQL error: group by package");
  exit();
}
while ($row=@mysql_fetch_row($res)) {
  array_push($m_packages,$row[0]);
}

// make dirpath for $rev
if (!is_dir("$outdir/$rev")) {
  if (!@mkdir("$outdir/$rev",0755)) {
    send_err("Cannot make $outdir/$rev directory!");
    exit();
  }
}

make_historybyrev($outbyrev1,$outbyrev2);


// render historic graphics by team for level 2
foreach ($m_teams as $teamcode => $teamname) {
  if ($teamcode=="templates") continue;
  debug(10,"render historic graphics for '$teamcode' team");
  
  // make dirpath for $rev & current teamcode
  if (!is_dir("$outdir/$rev/$teamcode")) {
    if (!@mkdir("$outdir/$rev/$teamcode",0755)) {
      send_err("Cannot make $outdir/$rev/$teamcode directory!");
      exit();
    }
  }
  
	make_historybyteam($teamcode);
}

closedb($dbh);

// send_ok("Success generating history graphs for $rev branch.");

?>

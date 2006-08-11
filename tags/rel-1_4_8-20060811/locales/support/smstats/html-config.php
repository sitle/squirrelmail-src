<?php

////////////////////////////////////////////////////////////////////////////
// $Id$
//
// Description: configuration file for GUI messages statistic program
//
////////////////////////////////////////////////////////////////////////////


// the output directory
$outdir= "/home/tomas/smstats-work/www";
// the base URL to KDE webcvs for HEAD branch
$cvswebformat1  = "/files/%s/locales/%s.gz";
// the base URL to KDE webcvs for branch HEAD with tags
$cvswebformat2  = "/files/%s/locales/%s.gz";


// thematic options
$okcolor1 ="#f0f0ff";
$okcolor2 ="#d0e0ff";
$okcolor3 ="#f0f8f0";
$okcolor4 ="#d0f8e0";
// cell table backgrounds
$okbg="#ffffff";
$okbg2="#f0f0f0";
$errorbg="#ffd8d0";
$errorbg2="#ffc8c0";
$nopotbg="#e0f0ff";
$nopotbg2="#d0e0f0";

// development options
$debug=0;


$prog="html-guistats";
$adminemail="tomas@topolis.lt";


// MySQL connection data
$sql_user   = "smstats";
$sql_pass   = "";
$sql_host   = "localhost";
$sql_db     = "smstats";

?>

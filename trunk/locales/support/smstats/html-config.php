<?php

////////////////////////////////////////////////////////////////////////////
// $Id$
//
// Description: configuration file for GUI messages statistic program
//
////////////////////////////////////////////////////////////////////////////


// the output directory
$outdir= "/home/httpd/i18n.kde.org/stats/gui";
// the base URL to KDE webcvs for HEAD branch
$cvswebformat1  = "http://webcvs.kde.org/cgi-bin/cvsweb.cgi/kde-i18n/%s?rev=HEAD&content-type=text/plain";
// the base URL to KDE webcvs for branch HEAD with tags
$cvswebformat2  = "http://webcvs.kde.org/cgi-bin/cvsweb.cgi/kde-i18n/%s?rev=%s&content-type=text/plain";



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
$debug=100;


$prog="html-guistats";
$adminemail="admin@i18n.kde.org";


// MySQL connection data
$sql_user   = "gui_www";
$sql_pass   = "";
$sql_host   = "127.0.0.1";
$sql_db     = "guistats";

?>

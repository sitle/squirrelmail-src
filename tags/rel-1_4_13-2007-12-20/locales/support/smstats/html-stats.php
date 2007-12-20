#!/usr/bin/php -q
<?php

////////////////////////////////////////////////////////////////////////////
// $Id$
//
// Description: GUI messages statistics HTML rendering
//
////////////////////////////////////////////////////////////////////////////


dl('mysql.so');
include("includes/fasttemplate.php");
include("html-functions.php");
include("html-config.php");


// ************* PRELIMINARY TASKS, DATA INIT *************
set_time_limit(0);

// get for which revision we generate stats
if ($argv[1]=="") {
  $rev="HEAD";
} else {
  $rev=$argv[1];
};

$outbyteam     = "$outdir/$rev/index.php";
$outbypackage  = "$outdir/$rev/packages.php";
$outfullinfo   = "$outdir/$rev/fullinfo.php";
$outtop        = "$outdir/$rev/top.php";
$outessential  = "$outdir/$rev/essential.php";
$outgeneral    = "$outdir/$rev/general.php";
$outpartial    = "$outdir/$rev/partial/index.php";


// preliminary checks
if (!is_dir($outdir)) {
  die("Cannot acces $outdir directory!");
}

// data initialization
$rundate = date("Y-m-d H:i",mktime());

// open database connection
$dbh=initdb($sql_host,$sql_user,$sql_pass,$sql_db);

// get last day with statistic data
debug(10,"getting last day with statistic data");
$result=@mysql_query("SELECT DISTINCT sdate FROM essential " .
                    "ORDER BY sdate DESC LIMIT 0,1"
                    ,$dbh) or die("SQL error: get date from esential");
$row=mysql_fetch_row($result);
$currdate=$row[0];


$tplt2 = new FastTemplate("./templates/");
$tplt3 = new FastTemplate("./templates/");
$tplp2 = new FastTemplate("./templates/");
$tplp3 = new FastTemplate("./templates/");
$tplp4 = new FastTemplate("./templates/");

$tplt2->define(array("level2byteam"    => "level2byteam.tpl"));
$tplt3->define(array("level3byteam"    => "level3byteam.tpl"));
$tplp2->define(array("level2bypackage" => "level2bypackage.tpl"));
$tplp3->define(array("level3bypackage" => "level3bypackage.tpl"));
$tplp4->define(array("level2partial"   => "level2partial.tpl"));

$tplt2->assign(array("TXT_DATE"=>$currdate,"TXT_RUNDATE"=>$rundate,"TXT_REV"=>$rev));
$tplt3->assign(array("TXT_DATE"=>$currdate,"TXT_RUNDATE"=>$rundate,"TXT_REV"=>$rev));
$tplp2->assign(array("TXT_DATE"=>$currdate,"TXT_RUNDATE"=>$rundate,"TXT_REV"=>$rev));
$tplp3->assign(array("TXT_DATE"=>$currdate,"TXT_RUNDATE"=>$rundate,"TXT_REV"=>$rev));
$tplp4->assign(array("TXT_DATE"=>$currdate,"TXT_RUNDATE"=>$rundate,"TXT_REV"=>$rev));


// ************* DATA PROCESSING *************
debug(10,"getting team codes");
$m_teams=array();
$res=@mysql_query("SELECT teamcode,teamname FROM teams WHERE rev='$rev' ORDER BY teamcode",$dbh) or die("SQL error: teams\n");
while ($row=mysql_fetch_array($res)) {
  $m_teams[$row['teamcode']]=$row['teamname'];
}

debug(10,"getting packages");
$m_packages=array();
$res=@mysql_query("SELECT package FROM sum WHERE rev='$rev' GROUP BY package",$dbh) or die("SQL error: group by package\n");
while ($row=@mysql_fetch_row($res)) {
  array_push($m_packages,$row[0]);
}

// make dirpath for $rev
if (!is_dir("$outdir/$rev")) {
  if (!@mkdir("$outdir/$rev",0755)) {
    die("Cannot make $outdir/$rev directory!");
  }
}

// build table columns array
$shortnames=array();
$packagelist="";
foreach ($m_packages as $package) {
  $shortkey = ereg_replace("^kde","",$package);
  array_push($shortnames,$shortkey);
  $packagelist.=make_packagelist($package,1);
}

// generate HTML code for team list bar
$teamlist="";
foreach ($m_teams as $teamcode => $teamname) {
  $teamlist.=make_teamlist($teamcode,$teamname,1);
}


$tplp2->assign(PACKAGELIST,$packagelist);
$tplp3->assign(TEAMLIST,$teamlist);
$tplt2->assign(TEAMLIST,$teamlist);
$tplt3->assign(PACKAGELIST,$packagelist);


// make dirpath for partialy translated section
if (!is_dir("$outdir/$rev/partial")) {
  if (!@mkdir("$outdir/$rev/partial",0755)) {
    die("Cannot make $outdir/$rev/partial directory!");
  }
}


make_infobyteam($outbyteam);
make_infobypackage($outbypackage);
make_fullinfo($outfullinfo);
make_top($outtop);
make_essential($outessential);
make_generalinfo($outgeneral);
make_partial($outpartial);



// render HTML/PHP pages by team for partial translations
foreach ($m_teams as $teamcode => $teamname) {
  if ($teamcode=="templates") continue;
  debug(10,"render partialy translated level2 for '$teamcode' team");

  
  if (!is_dir("$outdir/$rev/partial/$teamcode")) {
    if (!@mkdir("$outdir/$rev/partial/$teamcode",0755)) {
      die("Cannot make $outdir/$rev/partial/$teamcode directory!\n");
    }
  }
  
  // render partialy translated level 2 
  $output ="<a name=\"partialy\"></a><h3>Partialy Translated PO files</h3>\n";

  $result=@mysql_query("SELECT package,filename,fuzzy,untranslated FROM stats WHERE team='$teamcode' " . 
                       "AND rev='$rev' AND have_po=1 AND have_pot=1 " .
                       "AND untranslated<>0 ORDER BY package",$dbh)
    or die("SQL error: partial level 2 - 1");
  
  $package="";
  $count=0;
  while ($row=@mysql_fetch_array($result)) {
    if ($package<>$row['package']) {
      if ($package<>"") {
        $output.="</table>\n\n";
      }
      $output.= "<h4><a href=\"../../". $teamcode ."/". $row['package'] ."/index.php\">" . $row['package'] . "</a></h4>\n" .
                "<table border=\"0\" cellpadding=\"2\">\n" .
                "<tr bgcolor=\"#f0f0ff\"><td><i>filename</i></td><td><i>fuzzy</i></td><td><i>untranslated</i></td></tr>\n";
      $package= $row['package'];
    }
    $filename=preg_replace('/\.po$/',"",$row['filename']);
    $cvsurl=cvsweburl($rev,$teamcode,$package,$row['filename']);
    $output .= "<tr bgcolor=\"#f8f8f8\"><td><a href=\"$cvsurl\">$filename</a></td><td>" . 
               $row['fuzzy'] . "</td><td>" . $row['untranslated'] . "</td></tr>\n";
    $count++;
  }
  if ($count==0) {
      $output.= "<table border=\"0\" cellpadding=\"2\">\n" .
                "<tr><td>There's no files available.</td></tr>\n";
  }
  $output .="</table>\n\n";
  
  
  $output .="<hr noshade size=\"1\">\n\n";
  
  $output .="<a name=\"totaly\"></a><h3>Totaly Untranslated PO files</h3>\n";

  // render totaly untranslated level 2 
  $result=@mysql_query("SELECT package,filename,untranslated,have_po FROM stats WHERE team='$teamcode' " . 
                       "AND rev='$rev' AND error=0 " .
                       "AND translated=0 AND fuzzy=0 ORDER BY package",$dbh)
    or die("SQL error: partial level 2 - 2");
  
  $package="";
  $count=0;
  while ($row=@mysql_fetch_array($result)) {
    if ($package<>$row['package']) {
      if ($package<>"") {
        $output.="</table>\n\n";
      }
      $output.= "<h4><a href=\"../../". $teamcode ."/". $row['package'] ."/index.php\">" . $row['package'] . "</a></h4>\n" .
                "<table border=\"0\" cellpadding=\"2\">\n" .
                "<tr bgcolor=\"#f0f0ff\"><td><i>filename</i></td><td><i>untranslated</i></td><td><i>type</i></td></tr>\n";
      $package= $row['package'];
    }
    $filename=preg_replace('/\.pot?$/',"",$row['filename']);
    $type=($row['have_po']==1) ? "po" : "pot";
    $cvsurl=cvsweburl($rev,$teamcode,$package,$row['filename']);
    $output .= "<tr bgcolor=\"#f8f8f8\"><td><a href=\"$cvsurl\">$filename</a></td><td>" . 
               $row['untranslated'] . "</td><td>" . $type . "</td></tr>\n";
    $count++;
  }
  if ($count==0) {
      $output.= "<table border=\"0\" cellpadding=\"2\">\n" .
                "<tr><td>There's no files available.</td></tr>\n";
  }
  $output .="</table>\n\n";

  $output .="<hr noshade size=\"1\">\n\n";

  // render obsolete translated level 2 
  $output .="<a name=\"obsolete\"></a><h3>Obsolete Translated PO files</h3>\n";

  $result=@mysql_query("SELECT package,filename,translated,fuzzy,untranslated FROM stats WHERE team='$teamcode' " . 
                       "AND rev='$rev' AND have_po=1 AND have_pot=0 " .
                       "ORDER BY package",$dbh)
    or die("SQL error: partial level 2 - 3");
  
  $package="";
  $count=0;
  while ($row=@mysql_fetch_array($result)) {
    if ($package<>$row['package']) {
      if ($package<>"") {
        $output.="</table>\n\n";
      }
      $output.= "<h4><a href=\"../../". $teamcode ."/". $row['package'] ."/index.php\">" . $row['package'] . "</a></h4>\n" .
                "<table border=\"0\" cellpadding=\"2\">\n" .
                "<tr bgcolor=\"#f0f0ff\"><td><i>filename</i></td><td><i>translated</i></td>" .
                "<td><i>fuzzy</i></td><td><i>untranslated</i></td></tr>\n";
      $package= $row['package'];
    }
    $filename=preg_replace('/\.po$/',"",$row['filename']);
    $cvsurl=cvsweburl($rev,$teamcode,$package,$row['filename']);
    $output .= "<tr bgcolor=\"#f8f8f8\"><td><a href=\"$cvsurl\">$filename</a></td><td>" . 
               $row['translated'] . "</td><td>" . $row['fuzzy'] . "</td><td>" .
               $row['untranslated'] . "</td></tr>\n";
    $count++;
  }
  if ($count==0) {
      $output.= "<table border=\"0\" cellpadding=\"2\">\n" .
                "<tr><td>There's no files available.</td></tr>\n";
  }
  $output .="</table>\n\n";


  $tplp4->assign(array(
              TXT_TEAMNAME  => $teamname,
              TXT_TEAMCODE  => $teamcode,
              TXT_TEAMCODE2 => strtoupper($teamcode),
              TEAMLIST      => $teamlist,
              CONTENT       => $output
            ));

  $tplp4->parse(MAIN,"level2partial");
  writefile("$outdir/$rev/partial/$teamcode/index.php",$tplp4->fetch());
}


// render HTML/PHP pages by KDE package for level 2 & 3
foreach ($m_packages as $package) {
  debug(10,"render level2 for '$package' package");

  $output            ="";
  $total_error       =0;
  $total_translated  =0;
  $total_fuzzy       =0;
  $total_untranslated=0;

  // make dirpath for $rev & current package
  if (!is_dir("$outdir/$rev/$package")) {
    if (!@mkdir("$outdir/$rev/$package",0755)) {
      die("Cannot make $outdir/$rev/$package directory!");
    }
  }
  
  // render level 2 HTML/PHP by package
  $result=@mysql_query("SELECT * FROM sum WHERE package='$package' " . 
                       "AND rev='$rev' AND team<>'templates' ORDER BY team",$dbh)
    or die("SQL error: level2 1");
  while ($row=mysql_fetch_array($result)) {
    $output .=bypackage_line2($row['team'],$row['error'],$row['translated'],$row['fuzzy'],$row['untranslated']);
    $total_error       +=$row['error'];
    $total_translated  +=$row['translated'];
    $total_fuzzy       +=$row['fuzzy'];
    $total_untranslated+=$row['untranslated'];
  }
  $output.=bypackage_total2($total_error,$total_translated,$total_fuzzy,$total_untranslated);
  $tplp2->assign(array(
            TXT_PACKAGE   => $package,
            TXT_PACKAGE2  => strtoupper($package),
            TABLE         => $output
          ));
  $tplp2->parse(MAIN,"level2bypackage");
  writefile("$outdir/$rev/$package/index.php",$tplp2->fetch());
  
  // render level 3 HTML/PHP by package and team
  $tplp3->assign(array(
                        TXT_PACKAGE  => $package,
                        TXT_PACKAGE2 => strtoupper($package),
                      ));
  foreach ($m_teams as $teamcode => $teamname) {
    // make dirpath for $rev, $team & current package
    if (!is_dir("$outdir/$rev/$package/$teamcode")) {
      if (!@mkdir("$outdir/$rev/$package/$teamcode",0755)) {
        die("Cannot make $outdir/$rev/$package/$teamcode directory!");
      }
    }
    
    $result=@mysql_query("SELECT * FROM stats WHERE package='$package' " . 
                         "AND rev='$rev' AND team='$teamcode' ORDER BY filename",$dbh)
      or die("SQL error: level3 1");
    $output            ="";
    $total_error       =0;
    $total_translated  =0;
    $total_fuzzy       =0;
    $total_untranslated=0;
    while ($row=mysql_fetch_array($result)) {
      $type = (!$row['have_pot'] && $row['have_po']) ? 1 : 0;
      $output .=bypackage_line3($row['filename'],$row['error'],$row['translated'],$row['fuzzy'],$row['untranslated'],
                                $teamcode,$package,$type);
      $total_error       +=$row['error'];
      if ($type==0) {
        $total_translated  +=$row['translated'];
        $total_fuzzy       +=$row['fuzzy'];
        $total_untranslated+=$row['untranslated'];
      }
    }
    $output.=bypackage_total3($total_error,$total_translated,$total_fuzzy,$total_untranslated);
    $tplp3->assign(array(
              TXT_TEAMNAME  => $teamname,
              TXT_TEAMCODE  => $teamcode,
              TXT_TEAMCODE2 => strtoupper($teamcode),
              TABLE         => $output
            ));
    $tplp3->parse(MAIN,"level3bypackage");
    writefile("$outdir/$rev/$package/$teamcode/index.php",$tplp3->fetch());
  }  
}


// render HTML/PHP pages by team for level 2 & 3
foreach ($m_teams as $teamcode => $teamname) {
  if ($teamcode=="templates") continue;
  debug(10,"render level2 for '$teamcode' team");
  
  $output            ="";
  $total_error       =0;
  $total_translated  =0;
  $total_fuzzy       =0;
  $total_untranslated=0;
  
  $tplt3->assign(TXT_TEAMCODE,$teamcode);

  // make dirpath for $rev & current teamcode
  if (!is_dir("$outdir/$rev/$teamcode")) {
    if (!@mkdir("$outdir/$rev/$teamcode",0755)) {
      die("Cannot make $outdir/$rev/$teamcode directory!");
    }
  }
  
  // render level 2 HTML/PHP by team
  $result=@mysql_query("SELECT * FROM sum WHERE team='$teamcode' " . 
                       "AND rev='$rev' ORDER BY package",$dbh)
    or die("SQL error: level2 1");
  
  while ($row=mysql_fetch_array($result)) {
    $output .=byteam_line2($row['package'],$row['error'],$row['translated'],$row['fuzzy'],$row['untranslated']);
    $total_error       +=$row['error'];
    $total_translated  +=$row['translated'];
    $total_fuzzy       +=$row['fuzzy'];
    $total_untranslated+=$row['untranslated'];
  }
  $output.=byteam_total2($total_error,$total_translated,$total_fuzzy,$total_untranslated);
  $tplt2->assign(array(
            TXT_TEAMCODE  => $teamcode,
            TXT_TEAMNAME  => $teamname,
            TABLE         => $output
          ));
  $tplt2->parse(MAIN,"level2byteam");
  writefile("$outdir/$rev/$teamcode/index.php",$tplt2->fetch());
  
  // render level 3 HTML/PHP by team and package
  $tplt3->assign(array(
            TXT_TEAMNAME  => $teamname,
            TXT_TEAMCODE  => $teamcode,
            TXT_TEAMCODE2 => strtoupper($teamcode)
          ));
  foreach ($m_packages as $package) {
    // make dirpath for $rev, current teamcode & current package
    if (!is_dir("$outdir/$rev/$teamcode/$package")) {
      if (!@mkdir("$outdir/$rev/$teamcode/$package",0755)) {
        die("Cannot make $outdir/$rev/$teamcode/$package directory!");
      }
    }
    
    $result=@mysql_query("SELECT * FROM stats WHERE package='$package' " . 
                         "AND rev='$rev' AND team='$teamcode' ORDER BY filename",$dbh)
      or die("SQL error: level3 1");
    $output            ="";
    $total_error       =0;
    $total_translated  =0;
    $total_fuzzy       =0;
    $total_untranslated=0;
    while ($row=mysql_fetch_array($result)) {
      $type = (!$row['have_pot'] && $row['have_po']) ? 1 : 0;
      $output .=byteam_line3($row['filename'],$row['error'],$row['translated'],$row['fuzzy'],$row['untranslated'],
                                $teamcode,$package,$type);
      $total_error+=$row['error'];
      if ($type==0) {
        $total_translated  +=$row['translated'];
        $total_fuzzy       +=$row['fuzzy'];
        $total_untranslated+=$row['untranslated'];
      }
    }
    $output.=byteam_total3($total_error,$total_translated,$total_fuzzy,$total_untranslated);
    $tplt3->assign(array(
              TXT_PACKAGE   => $package,
              TXT_PACKAGE2  => strtoupper($package),
              TABLE         => $output
            ));
    $tplt3->parse(MAIN,"level3byteam");
    writefile("$outdir/$rev/$teamcode/$package/index.php",$tplt3->fetch());
    
  }    
}

closedb($dbh);

// send_ok("Success generating HTML GUI statistics pages for $rev branch.");

?>

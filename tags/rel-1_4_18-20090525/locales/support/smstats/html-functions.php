<?php

////////////////////////////////////////////////////////////////////////////
// $Id$
//
// Description: common functions for GUI messages statistic program
//
////////////////////////////////////////////////////////////////////////////


/**************************************************************************/

//
// make partialy translated page by team (level 0)
//
function make_partial($outfile="") {
  global $dbh, $currdate, $rundate, $rev, $m_teams, $okbg;
  
  debug(10,"making partialy translated page");
  
  $output="\n";
  $bgcolor=$okbg;
  
  $tpl=new FastTemplate("./templates/");
  $tpl->define(array("index" => "indexpartial.tpl"));
  
  $results1=@mysql_query("SELECT team, COUNT(*) AS partialytranslated "  .
                        " FROM stats WHERE rev='$rev' AND untranslated<>0 " . 
                        " AND have_po=1 AND have_pot=1 GROUP BY team ORDER BY team"
                        ,$dbh);
  if (!$results1) {
    send_err("SQL error: partial total untranslated");
    exit();
  }
  $results2=@mysql_query("SELECT team, COUNT(*) AS totalyuntranslated "  .
                        " FROM stats WHERE rev='$rev' AND ( (have_po=0 AND have_pot=1 " . 
                        " AND error=0 AND team<>'templates') or (have_po=1 AND " .
                        " have_pot=1 AND error=0 AND translated=0 AND fuzzy=0) ) " .
                        " GROUP BY team ORDER BY team"
                        ,$dbh);
  if (!$results2) {
    send_err("SQL error: partial total no-po");
    exit();
  }

  if (@mysql_num_rows($results1) <> @mysql_num_rows($results2)) {
    $warn = "WARNING! number of teams is different on SQL queries\n" .
            "partialy=" . mysql_num_rows($results1) .
            " untranslated=" . mysql_num_rows($results2);
    debug(1,$warn);
  }
  
  $partial_array=array();
  while ($row=@mysql_fetch_array($results1)) {
    $partial_array[$row['team']]=$row['partialytranslated'];
  }
  $untranslated_array=array();
  while ($row=@mysql_fetch_array($results2)) {
    $untranslated_array[$row['team']]=$row['totalyuntranslated'];
  }
  
  foreach($m_teams as  $teamcode=>$teamname) {
    if (!empty($partial_array[$teamcode])) {
      $partialy=$partial_array[$teamcode];
    } else {
      $partialy="n/a";
    }
    if (!empty($untranslated_array[$teamcode])) {
      $untranslated=$untranslated_array[$teamcode];
    } else {
      $untranslated="n/a";
    }
    
    $output.= "<tr bgcolor=\"$bgcolor\">\n" .
              "  <td><font size=\"2\"><a href=\"$teamcode/index.php\">$teamname</a> ($teamcode)</font></td>\n" .
              "  <td align=\"right\"><font size=\"2\">$partialy</font></td>\n" .
              "  <td align=\"right\"><font size=\"2\">$untranslated</font></td>\n" .
              "</tr>\n";

  }
  
  // parsing & writing PHP/HTML output
  $tpl->assign(array(
              "TXT_DATE"    => $currdate,
              "TXT_RUNDATE" => $rundate,
              "TXT_REV"     => $rev, 
              "TABLE"       => $output
            ));
  $tpl->parse("MAIN","index");
  writefile($outfile,$tpl->fetch());
}


/**************************************************************************/

//
// make essential files page (level 0)
//
function make_generalinfo($outfile="") {
  global $dbh, $currdate, $rundate, $rev, $m_teams, $m_packages, $errorbg;

  debug(10,"making general info page");
  $tpl=new FastTemplate("./templates/");
  $tpl->define(array("index" => "indexgeneral.tpl"));
  
  // **** totals ****
  $output="<h3>Totals</h3><font size=\"2\">";
  
  $result=@mysql_query("SELECT count(*) FROM sum "           .
                       "WHERE team!='templates' " . 
                       "AND rev='$rev' GROUP BY package",$dbh);
  if (!$result) {
    send_err("SQL error: general 1");
    exit();
  }
  $row=@mysql_fetch_row($result);
  $output.="&nbsp;&nbsp;<b>Translation teams:</b> " . $row[0] . "<br>\n";

  $result=@mysql_query("SELECT count(*) FROM sum "           .
                       "WHERE rev='$rev' GROUP BY team",$dbh);
  if (!$result) {
    send_err("SQL error: general 2");
    exit();
  }
  $row=mysql_fetch_row($result);
  $output.="&nbsp;&nbsp;<b>KDE packages:</b> " . $row[0] . "<br>\n";
  
  $result=@mysql_query("SELECT sum(total) FROM stats "           .
                       "WHERE rev='$rev' AND team='templates'",$dbh);
  if (!$result) {
    send_err("SQL error: general 3");
    exit();
  }
  $row=@mysql_fetch_row($result);
  $output.="&nbsp;&nbsp;<b>Total POT messages:</b> " . $row[0] . "<br>\n";
  
  $result=@mysql_query("SELECT count(filename) FROM stats "           .
                       "WHERE rev='$rev' AND team='templates'",$dbh);
  if (!$result) {
    send_err("SQL error: general 4");
    exit();
  }
  $row=mysql_fetch_row($result);
  $output.="&nbsp;&nbsp;<b>Total POT files:</b> " . $row[0] . "<br>\n";

  $result=@mysql_query("SELECT sum(total) FROM stats "           .
                       "WHERE rev='$rev' AND team!='templates'",$dbh);
  if (!$result) {
    send_err("SQL error: general 5");
    exit();
  }
  $row=mysql_fetch_row($result);
  $output.="&nbsp;&nbsp;<b>Total PO messages:</b> " . $row[0] . "<br>\n";

  $result=@mysql_query("SELECT count(filename) FROM stats "           .
                       "WHERE rev='$rev' AND team!='templates'",$dbh);
  if (!$result) {
    send_err("SQL error: general 6");
    exit();
  }
  $row=mysql_fetch_row($result);
  $output.="&nbsp;&nbsp;<b>Total PO files:</b> " . $row[0] . "<br>\n";


  // **** extended info ****
  $output.="</font><h3>Extended info</h3><font size=\"2\">";

  $result=@mysql_query("SELECT team FROM sum "           .
                       "WHERE team!='templates' " . 
                       "AND rev='$rev' GROUP BY team ORDER BY team",$dbh);
  if (!$result) {
    send_err("SQL error: general 7");
    exit();
  }
  $output.="&nbsp;&nbsp;<b>Translation teams:</b> ";
  $row=mysql_fetch_row($result);
  $output.=$row[0];
  while ($row=mysql_fetch_row($result)) {
   $output.=", " . $row[0];
  }
  $output.="<br>\n";

  $result=@mysql_query("SELECT package FROM sum "           .
                       "WHERE " . 
                       "rev='$rev' GROUP BY package ORDER BY package",$dbh);
  if (!$result) {
    send_err("SQL error: general 8");
    exit();
  }
  $output.="&nbsp;&nbsp;<b>KDE packages:</b> ";
  $row=mysql_fetch_row($result);
  $output.=$row[0];
  while ($row=mysql_fetch_row($result)) {
   $output.=", " . $row[0];
  }
  $output.="<br>\n";
  
  $result=@mysql_query("SELECT sdate FROM essential " .
                       "WHERE " . 
                       "rev='$rev' GROUP BY sdate ORDER BY sdate",$dbh);
  if (!$result) {
    send_err("SQL error: general 9");
    exit();
  }
  $output.="&nbsp;&nbsp;<b>Essential history days:</b> ";
  $row=mysql_fetch_row($result);
  $output.=$row[0];
  while ($row=mysql_fetch_row($result)) {
   $output.=", " . $row[0];
  }
  $output.="<br>\n";

  // **** errors ***
  $output.="</font><h3>Errors</h3><font size=\"2\">";
  $result=@mysql_query("SELECT sum(error) FROM sum WHERE rev='$rev' AND error<>0",$dbh);
  if (!$result) {
    send_err("SQL error: general 10");
    exit();
  }
  $row=mysql_fetch_row($result);
  $output.="&nbsp;&nbsp;<b>Total invalid PO/POT files:</b> " . ($row[0]+0) . "<br>\n";

  $result=@mysql_query("SELECT team,package,error FROM sum "           .
                       "WHERE rev='$rev' AND error<>0 " . 
                       "ORDER BY team, package",$dbh);
  if (!$result) {
    send_err("SQL error: general 11");
    exit();
  }
  $output.="&nbsp;&nbsp;<b>Packages with errors:</b> ";
  if (mysql_num_rows($result)>0) {
    $row=mysql_fetch_row($result);
    $t=$row[0]; $p=$row[1]; $c=$row[2];
    $output.="<a href=\"$t/$p/\">$t/$p</a>($c)";
    while ($row=mysql_fetch_row($result)) {
      $t=$row[0]; $p=$row[1]; $c=$row[2];  
      $output.=", <a href=\"$t/$p/\">$t/$p</a>($c)";
    }
  }
  $output.="<br>\n";

  $result=@mysql_query("SELECT count(*) FROM stats WHERE rev='$rev' ". 
                       "AND team='templates' AND (translated<>0 OR fuzzy<>0)",$dbh);
  if (!$result) {
    send_err("SQL error: general 12");
    exit();
  }
  $row=@mysql_fetch_row($result);
  $output.="&nbsp;&nbsp;<b>Total \"translated\" POT files:</b> " . $row[0] . "<br>\n";

  $result=@mysql_query("SELECT filename FROM stats "           .
                       "WHERE rev='$rev' AND team='templates' AND " . 
                       "(translated<>0 OR fuzzy<>0) ORDER BY filename",$dbh);
  if (!$result) {
    send_err("SQL error: general 13");
    exit();
  }
  $output.="&nbsp;&nbsp;<b>\"Translated\" POT files:</b> ";
  $row=mysql_fetch_row($result);
  $output.=$row[0];
  while ($row=mysql_fetch_row($result)) {
   $output.=", " . $row[0];
  }
  $output.="<br>\n";


  $result=@mysql_query("SELECT filename FROM essential WHERE rev='$rev' AND team='templates' ". 
                       "AND sdate='$currdate' AND (total=0 OR translated=0)" .
                       "ORDER BY filename",$dbh);
  if (!$result) {
    send_err("SQL error: general 14");
    exit();
  }
  $output.="&nbsp;&nbsp;<b>Configuration errors for essential:</b> ";
  $row=mysql_fetch_row($result);
  $output.=$row[0];
  while ($row=mysql_fetch_row($result)) {
   $output.=", " . $row[0];
  }
  $output.="<br>\n";

  
  // **** sql database ***
  $output.="</font><h3>SQL info</h3><font size=\"2\">";

  $result=@mysql_query("SELECT count(*) FROM sum WHERE rev='$rev'",$dbh);
  if (!$result) {
    send_err("SQL error: general 15");
    exit();
  }
  $row=@mysql_fetch_row($result);
  $output.="&nbsp;&nbsp;<b>Table SUM records:</b> " . $row[0] . "<br>\n";
  
  $result=@mysql_query("SELECT count(*) FROM stats WHERE rev='$rev'",$dbh);
  if (!$result) {
    send_err("SQL error: general 16");
    exit();
  }
  $row=@mysql_fetch_row($result);
  $output.="&nbsp;&nbsp;<b>Table STATS records:</b> " . $row[0] . "<br>\n";
    
  $result=@mysql_query("SELECT count(*) FROM essential WHERE rev='$rev'",$dbh);
  if (!$result) {
    send_err("SQL error: general 17");
    exit();
  }
  $row=@mysql_fetch_row($result);
  $output.="&nbsp;&nbsp;<b>Table ESSENTIAL records:</b> " . $row[0] . "<br>\n";

  $result=@mysql_query("SELECT count(*) FROM teams WHERE rev='$rev'",$dbh);
  if (!$result) {
    send_err("SQL error: general 18");
    exit();
  }
  $row=@mysql_fetch_row($result);
  $output.="&nbsp;&nbsp;<b>Table TEAMS records:</b> " . $row[0] . "<br>\n";
  
  $output.="</font>";

  // parsing & writing PHP/HTML output
  $tpl->assign(array(
              "TXT_DATE"    => $currdate,
              "TXT_RUNDATE" => $rundate,
              "TXT_REV"     => $rev, 
              "TABLE"       => $output
            ));
  $tpl->parse("MAIN","index");
  writefile($outfile,$tpl->fetch());

}


/**************************************************************************/

//
// make essential files page (level 0)
//
function make_essential($outfile="") {
  global $dbh, $currdate, $rundate, $rev, $m_teams, $m_packages,
         $okcolor1, $okcolor2, $okcolor3, $okcolor4, $errorbg;
  
  debug(10,"making essential files page");
  
  
  // make columns line
  $output ="<tr bgcolor=\"#f0f0f0\">\n" .
           "  <td><font size=\"2\">teams</font></td>\n";
  $output.="  <td><font size=\"2\"><i>completeness</i></font></td>\n";
  
  $result=@mysql_query("SELECT * FROM essential WHERE team='templates' " .
                      "AND sdate='$currdate' AND rev='$rev'"
                      ,$dbh);
  if (!$result) {
    send_err("SQL error: esential 1");
    exit();
  }
  $essential=array();
  while($row=mysql_fetch_array($result)) {
    $filename=$row['filename'];
    $threshold=$row['translated'];
    $total=$row['total'];
    $essential[$filename]=array($threshold,$total);
    if (preg_match("/\.po$/",$filename)) {
      $output.="  <td><nobr><font size=\"2\">$filename</font></nobr></td>\n";
    } else {
      $output.="  <td><nobr><font size=\"2\"><b>$filename</b></font></nobr></td>\n";
    }
  }
  $output.="</tr>\n";
  
  $tpl=new FastTemplate("./templates/");
  $tpl->define(array("index" => "indexessential.tpl"));
  
  // extract only the used team codes
  $result=@mysql_query("SELECT DISTINCT team FROM essential "           .
                       "WHERE team!='templates' AND sdate='$currdate' " . 
                       "AND rev='$rev' ORDER BY team",$dbh);
  if (!$result) {
    send_err("SQL error: esential 1");
    exit();
  }
  $teams=array();
  while($row=mysql_fetch_array($result)) {
    $teams[$row['team']]=$m_teams[$row['team']];
  }
  
  $i=0;
  foreach (array_keys($teams) as $team) {  
    // render cell for team name
    $okcolor  = ($i++%2) ? $okcolor1 : $okcolor2;
    $teamname=$m_teams[$team];
    $output .="<tr bgcolor=\"$okcolor\">\n" .
              "<td width=\"100%\"><font size=\"2\">" .
              "<a class=\"team\" href=\"$team/index.php\">$teamname</a> " .
              "<font size=\"1\">($team)</font>" .
              "</font></td>\n";
    
    // render cells
    $completeness=0;
    $result=@mysql_query("SELECT * FROM essential WHERE team='$team' AND sdate='$currdate' AND rev='$rev'",$dbh);
    if (!$result) {
      send_err("SQL error: esential 1");
      exit();
    }
    $j=$total_percent=0;
    $percentages=array();
    while($row=mysql_fetch_array($result)) {
      $filename=$row['filename'];
      $translated=$row['translated'];
      list($threshold,$total)=$essential[$filename];
      if ($translated>0 && $total>0) {
        $percent=sprintf("%2.2f",100*$translated/$total);
        // corection when are duplicates in some PO files
        if ($percent>100) $percent="100.00";
        if ($percent>=$threshold) {
          $total_percent+=1;
          $tag_start="<b>";
          $tag_stop="</b>";
        } else {
          $tag_start="";
          $tag_stop="";
        }
      } else {
        $percent="0";
        $tag_start="";
        $tag_stop="";
      }
      if ($total>0) $j++;
      array_push($percentages,"$tag_start$percent$tag_stop");
    }
    
    
    // calculate & render completeness cell
    $completeness=sprintf("%2.2f",100*$total_percent/$j);
    // make sure that 0% is not 0.00% (lower eye strain)
    if ($completeness==0) $completeness=0;
    $okcolor = ($i%2) ? $okcolor4 : $okcolor3;
    if ($completeness==100) {
      $okcolor="#229922";
      $tag_start="<b><font color=\"#ffffff\">";
      $tag_stop="</font></b>";
    } else {
      $tag_start="";
      $tag_stop="";
    }
    $output .= "<td bgcolor=\"$okcolor\" align=\"right\"><font size=\"2\">$tag_start$completeness$tag_stop</font></td>\n";
    
    foreach($percentages as $percentage) {
      $output .= "<td align=\"right\"><font size=\"2\">$percentage</font></td>\n";
    }

    $output .="</tr>\n";
  }
  
  // list thresholds used for calculus
  $thresholds="";
  $result=@mysql_query("SELECT filename,translated FROM essential WHERE team='templates' " .
                       "AND sdate='$currdate' AND rev='$rev'"
                       ,$dbh);
  if (!$result) {
    send_err("SQL error: esential 4");
    exit();
  }
  while($row=mysql_fetch_array($result)) {
    $filename=$row['filename'];
    $threshold=$row['translated'];
    $thresholds.="&nbsp;&nbsp;$filename - $threshold%<br>\n";
  }
  
    
  // parsing & writing PHP/HTML output
  $tpl->assign(array(
              "TXT_DATE"    => $currdate,
              "TXT_RUNDATE" => $rundate,
              "TXT_REV"     => $rev, 
              "TABLE"       => $output,
              "THRESHOLDS"  => $thresholds
            ));
  $tpl->parse("MAIN","index");
  writefile($outfile,$tpl->fetch());
}


/**************************************************************************/

//
// make full info page (level 0)
//
function make_fullinfo($outfile="") {
  global $dbh, $currdate, $rundate, $rev, $m_teams, $m_packages, $okcolor1, $okcolor2, $errorbg;
  
  debug(10,"making full info page");
  
  $i=0;
  
  // make columns line
  $output="<tr bgcolor=\"#f0f0f0\">\n" .
          "  <td><font size=\"2\">&nbsp;</font></td>\n";
  foreach ($m_packages as $key) {
    $shortkey = ereg_replace("^kde","",$key);
    $output.="  <td><font size=\"2\"><b>$shortkey</b></font></td>\n";
  }
  $output.="</tr>\n";
  
    
  $tpl=new FastTemplate("./templates/");
  $tpl->define(array("index" => "indexfullinfo.tpl"));

  foreach (array_keys($m_teams) as $team) {  
    $okcolor  = ($i++%2) ? $okcolor1 : $okcolor2;
    $teamname=$m_teams[$team];
    $output .="<tr bgcolor=\"$okcolor\">\n" .
              "<td><font size=\"2\">" .
              "<a class=\"team\" href=\"$team/index.php\">$teamname</a> " .
              "<font size=\"1\">($team)</font>" .
              "</font></td>\n";
    
    $results=@mysql_query("SELECT package, translated, total, error " .
                          " FROM sum WHERE rev='$rev' and team='$team' ORDER BY package"
                          ,$dbh);
    if (!$results) {
      send_err("SQL error: fullinfo");
      exit();
    }

    while ($row=@mysql_fetch_array($results)) {
      if ($row['total']>0) {
        $package=$row['package'];
        $percent=sprintf("%2.2f",100*$row['translated']/$row['total']);
        if ($percent==100) $percent=100;
        if ($row['error']>0) { 
          $bgcolor="bgcolor=\"$errorbg\"";
        } else {
          $bgcolor="";
        }
        $output .= "<td $bgcolor align=\"right\"><font size=\"2\">" .
                  "<a class=\"team\" href=\"$team/$package/index.php\">$percent</a>" .
                  "</font></td>\n";
      } else {
        $output .= "<td align=\"right\"><font size=\"2\">0</font></td>\n";
      }
    }
    
    $output .="</tr>\n";
  }
    
  // parsing & writing PHP/HTML output
  $tpl->assign(array(
              "TXT_DATE"    => $currdate,
              "TXT_RUNDATE" => $rundate,
              "TXT_REV"     => $rev, 
              "TABLE"       => $output
            ));
  $tpl->parse("MAIN","index");
  writefile($outfile,$tpl->fetch());
}


/**************************************************************************/

//
// make top 10 (level 0)
//
function make_top($outfile="") {
  global $dbh, $currdate, $rundate, $rev, $m_teams, $okbg, $okbg2;
  
  debug(10,"making top list");
  
  $output="\n";
  $i=1;
  $total_errors=0;
  $total_translated=0;
  $total_fuzzy=0;
  $total_untranslated=0;
  $total_total=0;
  
  $tpl=new FastTemplate("./templates/");
  $tpl->define(array("index" => "indextop.tpl"));
  
  $results=mysql_query("SELECT team, SUM(translated) AS translated, SUM(fuzzy) AS fuzzy, "   .
                       " SUM(untranslated) AS untranslated, SUM(total) AS total "            .
                       " FROM sum WHERE rev='$rev' GROUP BY team ORDER BY translated DESC, " .
                       " team ASC LIMIT 0,100" 
                       ,$dbh);
  if (!$results) {
    send_err("SQL error: top10");
    exit();
  }
  while ($row=@mysql_fetch_array($results)) {
    $teamcode       = $row['team'];
    $teamname       = $m_teams[$row['team']];
    $translated     = $row['translated'];
    $fuzzy          = $row['fuzzy'];
    $untranslated   = $row['untranslated'];
    $total          = $row['total'];
    
    if ($row['total'] >0 ) {
      $ptranslated    = (int)(100*$translated/$total);
      $pfuzzy         = (int)(100*$fuzzy/$total);
      $puntranslated  = 100-$pfuzzy-$ptranslated;
      $ptranslated    = 2 * $ptranslated;
      $pfuzzy         = 2 * $pfuzzy;
      $puntranslated  = 2 * $puntranslated;

      $ptranslated2   = sprintf("%2.2f",100*$translated/$total);
      $pfuzzy2        = sprintf("%2.2f",100*$fuzzy/$total);
      $puntranslated2 = sprintf("%2.2f",100-$pfuzzy2-$ptranslated2);

      $output.= "<tr bgcolor=\"$okbg\">\n" .
                "  <td align=\"right\"><font size=\"2\">$i</font></td>\n" .
                "  <td><font size=\"2\"><a class=\"team\" href=\"$teamcode/index.php\">$teamname</a> ($teamcode)</font></td>\n" .
                "  <td align=\"right\"><font size=\"2\">$translated</font></td>\n" .
                "  <td bgcolor=\"$okbg2\" align=\"right\"><font size=\"2\">$ptranslated2</font></td>\n" .
                "  <td align=\"right\"><font size=\"2\">$fuzzy</font></td>\n" .
                "  <td bgcolor=\"$okbg2\" align=\"right\"><font size=\"2\">$pfuzzy2</font></td>\n" .
                "  <td align=\"right\"><font size=\"2\">$untranslated</font></td>\n" .
                "  <td bgcolor=\"$okbg2\" align=\"right\"><font size=\"2\">$puntranslated2</font></td>\n" .
                "  <td align=\"right\"><font size=\"2\">$total</font></td>\n" .
                "  <td>" .
                  "<img src=\"../img/bar0.png\" height=\"15\" width=\"$ptranslated\">" .
                  "<img src=\"../img/bar4.png\" height=\"15\" width=\"$pfuzzy\">" .
                  "<img src=\"../img/bar1.png\" height=\"15\" width=\"$puntranslated\">" .
                "</td>\n" .
                "</tr>\n";
    } else {
      $output.= "<tr bgcolor=\"$okbg\">\n" .
                "  <td align=\"right\"><font size=\"2\">$i</font></td>\n" .
                "  <td><font size=\"2\"><a class=\"team\" href=\"$teamcode/index.php\">$teamname</a> ($teamcode)</font></td>\n" .
                "  <td align=\"right\"><font size=\"2\">0</font></td>\n" .
                "  <td bgcolor=\"$okbg2\" align=\"right\"><font size=\"2\">0</font></td>\n" .
                "  <td align=\"right\"><font size=\"2\">0</font></td>\n" .
                "  <td bgcolor=\"$okbg2\" align=\"right\"><font size=\"2\">0</font></td>\n" .
                "  <td align=\"right\"><font size=\"2\">0</font></td>\n" .
                "  <td bgcolor=\"$okbg2\" align=\"right\"><font size=\"2\">0</font></td>\n" .
                "  <td align=\"right\"><font size=\"2\">0</font></td>\n" .
                "  <td>" .
                  "<img src=\"../img/bar6.png\" height=\"15\" width=\"200\">" .
                "</td>\n" .
                "</tr>\n";
    }
    $i++;
  }
  
  // parsing & writing PHP/HTML output
  $tpl->assign(array(
              "TXT_DATE"    => $currdate,
              "TXT_RUNDATE" => $rundate,
              "TXT_REV"     => $rev, 
              "TABLE"       => $output
            ));
  $tpl->parse("MAIN","index");
  writefile($outfile,$tpl->fetch());
}


/**************************************************************************/

//
// make compact info page by team (level 0)
//
function make_infobyteam($outfile="") {
  global $dbh, $currdate, $rundate, $rev, $m_teams;
  
  debug(10,"making compact info page by team");
  
  $output="\n";
  $total_errors=0;
  $total_translated=0;
  $total_fuzzy=0;
  $total_untranslated=0;
  $total_total=0;
  
  $tpl=new FastTemplate("./templates/");
  $tpl->define(array("index" => "indexbyteam.tpl"));
  
  $results=@mysql_query("SELECT team, SUM(translated) AS translated, SUM(fuzzy) AS fuzzy, "     .
                        " SUM(untranslated) AS untranslated, SUM(total) AS total, "             .
                        " SUM(error) AS error FROM sum WHERE rev='$rev' AND team<>'templates' " .
                        " GROUP BY team ORDER BY team"
                        ,$dbh);
  if (!$results) {
    send_err("SQL error: byteam");
    exit();
  }
  while ($row=@mysql_fetch_array($results)) {
    $output.=byteam_line1($row['team'],$m_teams[$row['team']],$row['error'],$row['translated'],
                         $row['fuzzy'],$row['untranslated'],$row['total']);
    $total_errors+=$row['error'];
    $total_translated+=$row['translated'];
    $total_fuzzy+=$row['fuzzy'];
    $total_untranslated+=$row['untranslated'];
    $total_total+=$row['total'];
  }
  
  // writing totalizing line
  $output.=byteam_total1($total_errors,$total_translated,$total_fuzzy,$total_untranslated,$total_total);
  // parsing & writing PHP/HTML output
  $tpl->assign(array(
              "TXT_DATE"    => $currdate,
              "TXT_RUNDATE" => $rundate,
              "TXT_REV"     => $rev, 
              "TABLE"       => $output
            ));
  $tpl->parse("MAIN","index");
  writefile($outfile,$tpl->fetch());
}



/**************************************************************************/

//
// make compact info page by team (level 0)
//
function make_infobypackage($outfile="") {
  global $dbh, $currdate, $rundate, $rev, $m_teams;
  
  debug(10,"making compact info page by package");
  
  $output="\n";
  $total_errors=0;
  $total_translated=0;
  $total_fuzzy=0;
  $total_untranslated=0;
  $total_total=0;
  
  $tpl=new FastTemplate("./templates/");
  $tpl->define(array("index" => "indexbypackage.tpl"));
  
  $results=@mysql_query("SELECT package, SUM(translated) AS translated, SUM(fuzzy) AS fuzzy, "  .
                        " SUM(untranslated) AS untranslated, SUM(total) AS total, "             .
                        " SUM(error) AS error FROM sum WHERE rev='$rev' AND team<>'templates' " .
                        " GROUP BY package ORDER BY package"
                        ,$dbh);
  if (!$results) {
    send_err("SQL error: bypackage");
    exit();
  }
  while ($row=@mysql_fetch_array($results)) {
    $output.=bypackage_line1($row['package'],$row['error'],$row['translated'],
                         $row['fuzzy'],$row['untranslated'],$row['total']);
    $total_errors+=$row['error'];
    $total_translated+=$row['translated'];
    $total_fuzzy+=$row['fuzzy'];
    $total_untranslated+=$row['untranslated'];
    $total_total+=$row['total'];
  }
  
  // writing totalizing line
  $output.=bypackage_total1($total_errors,$total_translated,$total_fuzzy,$total_untranslated,$total_total);
  // parsing & writing PHP/HTML output
  $tpl->assign(array(
              "TXT_DATE"    => $currdate,
              "TXT_RUNDATE" => $rundate,
              "TXT_REV"     => $rev, 
              "TABLE"       => $output
            ));
  $tpl->parse("MAIN","index");
  writefile($outfile,$tpl->fetch());
}



/**************************************************************************/

//
// generate a team status line  for 1st level status file
//
function bypackage_line1($package,$error,$translated,$fuzzy,$untranslated,$total) {
  global $rev, $okbg, $errorbg, $okbg2, $errorbg2;
  
  // color by $error
  if ($error>0) {
    $bgcolor=$errorbg;
    $bgcolor2=$errorbg2;
  } else {
    $bgcolor=$okbg;
    $bgcolor2=$okbg2;
  }
  
  if ($total>0) {
    $ptranslated    = (int)(100*$translated/$total);
    $pfuzzy         = (int)(100*$fuzzy/$total);
    $puntranslated  = 100-$pfuzzy-$ptranslated;
    $ptranslated    = 2 * $ptranslated;
    $pfuzzy         = 2 * $pfuzzy;
    $puntranslated  = 2 * $puntranslated;

    $ptranslated2   = sprintf("%2.2f",100*$translated/$total);
    $pfuzzy2        = sprintf("%2.2f",100*$fuzzy/$total);
    $puntranslated2 = sprintf("%2.2f",100-$pfuzzy2-$ptranslated2);
  
    $output = "<tr bgcolor=\"$bgcolor\">\n" .
              "  <td><font size=\"2\"><a href=\"$package/index.php\">$package</a></font></td>\n" .
              "  <td align=\"right\"><font size=\"2\">$translated</font></td>\n" .
              "  <td bgcolor=\"$bgcolor2\" align=\"right\"><font size=\"2\">$ptranslated2</font></td>\n" .
              "  <td align=\"right\"><font size=\"2\">$fuzzy</font></td>\n" .
              "  <td bgcolor=\"$bgcolor2\" align=\"right\"><font size=\"2\">$pfuzzy2</font></td>\n" .
              "  <td align=\"right\"><font size=\"2\">$untranslated</font></td>\n" .
              "  <td bgcolor=\"$bgcolor2\" align=\"right\"><font size=\"2\">$puntranslated2</font></td>\n" .
              "  <td align=\"right\"><font size=\"2\">$total</font></td>\n" .
              "  <td>" .
                 "<img src=\"../img/bar0.png\" height=\"15\" width=\"$ptranslated\">" .
                 "<img src=\"../img/bar4.png\" height=\"15\" width=\"$pfuzzy\">" .
                 "<img src=\"../img/bar1.png\" height=\"15\" width=\"$puntranslated\">" .
              "</td>\n" .
              "  <td align=\"right\"><font size=\"2\">$error</font></td>\n" .
              "</tr>\n";
  } else {
    $output = "<tr bgcolor=\"$bgcolor\">\n" .
              "  <td><font size=\"2\"><a class=\"team\" href=\"$package/index.php\">$package</a> ($teamcode)</font></td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td bgcolor=\"$bgcolor2\" align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td bgcolor=\"$bgcolor2\" align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td bgcolor=\"$bgcolor2\" align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td>" .
                 "<img src=\"../img/bar6.png\" height=\"15\" width=\"200\">" .
              "</td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "</tr>\n";
  }          
  return $output;
}

//
// generate a PO total line for 1st level status file
//
function bypackage_total1($errors,$translated,$fuzzy,$untranslated,$total) {

  if ($total>0) {
    $ptranslated    = (int)(100*$translated/$total);
    $pfuzzy         = (int)(100*$fuzzy/$total);
    $puntranslated  = 100-$pfuzzy-$ptranslated;
    $ptranslated    = 2 * $ptranslated;
    $pfuzzy         = 2 * $pfuzzy;
    $puntranslated  = 2 * $puntranslated;
  
    $ptranslated2   = sprintf("%2.2f",100*$translated/$total);
    $pfuzzy2        = sprintf("%2.2f",100*$fuzzy/$total);
    $puntranslated2 = sprintf("%2.2f",100-$pfuzzy2-$ptranslated2);

    $output = "<tr bgcolor=\"#e0e0e0\">\n" .
              "  <td><font size=\"2\"><b>total</b></font></td>\n" .
              "  <td align=\"right\"><font size=\"2\">$translated</font></td>\n" .
              "  <td bgcolor=\"#d0d0d0\" align=\"right\"><font size=\"2\">$ptranslated2</font></td>\n" .
              "  <td align=\"right\"><font size=\"2\">$fuzzy</font></td>\n" .
              "  <td bgcolor=\"#d0d0d0\" align=\"right\"><font size=\"2\">$pfuzzy2</font></td>\n" .
              "  <td align=\"right\"><font size=\"2\">$untranslated</font></td>\n" .
              "  <td bgcolor=\"#d0d0d0\" align=\"right\"><font size=\"2\">$puntranslated2</font></td>\n" .
              "  <td align=\"right\"><font size=\"2\">$total</font></td>\n" .
              "  <td>" .
                 "<img src=\"../img/bar0.png\" height=\"15\" width=\"$ptranslated\">" .
                 "<img src=\"../img/bar4.png\" height=\"15\" width=\"$pfuzzy\">" .
                 "<img src=\"../img/bar1.png\" height=\"15\" width=\"$puntranslated\">" .
              "</td>\n" .
              "  <td align=\"right\"><font size=\"2\">$errors</font></td>\n" .
              "</tr>\n";
  } else {
    $output = "<tr bgcolor=\"#e0e0e0\">\n" .
              "  <td><font size=\"2\"><b>total</b></font></td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td bgcolor=\"#d0d0d0\" align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td bgcolor=\"#d0d0d0\" align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td bgcolor=\"#d0d0d0\" align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td>" .
                 "<img src=\"../img/bar6.png\" height=\"15\" width=\"200\">" .
              "</td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "</tr>\n";
  }          
  return $output;
  
}

//
// generate a team status line  for 1st level status file
//
function byteam_line1($teamcode,$teamname,$error,$translated,$fuzzy,$untranslated,$total) {
  global $rev, $okbg, $errorbg, $okbg2, $errorbg2;
  
  // color by $error
  if ($error>0) {
    $bgcolor=$errorbg;
    $bgcolor2=$errorbg2;
  } else {
    $bgcolor=$okbg;
    $bgcolor2=$okbg2;
  }
  
  if ($total>0) {
    $ptranslated    = (int)(100*$translated/$total);
    $pfuzzy         = (int)(100*$fuzzy/$total);
    $puntranslated  = 100-$pfuzzy-$ptranslated;
    $ptranslated    = 2 * $ptranslated;
    $pfuzzy         = 2 * $pfuzzy;
    $puntranslated  = 2 * $puntranslated;

    $ptranslated2   = sprintf("%2.2f",100*$translated/$total);
    $pfuzzy2        = sprintf("%2.2f",100*$fuzzy/$total);
    $puntranslated2 = sprintf("%2.2f",100-$pfuzzy2-$ptranslated2);
  
    $output = "<tr bgcolor=\"$bgcolor\">\n" .
              "  <td><font size=\"2\"><a href=\"$teamcode/index.php\">$teamname</a> ($teamcode)</font></td>\n" .
              "  <td align=\"right\"><font size=\"2\">$translated</font></td>\n" .
              "  <td bgcolor=\"$bgcolor2\" align=\"right\"><font size=\"2\">$ptranslated2</font></td>\n" .
              "  <td align=\"right\"><font size=\"2\">$fuzzy</font></td>\n" .
              "  <td bgcolor=\"$bgcolor2\" align=\"right\"><font size=\"2\">$pfuzzy2</font></td>\n" .
              "  <td align=\"right\"><font size=\"2\">$untranslated</font></td>\n" .
              "  <td bgcolor=\"$bgcolor2\" align=\"right\"><font size=\"2\">$puntranslated2</font></td>\n" .
              "  <td align=\"right\"><font size=\"2\">$total</font></td>\n" .
              "  <td>";
	if ($ptranslated==200) {
		$output.= "<img src=\"../img/bar0.png\" height=\"15\" width=\"200\">";
    } else if ($pfuzzy==200) {
		$output.= "<img src=\"../img/bar4.png\" height=\"15\" width=\"200\">";
    } else if ($puntranslated==200) {
		$output.= "<img src=\"../img/bar1.png\" height=\"15\" width=\"200\">";
	} else {	
        $output.= "<img src=\"../img/bar0.png\" height=\"15\" width=\"$ptranslated\">" .
                  "<img src=\"../img/bar4.png\" height=\"15\" width=\"$pfuzzy\">" .
                  "<img src=\"../img/bar1.png\" height=\"15\" width=\"$puntranslated\">";
	}
    $output.= "</td>\n" .
              "  <td align=\"right\"><font size=\"2\">$error</font></td>\n" .
              "</tr>\n";
  } else {
    $output = "<tr bgcolor=\"$bgcolor\">\n" .
              "  <td><font size=\"2\"><a class=\"team\" href=\"$teamcode/index.php\">$teamname</a> ($teamcode)</font></td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td bgcolor=\"$bgcolor2\" align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td bgcolor=\"$bgcolor2\" align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td bgcolor=\"$bgcolor2\" align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td>" .
                 "<img src=\"../img/bar6.png\" height=\"15\" width=\"200\">" .
              "</td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "</tr>\n";
  }          
  return $output;
}

//
// generate a PO total line for 1st level status file
//
function byteam_total1($errors,$translated,$fuzzy,$untranslated,$total) {

  if ($total>0) {
    $ptranslated    = (int)(100*$translated/$total);
    $pfuzzy         = (int)(100*$fuzzy/$total);
    $puntranslated  = 100-$pfuzzy-$ptranslated;
    $ptranslated    = 2 * $ptranslated;
    $pfuzzy         = 2 * $pfuzzy;
    $puntranslated  = 2 * $puntranslated;
  
    $ptranslated2   = sprintf("%2.2f",100*$translated/$total);
    $pfuzzy2        = sprintf("%2.2f",100*$fuzzy/$total);
    $puntranslated2 = sprintf("%2.2f",100-$pfuzzy2-$ptranslated2);

    $output = "<tr bgcolor=\"#e0e0e0\">\n" .
              "  <td><font size=\"2\"><b>total</b></font></td>\n" .
              "  <td align=\"right\"><font size=\"2\">$translated</font></td>\n" .
              "  <td bgcolor=\"#d0d0d0\" align=\"right\"><font size=\"2\">$ptranslated2</font></td>\n" .
              "  <td align=\"right\"><font size=\"2\">$fuzzy</font></td>\n" .
              "  <td bgcolor=\"#d0d0d0\" align=\"right\"><font size=\"2\">$pfuzzy2</font></td>\n" .
              "  <td align=\"right\"><font size=\"2\">$untranslated</font></td>\n" .
              "  <td bgcolor=\"#d0d0d0\" align=\"right\"><font size=\"2\">$puntranslated2</font></td>\n" .
              "  <td align=\"right\"><font size=\"2\">$total</font></td>\n" .
              "  <td>";
	if ($ptranslated==200) {
		$output.= "<img src=\"../img/bar0.png\" height=\"15\" width=\"200\">";
    } else if ($pfuzzy==200) {
		$output.= "<img src=\"../img/bar4.png\" height=\"15\" width=\"200\">";
    } else if ($puntranslated==200) {
		$output.= "<img src=\"../img/bar1.png\" height=\"15\" width=\"200\">";
	} else {	
        $output.= "<img src=\"../img/bar0.png\" height=\"15\" width=\"$ptranslated\">" .
                  "<img src=\"../img/bar4.png\" height=\"15\" width=\"$pfuzzy\">" .
                  "<img src=\"../img/bar1.png\" height=\"15\" width=\"$puntranslated\">";
	}
    $output.= "</td>\n" .
              "  <td align=\"right\"><font size=\"2\">$errors</font></td>\n" .
              "</tr>\n";
  } else {
    $output = "<tr bgcolor=\"#e0e0e0\">\n" .
              "  <td><font size=\"2\"><b>total</b></font></td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td bgcolor=\"#d0d0d0\" align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td bgcolor=\"#d0d0d0\" align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td bgcolor=\"#d0d0d0\" align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td>" .
                 "<img src=\"../img/bar6.png\" height=\"15\" width=\"200\">" .
              "</td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "</tr>\n";
  }          
  return $output;
}

/**************************************************************************/

//
// generate by package status line for 2nd level status file
//
function bypackage_line2($teamcode,$error,$translated,$fuzzy,$untranslated) {
  global $okbg, $okbg2, $errorbg, $errorbg2, $m_teams;
  $total=$translated+$fuzzy+$untranslated;

  // color by $error
  if ($error==0) {
    $bgcolor=$okbg;
    $bgcolor2=$okbg2;
  } else {
    $bgcolor=$errorbg;
    $bgcolor2=$errorbg2;
  }

  $teamname=$m_teams[$teamcode];
  
  if ($total>0) {
    $ptranslated    = (int)(100*$translated/$total);
    $pfuzzy         = (int)(100*$fuzzy/$total);
    $puntranslated  = 100-$pfuzzy-$ptranslated;
    $ptranslated    = 2 * $ptranslated;
    $pfuzzy         = 2 * $pfuzzy;
    $puntranslated  = 2 * $puntranslated;
  
    $ptranslated2   = sprintf("%2.2f",100*$translated/$total);
    $pfuzzy2        = sprintf("%2.2f",100*$fuzzy/$total);
    $puntranslated2 = sprintf("%2.2f",100-$pfuzzy2-$ptranslated2);

    $output = "<tr bgcolor=\"$bgcolor\">\n" .
              "  <td><nobr><font size=\"2\"><a href=\"$teamcode/index.php\">$teamname</a> ($teamcode)</font></nobr></td>\n" .
              "  <td align=\"right\"><font size=\"2\" class=\"$class2\">$translated</font></td>\n" .
              "  <td bgcolor=\"$bgcolor2\" align=\"right\"><font size=\"2\" class=\"$class2\">$ptranslated2</font></td>\n" .
              "  <td align=\"right\"><font size=\"2\">$fuzzy</font></td>\n" .
              "  <td bgcolor=\"$bgcolor2\" align=\"right\"><font size=\"2\">$pfuzzy2</font></td>\n" .
              "  <td align=\"right\"><font size=\"2\">$untranslated</font></td>\n" .
              "  <td bgcolor=\"$bgcolor2\" align=\"right\"><font size=\"2\">$puntranslated2</font></td>\n" .
              "  <td align=\"right\"><font size=\"2\">$total</font></td>\n" .
              "  <td>";
	if ($ptranslated==200) {
		$output.= "<img src=\"../../img/bar0.png\" height=\"15\" width=\"200\">";
    } else if ($pfuzzy==200) {
		$output.= "<img src=\"../../img/bar4.png\" height=\"15\" width=\"200\">";
    } else if ($puntranslated==200) {
		$output.= "<img src=\"../../img/bar1.png\" height=\"15\" width=\"200\">";
	} else {	
        $output.= "<img src=\"../../img/bar0.png\" height=\"15\" width=\"$ptranslated\">" .
                  "<img src=\"../../img/bar4.png\" height=\"15\" width=\"$pfuzzy\">" .
                  "<img src=\"../../img/bar1.png\" height=\"15\" width=\"$puntranslated\">";
	}
	$output.= "</td>\n" .
              "  <td align=\"right\"><font size=\"2\">$error</font></td>\n" .
              "</tr>\n";
  } else {
    $output = "<tr bgcolor=\"$bgcolor\">\n" .
              "  <td><nobr><font size=\"2\"><a href=\"$teamcode/index.php\">$teamname</a> ($teamcode)</font></nobr></td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td bgcolor=\"$bgcolor2\" align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td bgcolor=\"$bgcolor2\" align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td bgcolor=\"$bgcolor2\" align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td>" .
                 "<img src=\"../../img/bar6.png\" height=\"15\" width=\"200\">" .
              "</td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "</tr>\n";
  }          
  return $output;
}

//
// generate by package total line for 2nd level status file
//
function bypackage_total2($error,$translated,$fuzzy,$untranslated) {
  $total=$translated+$fuzzy+$untranslated;  

  if ($total>0) {
    $ptranslated    = (int)(100*$translated/$total);
    $pfuzzy         = (int)(100*$fuzzy/$total);
    $puntranslated  = 100-$pfuzzy-$ptranslated;
    $ptranslated    = 2 * $ptranslated;
    $pfuzzy         = 2 * $pfuzzy;
    $puntranslated  = 2 * $puntranslated;
  
    $ptranslated2   = sprintf("%2.2f",100*$translated/$total);
    $pfuzzy2        = sprintf("%2.2f",100*$fuzzy/$total);
    $puntranslated2 = sprintf("%2.2f",100-$pfuzzy2-$ptranslated2);

    $output = "<tr bgcolor=\"#e0e0e0\">\n" .
              "  <td><font size=\"2\"><b>total</b></font></td>\n" .
              "  <td align=\"right\"><font size=\"2\">$translated</font></td>\n" .
              "  <td bgcolor=\"#d0d0d0\" align=\"right\"><font size=\"2\">$ptranslated2</font></td>\n" .
              "  <td align=\"right\"><font size=\"2\">$fuzzy</font></td>\n" .
              "  <td bgcolor=\"#d0d0d0\" align=\"right\"><font size=\"2\">$pfuzzy2</font></td>\n" .
              "  <td align=\"right\"><font size=\"2\">$untranslated</font></td>\n" .
              "  <td bgcolor=\"#d0d0d0\" align=\"right\"><font size=\"2\">$puntranslated2</font></td>\n" .
              "  <td align=\"right\"><font size=\"2\">$total</font></td>\n" .
              "  <td>";
	if ($ptranslated==200) {
		$output.= "<img src=\"../../img/bar0.png\" height=\"15\" width=\"200\">";
    } else if ($pfuzzy==200) {
		$output.= "<img src=\"../../img/bar4.png\" height=\"15\" width=\"200\">";
    } else if ($puntranslated==200) {
		$output.= "<img src=\"../../img/bar1.png\" height=\"15\" width=\"200\">";
	} else {	
        $output.= "<img src=\"../../img/bar0.png\" height=\"15\" width=\"$ptranslated\">" .
                  "<img src=\"../../img/bar4.png\" height=\"15\" width=\"$pfuzzy\">" .
                  "<img src=\"../../img/bar1.png\" height=\"15\" width=\"$puntranslated\">";
	}
    $output.= "</td>\n" .
              "  <td align=\"right\"><font size=\"2\">$error</font></td>\n" .
              "</tr>\n";
  } else {
    $output = "<tr bgcolor=\"#e0e0e0\">\n" .
              "  <td><font size=\"2\"><b>total</b></font></td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td bgcolor=\"#d0d0d0\" align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td bgcolor=\"#d0d0d0\" align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td bgcolor=\"#d0d0d0\" align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td>" .
                 "<img src=\"../../img/bar6.png\" height=\"15\" width=\"200\">" .
              "</td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "</tr>\n";
  }          
  return $output;
  
}

//
// generate by team status line for 2nd level status file
//
function byteam_line2($package,$error,$translated,$fuzzy,$untranslated) {
  global $okbg, $okbg2, $errorbg, $errorbg2;
  $total=$translated+$fuzzy+$untranslated;

  // color by $error
  if ($error==0) {
    $bgcolor=$okbg;
    $bgcolor2=$okbg2;
  } else {
    $bgcolor=$errorbg;
    $bgcolor2=$errorbg2;
  }

  if ($total>0) {
    $ptranslated    = (int)(100*$translated/$total);
    $pfuzzy         = (int)(100*$fuzzy/$total);
    $puntranslated  = 100-$pfuzzy-$ptranslated;
    $ptranslated    = 2 * $ptranslated;
    $pfuzzy         = 2 * $pfuzzy;
    $puntranslated  = 2 * $puntranslated;
  
    $ptranslated2   = sprintf("%2.2f",100*$translated/$total);
    $pfuzzy2        = sprintf("%2.2f",100*$fuzzy/$total);
    $puntranslated2 = sprintf("%2.2f",100-$pfuzzy2-$ptranslated2);

    $output = "<tr bgcolor=\"$bgcolor\">\n" .
              "  <td><font size=\"2\"><a href=\"$package/index.php\">$package</a></font></td>\n" .
              "  <td align=\"right\"><font size=\"2\" class=\"$class2\">$translated</font></td>\n" .
              "  <td bgcolor=\"$bgcolor2\" align=\"right\"><font size=\"2\" class=\"$class2\">$ptranslated2</font></td>\n" .
              "  <td align=\"right\"><font size=\"2\">$fuzzy</font></td>\n" .
              "  <td bgcolor=\"$bgcolor2\" align=\"right\"><font size=\"2\">$pfuzzy2</font></td>\n" .
              "  <td align=\"right\"><font size=\"2\">$untranslated</font></td>\n" .
              "  <td bgcolor=\"$bgcolor2\" align=\"right\"><font size=\"2\">$puntranslated2</font></td>\n" .
              "  <td align=\"right\"><font size=\"2\">$total</font></td>\n" .
              "  <td>";
	if ($ptranslated==200) {
		$output.= "<img src=\"../../img/bar0.png\" height=\"15\" width=\"200\">";
    } else if ($pfuzzy==200) {
		$output.= "<img src=\"../../img/bar4.png\" height=\"15\" width=\"200\">";
    } else if ($puntranslated==200) {
		$output.= "<img src=\"../../img/bar1.png\" height=\"15\" width=\"200\">";
	} else {	
        $output.= "<img src=\"../../img/bar0.png\" height=\"15\" width=\"$ptranslated\">" .
                  "<img src=\"../../img/bar4.png\" height=\"15\" width=\"$pfuzzy\">" .
                  "<img src=\"../../img/bar1.png\" height=\"15\" width=\"$puntranslated\">";
	}
    $output.= "</td>\n" .
              "  <td align=\"right\"><font size=\"2\">$error</font></td>\n" .
              "</tr>\n";
  } else {
    $output = "<tr bgcolor=\"$bgcolor\">\n" .
              "  <td><font size=\"2\"><a href=\"$package/index.php\">$package</a></font></td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td bgcolor=\"$bgcolor2\" align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td bgcolor=\"$bgcolor2\" align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td bgcolor=\"$bgcolor2\" align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td>" .
                 "<img src=\"../../img/bar6.png\" height=\"15\" width=\"200\">" .
              "</td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "</tr>\n";
  }          
  return $output;
}

//
// generate by team total line for 2nd level status file
//
function byteam_total2($error,$translated,$fuzzy,$untranslated) {
  $total=$translated+$fuzzy+$untranslated;  

  if ($total>0) {
    $ptranslated    = (int)(100*$translated/$total);
    $pfuzzy         = (int)(100*$fuzzy/$total);
    $puntranslated  = 100-$pfuzzy-$ptranslated;
    $ptranslated    = 2 * $ptranslated;
    $pfuzzy         = 2 * $pfuzzy;
    $puntranslated  = 2 * $puntranslated;
  
    $ptranslated2   = sprintf("%2.2f",100*$translated/$total);
    $pfuzzy2        = sprintf("%2.2f",100*$fuzzy/$total);
    $puntranslated2 = sprintf("%2.2f",100-$pfuzzy2-$ptranslated2);

    $output = "<tr bgcolor=\"#e0e0e0\">\n" .
              "  <td><font size=\"2\"><b>total</b></font></td>\n" .
              "  <td align=\"right\"><font size=\"2\">$translated</font></td>\n" .
              "  <td bgcolor=\"#d0d0d0\" align=\"right\"><font size=\"2\">$ptranslated2</font></td>\n" .
              "  <td align=\"right\"><font size=\"2\">$fuzzy</font></td>\n" .
              "  <td bgcolor=\"#d0d0d0\" align=\"right\"><font size=\"2\">$pfuzzy2</font></td>\n" .
              "  <td align=\"right\"><font size=\"2\">$untranslated</font></td>\n" .
              "  <td bgcolor=\"#d0d0d0\" align=\"right\"><font size=\"2\">$puntranslated2</font></td>\n" .
              "  <td align=\"right\"><font size=\"2\">$total</font></td>\n" .
              "  <td>";
	if ($ptranslated==200) {
		$output.= "<img src=\"../../img/bar0.png\" height=\"15\" width=\"200\">";
    } else if ($pfuzzy==200) {
		$output.= "<img src=\"../../img/bar4.png\" height=\"15\" width=\"200\">";
    } else if ($puntranslated==200) {
		$output.= "<img src=\"../../img/bar1.png\" height=\"15\" width=\"200\">";
	} else {	
        $output.= "<img src=\"../../img/bar0.png\" height=\"15\" width=\"$ptranslated\">" .
                  "<img src=\"../../img/bar4.png\" height=\"15\" width=\"$pfuzzy\">" .
                  "<img src=\"../../img/bar1.png\" height=\"15\" width=\"$puntranslated\">";
	}
	$output.= "</td>\n" .
              "  <td align=\"right\"><font size=\"2\">$error</font></td>\n" .
              "</tr>\n";
  } else {
    $output = "<tr bgcolor=\"#e0e0e0\">\n" .
              "  <td><font size=\"2\"><b>total</b></font></td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td bgcolor=\"#d0d0d0\" align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td bgcolor=\"#d0d0d0\" align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td bgcolor=\"#d0d0d0\" align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td>" .
                 "<img src=\"../../img/bar6.png\" height=\"15\" width=\"200\">" .
              "</td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "</tr>\n";
  }          
  return $output;
  
}


/**************************************************************************/

//
// generate by package status line  for 3rd level status file
//
function bypackage_line3($file,$error,$translated,$fuzzy,$untranslated,$teamcode,$package,$type) {
  global $rev, $okbg, $okbg2, $errorbg, $errorbg2,
         $nopotbg, $nopotbg2;
  
  $total=$translated+$fuzzy+$untranslated;  
  
  // color by with POT or without POT
  if ($type==0) {
    $bgcolor=$okbg;
    $bgcolor2=$okbg2;
  } else {
    $bgcolor=$nopotbg;
    $bgcolor2=$nopotbg2;
  }
  // color by error
  if ($error==1) {
    $bgcolor=$errorbg;
    $bgcolor2=$errorbg2;
  }
  
  $cvsurl=cvsweburl($rev,$teamcode,$package,$file);
   
  if ($total>0) {
    $ptranslated    = (int)(100*$translated/$total);
    $pfuzzy         = (int)(100*$fuzzy/$total);
    $puntranslated  = 100-$pfuzzy-$ptranslated;
    $ptranslated    = 2 * $ptranslated;
    $pfuzzy         = 2 * $pfuzzy;
    $puntranslated  = 2 * $puntranslated;

    $ptranslated2   = sprintf("%2.2f",100*$translated/$total);
    $pfuzzy2        = sprintf("%2.2f",100*$fuzzy/$total);
    $puntranslated2 = sprintf("%2.2f",100-$pfuzzy2-$ptranslated2);
  
    $output = "<tr bgcolor=\"$bgcolor\">\n" .
              "  <td><font size=\"2\"><a href=\"$cvsurl\">$file</a></font></td>\n" .
              "  <td align=\"right\"><font size=\"2\">$translated</font></td>\n" .
              "  <td bgcolor=\"$bgcolor2\" align=\"right\"><font size=\"2\">$ptranslated2</font></td>\n" .
              "  <td align=\"right\"><font size=\"2\">$fuzzy</font></td>\n" .
              "  <td bgcolor=\"$bgcolor2\" align=\"right\"><font size=\"2\">$pfuzzy2</font></td>\n" .
              "  <td align=\"right\"><font size=\"2\">$untranslated</font></td>\n" .
              "  <td bgcolor=\"$bgcolor2\" align=\"right\"><font size=\"2\">$puntranslated2</font></td>\n" .
              "  <td align=\"right\"><font size=\"2\">$total</font></td>\n" .
              "  <td>";
	if ($ptranslated==200) {
		$output.= "<img src=\"../../../img/bar0.png\" height=\"15\" width=\"200\">";
    } else if ($pfuzzy==200) {
		$output.= "<img src=\"../../../img/bar4.png\" height=\"15\" width=\"200\">";
    } else if ($puntranslated==200) {
		$output.= "<img src=\"../../../img/bar1.png\" height=\"15\" width=\"200\">";
	} else {	
        $output.= "<img src=\"../../../img/bar0.png\" height=\"15\" width=\"$ptranslated\">" .
                  "<img src=\"../../../img/bar4.png\" height=\"15\" width=\"$pfuzzy\">" .
                  "<img src=\"../../../img/bar1.png\" height=\"15\" width=\"$puntranslated\">";
	}
    $output.= "</td>\n" .
              "</tr>\n";
  } else {
    $output = "<tr bgcolor=\"$bgcolor\">\n" .
              "  <td><font size=\"2\"><a href=\"$cvsurl\">$file</a></font></td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td bgcolor=\"$bgcolor2\" align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td bgcolor=\"$bgcolor2\" align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td bgcolor=\"$bgcolor2\" align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td>" .
                 "<img src=\"../../../img/bar6.png\" height=\"15\" width=\"200\">" .
              "</td>\n" .
              "</tr>\n";
  }          
  return $output;
}


//
// generate by package total line for 3rd level status file
//
function bypackage_total3($error,$translated,$fuzzy,$untranslated) {
  $total=$translated+$fuzzy+$untranslated;  

  if ($total>0) {
    $ptranslated    = (int)(100*$translated/$total);
    $pfuzzy         = (int)(100*$fuzzy/$total);
    $puntranslated  = 100-$pfuzzy-$ptranslated;
    $ptranslated    = 2 * $ptranslated;
    $pfuzzy         = 2 * $pfuzzy;
    $puntranslated  = 2 * $puntranslated;
  
    $ptranslated2   = sprintf("%2.2f",100*$translated/$total);
    $pfuzzy2        = sprintf("%2.2f",100*$fuzzy/$total);
    $puntranslated2 = sprintf("%2.2f",100-$pfuzzy2-$ptranslated2);

    $output = "<tr bgcolor=\"#e0e0e0\">\n" .
              "  <td><font size=\"2\"><b>total</b></font></td>\n" .
              "  <td align=\"right\"><font size=\"2\">$translated</font></td>\n" .
              "  <td bgcolor=\"#d0d0d0\" align=\"right\"><font size=\"2\">$ptranslated2</font></td>\n" .
              "  <td align=\"right\"><font size=\"2\">$fuzzy</font></td>\n" .
              "  <td bgcolor=\"#d0d0d0\" align=\"right\"><font size=\"2\">$pfuzzy2</font></td>\n" .
              "  <td align=\"right\"><font size=\"2\">$untranslated</font></td>\n" .
              "  <td bgcolor=\"#d0d0d0\" align=\"right\"><font size=\"2\">$puntranslated2</font></td>\n" .
              "  <td align=\"right\"><font size=\"2\">$total</font></td>\n" .
              "  <td>";
	if ($ptranslated==200) {
		$output.= "<img src=\"../../../img/bar0.png\" height=\"15\" width=\"200\">";
    } else if ($pfuzzy==200) {
		$output.= "<img src=\"../../../img/bar4.png\" height=\"15\" width=\"200\">";
    } else if ($puntranslated==200) {
		$output.= "<img src=\"../../../img/bar1.png\" height=\"15\" width=\"200\">";
	} else {	
        $output.= "<img src=\"../../../img/bar0.png\" height=\"15\" width=\"$ptranslated\">" .
                  "<img src=\"../../../img/bar4.png\" height=\"15\" width=\"$pfuzzy\">" .
                  "<img src=\"../../../img/bar1.png\" height=\"15\" width=\"$puntranslated\">";
	}
    $output.= "</td>\n" .
              "</tr>\n";
  } else {
    $output = "<tr bgcolor=\"#e0e0e0\">\n" .
              "  <td><font size=\"2\"><b>total</b></font></td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td bgcolor=\"#d0d0d0\" align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td bgcolor=\"#d0d0d0\" align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td bgcolor=\"#d0d0d0\" align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td>" .
                 "<img src=\"../../../img/bar6.png\" height=\"15\" width=\"200\">" .
              "</td>\n" .
              "</tr>\n";
  }          
  return $output;
  
}


//
// generate by team status line  for 3rd level status file
//
function byteam_line3($file,$error,$translated,$fuzzy,$untranslated,$teamcode,$package,$type) {
  global $rev, $okbg, $okbg2, $errorbg, $errorbg2,
         $nopotbg, $nopotbg2;
  
  $total=$translated+$fuzzy+$untranslated;  
  
  // color by with POT or without POT
  if ($type==0) {
    $bgcolor=$okbg;
    $bgcolor2=$okbg2;
  } else {
    $bgcolor=$nopotbg;
    $bgcolor2=$nopotbg2;
  }
  // color by error
  if ($error==1) {
    $bgcolor=$errorbg;
    $bgcolor2=$errorbg2;
  }
  
  $cvsurl=cvsweburl($rev,$teamcode,$package,$file);
  
  if ($total>0) {
    $ptranslated    = (int)(100*$translated/$total);
    $pfuzzy         = (int)(100*$fuzzy/$total);
    $puntranslated  = 100-$pfuzzy-$ptranslated;
    $ptranslated    = 2 * $ptranslated;
    $pfuzzy         = 2 * $pfuzzy;
    $puntranslated  = 2 * $puntranslated;

    $ptranslated2   = sprintf("%2.2f",100*$translated/$total);
    $pfuzzy2        = sprintf("%2.2f",100*$fuzzy/$total);
    $puntranslated2 = sprintf("%2.2f",100-$pfuzzy2-$ptranslated2);
  
    $output = "<tr bgcolor=\"$bgcolor\">\n" .
              "  <td><font size=\"2\"><a href=\"$cvsurl\">$file</a></font></td>\n" .
              "  <td align=\"right\"><font size=\"2\">$translated</font></td>\n" .
              "  <td bgcolor=\"$bgcolor2\" align=\"right\"><font size=\"2\">$ptranslated2</font></td>\n" .
              "  <td align=\"right\"><font size=\"2\">$fuzzy</font></td>\n" .
              "  <td bgcolor=\"$bgcolor2\" align=\"right\"><font size=\"2\">$pfuzzy2</font></td>\n" .
              "  <td align=\"right\"><font size=\"2\">$untranslated</font></td>\n" .
              "  <td bgcolor=\"$bgcolor2\" align=\"right\"><font size=\"2\">$puntranslated2</font></td>\n" .
              "  <td align=\"right\"><font size=\"2\">$total</font></td>\n" .
              "  <td>";
	if ($ptranslated==200) {
		$output.= "<img src=\"../../../img/bar0.png\" height=\"15\" width=\"200\">";
    } else if ($pfuzzy==200) {
		$output.= "<img src=\"../../../img/bar4.png\" height=\"15\" width=\"200\">";
    } else if ($puntranslated==200) {
		$output.= "<img src=\"../../../img/bar1.png\" height=\"15\" width=\"200\">";
	} else {	
        $output.= "<img src=\"../../../img/bar0.png\" height=\"15\" width=\"$ptranslated\">" .
                  "<img src=\"../../../img/bar4.png\" height=\"15\" width=\"$pfuzzy\">" .
                  "<img src=\"../../../img/bar1.png\" height=\"15\" width=\"$puntranslated\">";
	}
    $output.= "</td>\n" .
              "</tr>\n";
  } else {
    $output = "<tr bgcolor=\"$bgcolor\">\n" .
              "  <td><font size=\"2\"><a href=\"$cvsurl\">$file</a></font></td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td bgcolor=\"$bgcolor2\" align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td bgcolor=\"$bgcolor2\" align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td bgcolor=\"$bgcolor2\" align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td>" .
                 "<img src=\"../../../img/bar6.png\" height=\"15\" width=\"200\">" .
              "</td>\n" .
              "</tr>\n";
  }          
  return $output;
}


//
// generate by team total line for 3rd level status file
//
function byteam_total3($error,$translated,$fuzzy,$untranslated) {
  $total=$translated+$fuzzy+$untranslated;  

  if ($total>0) {
    $ptranslated    = (int)(100*$translated/$total);
    $pfuzzy         = (int)(100*$fuzzy/$total);
    $puntranslated  = 100-$pfuzzy-$ptranslated;
    $ptranslated    = 2 * $ptranslated;
    $pfuzzy         = 2 * $pfuzzy;
    $puntranslated  = 2 * $puntranslated;
  
    $ptranslated2   = sprintf("%2.2f",100*$translated/$total);
    $pfuzzy2        = sprintf("%2.2f",100*$fuzzy/$total);
    $puntranslated2 = sprintf("%2.2f",100-$pfuzzy2-$ptranslated2);

    $output = "<tr bgcolor=\"#e0e0e0\">\n" .
              "  <td><font size=\"2\"><b>total</b></font></td>\n" .
              "  <td align=\"right\"><font size=\"2\">$translated</font></td>\n" .
              "  <td bgcolor=\"#d0d0d0\" align=\"right\"><font size=\"2\">$ptranslated2</font></td>\n" .
              "  <td align=\"right\"><font size=\"2\">$fuzzy</font></td>\n" .
              "  <td bgcolor=\"#d0d0d0\" align=\"right\"><font size=\"2\">$pfuzzy2</font></td>\n" .
              "  <td align=\"right\"><font size=\"2\">$untranslated</font></td>\n" .
              "  <td bgcolor=\"#d0d0d0\" align=\"right\"><font size=\"2\">$puntranslated2</font></td>\n" .
              "  <td align=\"right\"><font size=\"2\">$total</font></td>\n" .
              "  <td>";
 	if ($ptranslated==200) {
		$output.= "<img src=\"../../../img/bar0.png\" height=\"15\" width=\"200\">";
    } else if ($pfuzzy==200) {
		$output.= "<img src=\"../../../img/bar4.png\" height=\"15\" width=\"200\">";
    } else if ($puntranslated==200) {
		$output.= "<img src=\"../../../img/bar1.png\" height=\"15\" width=\"200\">";
	} else {	
        $output.= "<img src=\"../../../img/bar0.png\" height=\"15\" width=\"$ptranslated\">" .
                  "<img src=\"../../../img/bar4.png\" height=\"15\" width=\"$pfuzzy\">" .
                  "<img src=\"../../../img/bar1.png\" height=\"15\" width=\"$puntranslated\">";
	}
    $output.= "</td>\n" .
              "</tr>\n";
  } else {
    $output = "<tr bgcolor=\"#e0e0e0\">\n" .
              "  <td><font size=\"2\"><b>total</b></font></td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td bgcolor=\"#d0d0d0\" align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td bgcolor=\"#d0d0d0\" align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td bgcolor=\"#d0d0d0\" align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td align=\"right\"><font size=\"2\"><nobr>n/a</nobr></font></td>\n" .
              "  <td>" .
                 "<img src=\"../../../img/bar6.png\" height=\"15\" width=\"200\">" .
              "</td>\n" .
              "</tr>\n";
  }          
  return $output;
  
}

/**************************************************************************/

//
// generate HTTP URL for PO or POT filename pointing in KDE CVS web interface
//
function cvsweburl($rev,$teamcode,$package,$file) {
  global $cvswebformat1, $cvswebformat2;
  
  // action regarding PO / POT file type
  if ($package=="squirrelmail") {
    $package_dir="";
  } else {
    $package_dir="$package/";
  }
  if ($rev=="HEAD") {
    $cvswebformat=$cvswebformat1;
  } else {
    $cvswebformat=$cvswebformat2;
  }
  if (preg_match("/t$/",$file)) {
    $cvsurl=sprintf($cvswebformat,$rev,"po/$package_dir$file");
  } else {
    $cvsurl=sprintf($cvswebformat,$rev,"locale/$teamcode/LC_MESSAGES/$package_dir$file");
  }
  return $cvsurl;
}

//
// generate a line for a KDE translation team
//
function make_teamlist($teamcode,$teamname,$level) {
  switch ($level) {
    case 1: 
      $dir= "../";
    break;
    case 2: 
      $dir= "../../";
    break;
    default:
      $dir= "";
  }
  $output = "<tr bgcolor=\"#d0e0ff\">" .
            "  <td><font size=\"2\"><a class=\"package\" href=\"$dir$teamcode/index.php\">$teamname</a> <font size=\"1\">($teamcode)</font></font></td>\n" .
            "</tr>\n";
  return $output;
}

//
// generate a bar with KDE packages 
//
function make_packagelist($name,$level) {
  switch ($level) {
    case 1: 
      $dir= "../";
    break;
    case 2: 
      $dir= "../../";
    break;
    default:
      $dir= "";
  }
  $output = "<tr bgcolor=\"#d0e0ff\">" .
            "  <td><font size=\"2\"><a class=\"package\" href=\"$dir$name/index.php\">$name</a></font></td>\n" .
            "</tr>\n";
  return $output;
}

//
// write the given text into file
//
function writefile($outfile,$output) {
  if ($fh = @fopen($outfile,"w")) {
    fwrite($fh,$output);
    fclose($fh);
  } else {
    send_err("writefile(): Cannot open $outfile file for write!");
    exit();
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

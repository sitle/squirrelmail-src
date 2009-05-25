#!/usr/bin/php -q
<?php

////////////////////////////////////////////////////////////////////////////
// $Id$
//
// Description: Grab GUI messages statistics
//
////////////////////////////////////////////////////////////////////////////


dl('mysql.so');
include("grab-functions.php");
include("grab-config.php");


// ************* PRELIMINARY TASKS, DATA INIT *************
set_time_limit(0);

// get for which revision we grab stats
if ($argv[1]=="") {
  $rev="HEAD";
} else {
  $rev=$argv[1];
};

$podir       = "$basedir/$rev/locales/locale";
$potdir      = "$basedir/$rev/locales/po";
$teamnames   = "$basedir/$rev/locales/teamnames";
$essential_f = "$basedir/$rev/locales/essential_files";
$essential_p = "$basedir/$rev/locales/essential_packages";
$packages_f  = "$basedir/$rev/locales/guistats_packages";
$excludes_f  = "$basedir/$rev/locales/guistats_excludes";


// preliminary checks
if (!is_dir($podir)) {
  send_err("Cannot acces $podir directory!");
  exit();
}
if (!is_dir($potdir)) {
  send_err("Cannot acces $potdir directory!");
  exit();
}
if (!is_readable($teamnames)) {
  send_err("Cannot acces $teamnames file!");
  exit();
}
if (!is_readable($essential_f)) {
  send_err("Cannot acces $essential_f file!");
  exit();
}
if (!is_readable($essential_p)) {
  send_err("Cannot acces $essential_p file!");
  exit();
}
if (!is_readable($packages_f)) {
  send_err("Cannot acces $packages_f file!");
  exit();
}
if (!is_readable($excludes_f)) {
  send_err("Cannot acces $excludes_f file!");
  exit();
}

// data initialization
$currdate = date("Y-m-d",mktime());
$teams    = get_config1($teamnames);
$ess_f    = get_config1($essential_f);
$ess_p    = get_config1($essential_p);
$packages = get_config2($packages_f);
$excludes = get_config2($excludes_f);
// customize the fake kdelibs package name
$packages = array_map("my_replace",$packages);

// open database connection
$dbh=initdb($sql_host,$sql_user,$sql_pass,$sql_db);


// ************* DATA PROCESSING *************

// erase old GUI statistics data
debug(40,"emptying database tables");
$res=@mysql_query("DELETE FROM sum WHERE rev='$rev'",$dbh);
if (!$res) {
  send_err("SQL error: delete sum!");
  exit();
}
$res=@mysql_query("DELETE FROM stats WHERE rev='$rev'",$dbh);
if (!$res) {
  send_err("SQL error: delete stats!");
  exit();
}
$res=@mysql_query("DELETE FROM teams WHERE rev='$rev'",$dbh);
if (!$res) {
  send_err("SQL error: delete teams!");
  exit();
}
$res=@mysql_query("DELETE FROM essential WHERE sdate='$currdate' AND rev='$rev'",$dbh);
if (!$res) {
  send_err("SQL error: delete essential!");
  exit();
}



// map team code - team name in database
debug(40,"save in database team names");
foreach ($teams as $teamcode => $teamname) {
  $res=@mysql_query("INSERT INTO teams SET rev='$rev', " .
              " teamcode='$teamcode', teamname='$teamname'"
              ,$dbh);
  if (!$res) {
    send_err("SQL error: insert teams teamcode=$teamcode");
  }
}

// intialize associative array with essential files
$essential_f=array();
foreach (array_keys($ess_f) as $file) {
  $essential_f[$file]=array(0,0);
}
$essential_p=array();
foreach (array_keys($ess_p) as $package) {
  $essential_p[$package]=array(0,0);
}
// grab POT messages data
debug(40,"grabing POT statistics");
$teamdir=$potdir; // note: it's a fake team
// process packages directories
foreach ($packages as $package) {
  debug(15,"level2:   statistics in templates:$package");
  $total_error       =0;
  $total_translated  =0;
  $total_fuzzy       =0;
  $total_untranslated=0;

  if (($package!=$kdefake && is_dir("$teamdir/$package")) || ($package==$kdefake && is_dir($teamdir))) {
    $files=array();
    $fullpotdir= ($package==$kdefake) ? "$potdir" : "$potdir/$package";
    if ($dh=@opendir($fullpotdir)) {
      $files=array();
      while ($file=readdir($dh)) {
        if (preg_match("/\.pot$/",$file) && !in_array(substr($file,0,strlen($file)-1),$excludes)) {
          array_push($files,$file);
        }
      }
      closedir($dh);
    } else {
      // directory have no POT files: likely to be an CVS mess
      $res=@mysql_query("INSERT INTO sum SET rev='$rev', "         .
                   " team='templates', package='$package', "       .
                   " translated='0', fuzzy='0', "                  .
                   " untranslated='0', total='0', "                .
                   " error='0'"
                   ,$dbh);
      if (!$res) {
        send_err("SQL error: sum POT grabbing package=$package");
        exit();
      }
    }

    foreach ($files as $filename) {
      $potfile = ($package==$kdefake) ? "$potdir/$filename" : "$potdir/$package/$filename";
      $have_po=0;
      if (is_readable($potfile)) {
        debug(20,"level3:    [templates:$package] POT: $filename");
        $have_pot=1;
        list($error,$translated,$fuzzy,$untranslated)=getstats($potfile);
      } else {
        debug(20,"level3:    [templates:$package] POT: $filename (NO ACCESS!)");
        // file permissions are strange!!
        $error=0;
        $translated=0;
        $fuzzy=0;
        $untranslated=0;
        $have_pot=0;
      }
      $total=$translated+$fuzzy+$untranslated;
      $res=@mysql_query("INSERT INTO stats SET rev='$rev', "                         .
                   " team='templates', package='$package', "                         .
                   " filename='$filename', translated='$translated', "               .
                   " fuzzy='$fuzzy', untranslated='$untranslated', total='$total', " .
                   " error='$error', have_po='$have_po', have_pot='$have_pot'"
                   ,$dbh);
      if (!$res) {
        send_err("SQL error: stats POT grabbing 1 package=$package; filename=$filename");
        exit();
      }

      // fake essential files stats: percentage & total untranslated in fact
      $filename=substr($filename,0,strlen($filename)-1);
      $percentage=$ess_f[$filename];
      if (in_array($filename,array_keys($ess_f))) {
        $essential_f[$filename]=array($percentage,$total);
      }
      // sumarize for entire package
      $total_error        += $error;
      $total_translated   += $translated;
      $total_fuzzy        += $fuzzy;
      $total_untranslated += $untranslated;
    }
  } else {
    // the team's package dir doesn't exist or is not accessible
    debug(20,"level3:   statistics in templates:$package (NODIR)");
    // insert null values to catch directory access errors
    $res=@mysql_query("INSERT INTO sum SET rev='$rev', "                       .
                 " team='templates', package='$package', "                     .
                 " translated='0', fuzzy='0', "                                .
                 " untranslated='0', total='0', error='0'"
                 ,$dbh);
    if (!$res) {
      send_err("SQL error: sum POT grabbing 2 package=$package");
      exit();
    }
  }
  // write sumary in "sum" table
  $total_total=$total_translated+$total_fuzzy+$total_untranslated;
  $res=@mysql_query("INSERT INTO sum SET rev='$rev', "                       .
               " team='templates', package='$package', "                     .
               " translated='$total_translated', fuzzy='$total_fuzzy', "     .
               " untranslated='$total_untranslated', total='$total_total', " .
               " error='$total_error'"
               ,$dbh);
  if (!$res) {
    send_err("SQL error: sum POT grabbing 2 package=$package");
    exit();
  }
  
  // build essential packages stats
  if (in_array($package,array_keys($ess_p))) {
    $total_translated=$ess_p[$package];
    $essential_p[$package]=array($total_translated,$total_total);
  }
}

// insert in database associative array for essential files
foreach (array_keys($ess_f) as $file) {
  list($translated,$total)=$essential_f[$file];
  $res=@mysql_query("INSERT INTO essential SET rev='$rev', "    .
               " sdate='$currdate', team='templates', "         .
               " filename='$file', translated='$translated', "  .
               " total='$total' "
               ,$dbh);
  if (!$res) {
    send_err("SQL error: essential POT grabbing 1 filename=$file");
    exit();
  }
}
foreach (array_keys($ess_p) as $package) {
  list($translated,$total)=$essential_p[$package];
  $res=@mysql_query("INSERT INTO essential SET rev='$rev', "       .
               " sdate='$currdate', team='templates', "            .
               " filename='$package', translated='$translated', "  .
               " total='$total' "
               ,$dbh);
  if (!$res) {
    send_err("SQL error: essential POT grabbing 2 filename=$package");
  }
}



// grab PO messages data
debug(40,"grabing PO statistics");
foreach ($teams as $teamcode => $teamname) {
  debug(10,"level1: statistics in $teamcode");
  $teamdir="$podir/$teamcode/LC_MESSAGES";
  
  // intialize associative array with essential files
  $essential_f=array();
  foreach (array_keys($ess_f) as $file) {
    $essential_f[$file]=array(0,0);
  }
  $essential_p=array();
  foreach (array_keys($ess_p) as $package) {
    $essential_p[$package]=array(0,0);
  }
  
  // process packages directories
  foreach ($packages as $package) {
    debug(15,"level2:   statistics in $teamcode:$package");
    $total_error       =0;
    $total_translated  =0;
    $total_fuzzy       =0;
    $total_untranslated=0;
    
    if (($package!=$kdefake && is_dir("$teamdir/$package")) || ($package==$kdefake && is_dir($teamdir))) {
      // fill the array with POT files for current package
      $files=array();
      $fullpotdir= ($package==$kdefake) ? "$potdir" : "$potdir/$package";
      if ($dh=@opendir($fullpotdir)) {
        $files=array();
        while ($file=readdir($dh)) {
          if (preg_match("/\.pot$/",$file) && !in_array($file,$excludes)) {
            array_push($files,$file);
          }
        }
        closedir($dh);
      } 
      
      // 1) check if POT files have corresponding translated PO files
      foreach ($files as $file) {
        $pofile2 = $pofile  = substr($file,0,strlen($file)-1);
        $pofile  = ($package==$kdefake) ? "$teamdir/$pofile" : "$teamdir/$package/$pofile";
        $potfile = ($package==$kdefake) ? "$potdir/$file" : "$potdir/$package/$file";
        if (is_readable($pofile)) {
          debug(20, "level3:    [$teamcode:$package] PO:    $pofile2");
          $file2=substr($file,0,strlen($file)-1);
          $filename=substr($file,0,strlen($file)-1);
          $have_po=1;
          $have_pot=1;
          list($error,$translated,$fuzzy,$untranslated)=getstats($pofile);
        } else {
          debug(20,"level3:    [$teamcode:$package] POT:   $file");
          $file2=$file;
          $filename=$file;
          $have_po=0;
          $have_pot=1;
          list($error,$translated,$fuzzy,$untranslated)=getstats($potfile);
        }
        $total=$translated+$fuzzy+$untranslated;
        $res=@mysql_query("INSERT INTO stats SET rev='$rev', "                         .
                     " team='$teamcode', package='$package', "                         .
                     " filename='$filename', translated='$translated', "               .
                     " fuzzy='$fuzzy', untranslated='$untranslated', total='$total', " .
                     " error='$error', have_po='$have_po', have_pot='$have_pot'"
                     ,$dbh);
        if (!$res) {
          send_err("SQL error: stats PO grabbing 1 team=$teamcode; filename='$filename");
          exit();
        }
        
        // build essential files stats
        $filename=substr($file,0,strlen($file)-1);
        if (in_array($filename,array_keys($ess_f))) {
          $essential_f[$filename]=array($translated,$total);
        }
        // sumarize for entire package
        $total_error        += $error;
        $total_translated   += $translated;
        $total_fuzzy        += $fuzzy;
        $total_untranslated += $untranslated;
      }
      
      // fill the array with PO files for current package
      $files=array();
      $fullpodir= ($package==$kdefake) ? "$teamdir" : "$teamdir/$package";
      if ($dh=@opendir($fullpodir)) {
        $files=array();
        while ($file=readdir($dh)) {
          if (preg_match("/\.po$/",$file) && !in_array($file."t",$excludes)) {
            array_push($files,$file);
          }
        }
        closedir($dh);
      } 
      
      // 2) check what POs don't have corresponding POTs
      foreach ($files as $file) {
        $pofile  = ($package==$kdefake) ? "$teamdir/$file" : "$teamdir/$package/$file";
        $potfile = ($package==$kdefake) ? "$potdir/${file}t" : "$potdir/$package/${file}t";
        if (!is_readable($potfile)) {
          debug(20,"level3:    [$teamcode:$package] NOPOT: $file");
          list($error,$translated,$fuzzy,$untranslated)=getstats($pofile);
          $total=$translated+$fuzzy+$untranslated;
          $res=@mysql_query("INSERT INTO stats SET rev='$rev', "            .
                       " team='$teamcode', package='$package', "            .
                       " filename='$file', translated='$translated', "      .
                       " fuzzy='$fuzzy', untranslated='$untranslated', "    .
                       " total='$total', error='$error', have_po='1', "     .
                       "have_pot='0'"
                       ,$dbh);
          if (!$res) {
            send_err("SQL error: stats PO grabbing 2 team=$teamcode; package=$package");
            exit();
          }
        }
      }
    } else {
      // the team's package dir doesn't exist or is not accessible
      debug(20,"level3:   statistics in $teamcode:$package (NODIR)");
    }
    // write sumary in "sum" table
    $total_total=$total_translated+$total_fuzzy+$total_untranslated;
    $res=@mysql_query("INSERT INTO sum SET rev='$rev', "                       .
                 " team='$teamcode', package='$package', "                     .
                 " translated='$total_translated', fuzzy='$total_fuzzy', "     .
                 " untranslated='$total_untranslated', total='$total_total', " .
                 " error='$total_error'"
                 ,$dbh);
    if (!$res) {
      send_err("SQL error: sum PO grabbing team=$teamcode; package=$package");
      exit();
    }  

    // build essential packages stats
    if (in_array($package,array_keys($ess_p))) {
      $essential_p[$package]=array($total_translated,$total_total);
    }
  }

  // insert in database associative array for essential files
  foreach (array_keys($ess_f) as $file) {
    list($translated,$total)=$essential_f[$file];
    $res=@mysql_query("INSERT INTO essential SET rev='$rev', "    .
                 " sdate='$currdate', team='$teamcode', "         .
                 " filename='$file', translated='$translated', "  .
                 " total='$total' "
                 ,$dbh);
    if (!$res) {
      send_err("SQL error: essential PO grabbing 1 team=$teamcode; filename=$file");
      exit();
    }
  }
  foreach (array_keys($ess_p) as $package) {
    list($translated,$total)=$essential_p[$package];
    $res=@mysql_query("INSERT INTO essential SET rev='$rev', "       .
                 " sdate='$currdate', team='$teamcode', "            .
                 " filename='$package', translated='$translated', "  .
                 " total='$total' "
                 ,$dbh);
    if (!$res) {
      send_err("SQL error: essential PO grabbing 2 team=$teamcode; filename=$package");
      exit();
    }
  }
}

closedb($dbh);

// send_ok("Success grabbing statistics for $rev branch.");

?>

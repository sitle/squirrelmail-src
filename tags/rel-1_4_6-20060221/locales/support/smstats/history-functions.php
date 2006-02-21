<?php

////////////////////////////////////////////////////////////////////////////
// $Id$
//
// Description: common functions for GUI messages history statistics
//
////////////////////////////////////////////////////////////////////////////



/**************************************************************************/


//
// formating a date returned by MySQL
//
function pretty_date($date="1970-01-01") {
    $year=substr($date,0,4);
    $month=substr($date,5,2);
    $day=substr($date,8,2);
		return date("d M Y",mktime(0,0,0,$month,$day,$year));
}

/**************************************************************************/


//
// initialize graphs classes
//
function init_graphs() {
	global $rundate;
	
	// Create the graphs
	$tdate1 = new Text();
	$tdate1->SetFont(FF_ARIAL,FS_NORMAL,8);
	$tdate1->SetColor("#000000");
	$tdate1->Set("generated at: $rundate");
	$tdate1->SetBox("#ffffff","#8b898b","#aaaaaa",0,0);
	$tdate1->SetPos(280,220);

	$tdate2 = new Text();
	$tdate2->SetFont(FF_ARIAL,FS_NORMAL,8);
	$tdate2->SetColor("#000000");
	$tdate2->Set("generated at: $rundate");
	$tdate2->SetBox("#ffffff","#8b898b","#aaaaaa",0,0);
	$tdate2->SetPos(560,420);

	// ** ------------- **
	$graph1 = new Graph(470,250,"auto");
	$graph1->SetScale("textlin");
	$graph1->SetShadow();
	$graph1->SetBox();
	$graph1->img->SetMargin(60,30,30,80);
	$graph1->SetMarginColor('#ececec');
	$graph1->title->SetFont(FF_ARIAL,FS_BOLD,12);
	$graph1->AddText($tdate1);

	// --------------

	$graph1->ygrid->Show(true,true);
	$graph1->yaxis->title->SetFont(FF_ARIAL,FS_BOLD,10);
	$graph1->yaxis->title->SetAngle(90);
	$graph1->yaxis->SetTitleMargin(40);
	$graph1->yaxis->title->Text("Translated Messages","high");

	$graph1->yaxis->SetFont(FF_ARIAL,FS_NORMAL,8);
	$graph1->yaxis->SetLabelAngle(0);
	$graph1->yaxis->SetPos('min');

	// -------------

	$graph1->xgrid->Show(false,false);
	$graph1->xaxis->title->SetFont(FF_ARIAL,FS_BOLD,10);
	$graph1->xaxis->title->SetAngle(0);
	$graph1->xaxis->title->Text("Days");

	$graph1->xaxis->SetFont(FF_ARIAL,FS_NORMAL,8);
	$graph1->xaxis->SetLabelAngle(30);
	$graph1->xaxis->SetTextLabelInterval(10);
	$graph1->xaxis->SetPos('min');

	$sline = new PlotLine(HORIZONTAL,0,"black",1);
	$graph1->Add($sline);

	// ** ------------- **
	$graph2 = new Graph(750,450,"auto");
	$graph2->SetScale("textlin");
	$graph2->SetShadow();
	$graph2->SetBox();
	$graph2->img->SetMargin(60,30,30,80);
	$graph2->SetMarginColor('#ececec');
	$graph2->title->SetFont(FF_ARIAL,FS_BOLD,12);
	$graph2->AddText($tdate2);

	// --------------

	$graph2->ygrid->Show(true,true);
	$graph2->yaxis->title->SetFont(FF_ARIAL,FS_BOLD,10);
	$graph2->yaxis->title->SetAngle(90);
	$graph2->yaxis->SetTitleMargin(40);
	$graph2->yaxis->title->Text("Translated Messages","high");

	$graph2->yaxis->SetFont(FF_ARIAL,FS_NORMAL,8);
	$graph2->yaxis->SetLabelAngle(0);
	$graph2->yaxis->SetPos('min');

	// -------------

	$graph2->xgrid->Show(true,false);
	$graph2->xaxis->title->SetFont(FF_ARIAL,FS_BOLD,10);
	$graph2->xaxis->title->SetAngle(0);
	$graph2->xaxis->title->Text("Days");

	$graph2->xaxis->SetFont(FF_ARIAL,FS_NORMAL,8);
	$graph2->xaxis->SetLabelAngle(30);
	$graph2->xaxis->SetTextLabelInterval(10);
	$graph2->xaxis->SetPos('min');

	$sline = new PlotLine(HORIZONTAL,0,"black",1);
	$graph2->Add($sline);

	return array($graph1,$graph2);
}

//
// initialize graphs classes
//
function destroy_graphs($list) {
	foreach($list as $item) {
		if (isset($item)) {
			unset($item);
		}
	}
}



//
// make history graph by revision for essential files(level 0)
//
function make_historybyrev($outfile1="",$outfile2="") {
  global $dbh, $rev, $m_teams;
  
  debug(10,"making history graph by CVS branch");
  
  $results=@mysql_query("SELECT sdate, SUM(translated) AS translated, SUM(total) AS total " .
                        " FROM essential WHERE rev='$rev' AND team<>'templates' " .
                        " GROUP BY sdate ORDER BY sdate LIMIT 0,60"
                        ,$dbh);
  if (!$results) {
    send_err("SQL error: historybyrev rev=$rev; team<>templates");
    exit();
  }
  
  $points_x=array();
  $points_y=array();
  $my_y=-1;
  while ($row=@mysql_fetch_array($results)) {
    array_push($points_x,pretty_date($row['sdate']));
    
    if ($my_y==-1) { 
      $my_y=$row['translated'];
    } else {
			if ($my_y==-1) $my_y=0;
      array_push($points_y,$row['translated']-$my_y);
      $my_y=$row['translated'];
    }
  }

	list($graph1,$graph2)=init_graphs();

	$lineplot=new LinePlot($points_y);
	$lineplot->SetColor('blueviolet');
	$lineplot->SetStepStyle();

	$graph1->xaxis->SetTickLabels($points_x); 
	$graph1->Add($lineplot);
	$graph1->title->Set("Essential files history for $rev branch");
	$graph1->Stroke($outfile1);


	$graph2->xaxis->SetTickLabels($points_x); 
	$graph2->Add($lineplot);
	$graph2->title->Set("Essential files history for $rev branch");
	$graph2->Stroke($outfile2);

	destroy_graphs(array($graph1,$graph2));
}



/**************************************************************************/

//
// make history graph by team for essential files (level 1)
//
function make_historybyteam($teamcode="") {
  global $dbh, $rev, $outdir, $m_teams;
  
  $results=@mysql_query("SELECT sdate, SUM(translated) AS translated, SUM(total) AS total " .
                        " FROM essential WHERE rev='$rev' AND team='$teamcode' " .
                        " GROUP BY sdate ORDER BY sdate LIMIT 0,60"
                        ,$dbh);
  if (!$results) {
   send_err("SQL error: historybyrev rev=$rev; teamcode=$teamcode");
   exit();
  }
  
  $points_x=array();
  $points_y=array();
  $my_y=-1;
  while ($row=@mysql_fetch_array($results)) {
    $points_x[]=pretty_date($row['sdate']);
    
    if ($my_y==-1) { 
      $my_y=$row['translated'];
    } else {
			if ($my_y==-1) $my_y=0;
      $points_y[]=$row['translated']-$my_y;
      $my_y=$row['translated'];
    }
  }
	
	if (count($points_y)<1) return;
	
	$teamname=$m_teams[$teamcode];
	
  list($graph1,$graph2)=init_graphs();
	
	$lineplot=new LinePlot($points_y);
	$lineplot->SetColor('blueviolet');
	$lineplot->SetStepStyle();

	$graph1->xaxis->SetTickLabels($points_x); 
	$graph1->Add($lineplot);
	$graph1->title->Set("Essential files history for $teamname team");
	$graph1->Stroke("$outdir/$rev/$teamcode/essential.png");


	$graph2->xaxis->SetTickLabels($points_x); 
	$graph2->Add($lineplot);
	$graph2->title->Set("Essential files history for $teamname team");
	$graph2->Stroke("$outdir/$rev/$teamcode/essential-big.png");

  destroy_graphs(array($graph1,$graph2));
}


/**************************************************************************/

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

<?php
/*
 *  functions.php
 *
 *
 * Copyright (c) 2002 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * Originally contrubuted by Michal Szczotka <michal@tuxy.org>
 *
 *  miscelenous functions.
 *
 * $Id$
 */


function calendar_header() {
    //Add Second layer ofCalendar links to upper menu
    global $color,$year,$day,$month;

    echo "<TABLE BGCOLOR=\"$color[0]\" BORDER=0 WIDTH=\"100%\" CELLSPACING=0 CELLPADDING=2>".
         "<TR><TD ALIGN=left WIDTH=\"100%\">";

    displayInternalLink("plugins/calendar/calendar.php?year=$year&month=$month",_("Month View"),"right");
    echo "&nbsp;&nbsp\n";
    displayInternalLink("plugins/calendar/day.php?year=$year&month=$month&day=$day",_("Day View"),"right");
    echo "&nbsp;&nbsp\n";
    // displayInternalLink("plugins/calendar/event_create.php?year=$year&month=$month&day=$day",_("Add Event"),"right");
    // echo "&nbsp;&nbsp\n";
    echo '</TD></TR>';

}

function select_option_length($selected) {

    $eventlength = array(
        "0" => _("0 min."),
        "15" => _("15 min."),
        "30" => _("30 min."),
        "45" => _("45 min."),
        "60" => _("1 hr."),
        "90" => _("1.5 hr."),
        "120" => _("2 hr."),
        "150" => _("2.5 hr."),
        "180" => _("3 hr."),
        "210" => _("3.5 hr."),
        "240" => _("4 hr."),
        "300" => _("5 hr."),
        "360" => _("6 hr.")
    );

    while( $bar = each($eventlength)) {
        if($selected==$bar[key]){
                echo "        <OPTION VALUE=\"".$bar[key]."\" SELECTED>".$bar[value]."</OPTION>\n";
        } else {
                echo "        <OPTION VALUE=\"".$bar[key]."\">".$bar[value]."</OPTION>\n";
        }
    }
}

function select_option_minute($selected) {
    $eventminute = array(
    "00"=>"00",
    "05"=>"05",
    "10"=>"10",
    "15"=>"15",
    "20"=>"20",
    "25"=>"25",
    "30"=>"30",
    "35"=>"35",
    "40"=>"40",
    "45"=>"45",
    "50"=>"50",
    "55"=>"55"
    );

    while ( $bar = each($eventminute)) {
        if ($selected==$bar[key]){
                echo "        <OPTION VALUE=\"".$bar[key]."\" SELECTED>".$bar[value]."</OPTION>\n";
        } else {
                echo "        <OPTION VALUE=\"".$bar[key]."\">".$bar[value]."</OPTION>\n";
        }
    }
}

function select_option_hour($selected) {

    for ($i=0;$i<24;$i++){
        ($i<10)? $ih = "0" . $i : $ih = $i;
        if ($ih==$selected){
            echo "            <OPTION VALUE=\"$ih\" SELECTED>$i</OPTION>\n";
        } else {
            echo "            <OPTION VALUE=\"$ih\">$i</OPTION>\n";
        }
    }
}

function select_option_priority($selected) {
    $temp = isOdd($selected);
    $eventpriority = array(
        "0" => _("Normal"),
        "1" => _("High"),
    );

    while( $bar = each($eventpriority)) {
        if($temp==$bar[key]){
                echo "        <OPTION VALUE=\"".$bar[key]."\" SELECTED>".$bar[value]."</OPTION>\n";
        } else {
                echo "        <OPTION VALUE=\"".$bar[key]."\">".$bar[value]."</OPTION>\n";
        }
    }
}

function select_option_year($selected) {

    for ($i=1902;$i<2038;$i++){
        if ($i==$selected){
            echo "            <OPTION VALUE=\"$i\" SELECTED>$i</OPTION>\n";
        } else {
            echo "            <OPTION VALUE=\"$i\">$i</OPTION>\n";
        }
    }
}

function select_option_month($selected) {

    for ($i=1;$i<13;$i++){
        $im=date('m',mktime(0,0,0,$i,1,1));
        $is = substr( _( date('F',mktime(0,0,0,$i,1,1)) ), 0, 3 );
        if ($im==$selected){
            echo "            <OPTION VALUE=\"$im\" SELECTED>$is</OPTION>\n";
        } else {
            echo "            <OPTION VALUE=\"$im\">$is</OPTION>\n";
        }
    }
}

function select_option_day($selected) {

    for ($i=1;$i<32;$i++){
        ($i<10)? $ih="0".$i : $ih=$i;
        if ($i==$selected){
            echo "            <OPTION VALUE=\"$ih\" SELECTED>$i</OPTION>\n";
        } else {
            echo "            <OPTION VALUE=\"$ih\">$i</OPTION>\n";
        }
    }
}
function calcNotifyTime($eventTime,$Priority){
/*
 * Caclulate and return the reminder time based on event time and priority
 *  input:
 *    $eventTime  - in the format: YYYYMMDDHHMM
 *    $Priority   - integer range(0:8)
 *
 *  output:
 *    notifyTime  - in the format: YYYYMMDDHHMM
 *
 */  

  // break the event time in to pieces for mktime
  $oY = substr($eventTime,0,4);
  $om = substr($eventTime,4,2);
  $od = substr($eventTime,6,2);
  $oH = substr($eventTime,8,2);
  $oi = substr($eventTime,10,2);

  // initialize the delta variables
  $days=0;
  $mins=0;
  $notify = $Priority - isOdd($Priority);
  if ($notify==2){ $mins=0;  };
  if ($notify==4){ $mins=5;  };
  if ($notify==6){ $mins=15; };
  if ($notify==8){ $mins=30; };
  if ($notify==10){ $mins=60; };
  if ($notify==12){ $mins=240;};
  if ($notify==14){ $days=1;  };

  return date("YmdHi",mktime($oH,$oi-$mins,0,$om,$od-$days,$oY));

};

function select_option_notification($priority) {
  $selected = $priority - isOdd($priority);
  $eventNotification = array(
    "0" =>  _("Don't Email Me"),
    "2" =>  _("Email Me - 0m prior"),
    "4" =>  _("Email Me - 5m prior"),
    "6" =>  _("Email Me - 15m prior"),
    "8" =>  _("Email Me - 30m prior"),
    "10" => _("Email Me - 1h prior"),
    "12" => _("Email Me - 4h prior"),
    "14" => _("Email Me - 1d prior"),
  );

  while( $bar = each($eventNotification)) {
    if($selected==$bar[key]){
      echo "        <OPTION VALUE=\"".$bar[key]."\" SELECTED>".$bar[value]."</OPTION>\n";
    } else {
      echo "        <OPTION VALUE=\"".$bar[key]."\">".$bar[value]."</OPTION>\n";
    }
  }
};

function isOdd($i){
  if ($i%2) { 
    return 1;	//Number is Odd 
  } else { 
    return 0;	//Number is Even
  };
};

?>

<?php


/* deleteMoveNP 2.0 
     deletes or moves currently displayed message and displays
     next or previous message.
   
   By Ben Brillat <brillat-sqplugin@mail.brillat.net>
   Based on Delete Move Next 1.0 by Bryan Stalcup <bryan@stalcup.net>

   Copyright (C) 2001 Benjamin Brillat, see "README" file for details.
*/

function squirrelmail_plugin_init_deleteMoveNP() {
  global $squirrelmail_plugin_hooks;
  $squirrelmail_plugin_hooks['html_top']['deleteMoveNP'] = 'deleteMoveNP_html_t';
  $squirrelmail_plugin_hooks['read_body_bottom']['deleteMoveNP'] = 'deleteMoveNP_read_b';
  $squirrelmail_plugin_hooks['read_body_top']['deleteMoveNP'] = 'deleteMoveNP_read_t';
  $squirrelmail_plugin_hooks["options_display_inside"]["deleteMoveNP"] = "deleteMoveNP_display_inside";
  $squirrelmail_plugin_hooks["options_display_save"]["deleteMoveNP"] = "deleteMoveNP_display_save";
  $squirrelmail_plugin_hooks["loading_prefs"]["deleteMoveNP"] = "deleteMoveNP_loading_prefs";
}


function deleteMoveNP_html_t() {

  global $PHP_SELF;

  if(preg_match ("/read_body/i", "$PHP_SELF")){
    //Will only be here if we're in the READ_BODY file.
    global $delete_id, $move_id;

    if ($delete_id) {
      deleteMoveNP_delete();
    } elseif ($move_id) {
      deleteMoveNP_move();
    }
  }


}

function deleteMoveNP_read_t() {

  global $color, $where, $what, $currentArrayIndex, $passed_id;
  global $urlMailbox, $sort, $startMessage, $delete_id, $move_id;
  global $imapConnection;

  global $deleteMoveNP_t;
  if($deleteMoveNP_t == "on") {
    deleteMoveNP_read("top");
  }
}

function deleteMoveNP_read_b() {

  global $color, $where, $what, $currentArrayIndex, $passed_id;
  global $urlMailbox, $sort, $startMessage, $delete_id, $move_id;
  global $imapConnection, $delete_id, $mailbox, $auto_expunge;


  global $deleteMoveNP_b;
  if($deleteMoveNP_b != "off") {
    deleteMoveNP_read("bottom");
  }
}


function deleteMoveNP_read($currloc) {
  global $deleteMoveNP_formATtop, $deleteMoveNP_formATbottom;
  global $color, $where, $what, $currentArrayIndex, $passed_id;
  global $urlMailbox, $sort, $startMessage, $delete_id, $move_id;
  global $imapConnection;

  if (($where && $what) || ($currentArrayIndex == -1)) {
    return;
  } else {
    $next = findNextMessage();
    if ($next == -1) {
      return;
    }
  }
   $location = get_location();
   echo "<base href=\"$location/\">";
?><table cols=1 cellspacing=0 width=100% border=0 cellpadding=2>
    <tr>
      <td bgcolor="<?php echo $color[9] ?>" width=100% align=center><small>
        <?php 
          $prev = findPreviousMessage();
          if ($prev != -1)
            echo "<a href=\"read_body.php?passed_id=$prev&mailbox=$urlMailbox&sort=$sort&startMessage=$startMessage&show_more=0\">" . _("Previous") . "</A>&nbsp;|&nbsp;";
          else
            echo _("Previous") . "&nbsp;|&nbsp;";
          echo "<a href=\"read_body.php?passed_id=$next&mailbox=$urlMailbox&sort=$sort&startMessage=$startMessage&show_more=0\">" . _("Next") . "</a>&nbsp;|&nbsp;";
	if($prev != -1){
          echo "<a href=\"read_body.php?passed_id=$passed_id&mailbox=$urlMailbox&sort=$sort&startMessage=$startMessage&show_more=0&delete_id=$passed_id\">" . _("Delete & Prev") . "</a>" . "&nbsp;|&nbsp;";
	}
	else
	  echo _("Delete & Prev") . "&nbsp;|&nbsp;";
          echo "<a href=\"read_body.php?passed_id=$next&mailbox=$urlMailbox&sort=$sort&startMessage=$startMessage&show_more=0&delete_id=$passed_id\">" . _("Delete & Next") . "</a>";
        ?>
      </small></td>
    </tr>

 <?php
  if(($deleteMoveNP_formATtop == "on") && ($currloc == "top")){
    deleteMoveNP_moveForm($next);
  }
  if(($deleteMoveNP_formATbottom != "off") && ($currloc == "bottom")){
    deleteMoveNP_moveForm($next);
  }
 ?> 
 
 </table><?php
}

function deleteMoveNP_moveForm($next) {

  global $color, $where, $what, $currentArrayIndex, $passed_id;
  global $urlMailbox, $sort, $startMessage, $delete_id, $move_id;
  global $imapConnection;

?>
   <tr>
      <form action="<?php echo "read_body.php"?>" method="get">
      <td bgcolor="<?php echo $color[9] ?>" width=100% align=center><small>
        <input type="hidden" name="passed_id" value="<?php echo $next ?>">
        <input type="hidden" name="mailbox" value="<?php echo $urlMailbox ?>">
        <input type="hidden" name="sort" value="<?php echo $sort ?>">
        <input type="hidden" name="startMessage" value="<?php echo $startMessage ?>">
        <input type="hidden" name="show_more" value="0">
        <input type="hidden" name="move_id" value="<?php echo $passed_id ?>">
        Move to:
        <select name="targetMailbox"><?php
      $boxes = sqimap_mailbox_list($imapConnection);
      for ($i = 0; $i < count($boxes); $i++) {
        if ($boxes[$i]["flags"][0] != "noselect" && $boxes[$i]["flags"][1] != "noselect" && $boxes[$i]["flags"][2] != "noselect") {
          $box = $boxes[$i]["unformatted"];
          $box2 = str_replace(' ', '&nbsp;', $boxes[$i]["formatted"]);
          echo "          <option value=\"$box\">$box2\n";
        }
      }?></select>
       <input type=submit value="Move">
       </small>
      </td>
      </form>
    </tr>

<?php

}

function deleteMoveNP_delete() {
  global $imapConnection, $delete_id, $mailbox, $auto_expunge;
  sqimap_messages_delete($imapConnection, $delete_id, $delete_id, $mailbox);
  if ($auto_expunge){
    sqimap_mailbox_expunge($imapConnection, $mailbox, true);
  }
}

function deleteMoveNP_move() {
  global $imapConnection, $move_id, $targetMailbox, $auto_expunge, $mailbox;
  // Move message
  sqimap_messages_copy($imapConnection, $move_id, $move_id, $targetMailbox);
  sqimap_messages_flag($imapConnection, $move_id, $move_id, "Deleted");
  if ($auto_expunge == true)
    sqimap_mailbox_expunge($imapConnection, $mailbox, true);
}

function deleteMoveNP_display_inside()
{
 global $username,$data_dir;
 global $deleteMoveNP_t, $deleteMoveNP_formATtop;
 global $deleteMoveNP_b, $deleteMoveNP_formATbottom;

 echo "<tr><td align=right valign=top>\n";
 echo "deleteMoveNP:</td>\n";
 echo "<td><input type=checkbox name=deleteMoveNP_ti";
 if($deleteMoveNP_t == "on") {
   echo " checked";
 }
 echo "> <- display at top ";

 echo "<input type=checkbox name=deleteMoveNP_formATtopi";
 if($deleteMoveNP_formATtop == "on") {
   echo " checked";
 }
 echo "> <- with move option";

 echo "<br>";

 echo "<input type=checkbox name=deleteMoveNP_bi";
 if($deleteMoveNP_b != "off") {
   echo " checked";
 }
 echo "> <- display at bottom";

 echo "<input type=checkbox name=deleteMoveNP_formATbottomi";
 if($deleteMoveNP_formATbottom != "off") {
   echo " checked";
 }
 echo "> <- with move option";

 echo "<br>";


 
 echo "</td></tr>\n";
}

function deleteMoveNP_display_save()
{

  global $username,$data_dir;
  global $deleteMoveNP_ti, $deleteMoveNP_formATtopi;
  global $deleteMoveNP_bi, $deleteMoveNP_formATbottomi;

   if(isset($deleteMoveNP_ti)) {
     setPref($data_dir, $username, "deleteMoveNP_t", 'on');
   } else {
     setPref($data_dir, $username, "deleteMoveNP_t", "off");
   }

   if(isset($deleteMoveNP_formATtopi)) {
     setPref($data_dir, $username, "deleteMoveNP_formATtop", 'on');
   } else {
     setPref($data_dir, $username, "deleteMoveNP_formATtop", "off");
   }


   if(isset($deleteMoveNP_bi)) {
     setPref($data_dir, $username, "deleteMoveNP_b", 'on');
   } else {
     setPref($data_dir, $username, "deleteMoveNP_b", "off");
   }

   if(isset($deleteMoveNP_formATbottomi)) {
     setPref($data_dir, $username, "deleteMoveNP_formATbottom", 'on');
   } else {
     setPref($data_dir, $username, "deleteMoveNP_formATbottom", "off");
   }


}


function deleteMoveNP_loading_prefs()
{
   global $username,$data_dir;
   global $deleteMoveNP_t, $deleteMoveNP_formATtop;
   global $deleteMoveNP_b, $deleteMoveNP_formATbottom;
   $deleteMoveNP_t = getPref($data_dir, $username, "deleteMoveNP_t");
   $deleteMoveNP_b = getPref($data_dir, $username, "deleteMoveNP_b");
   $deleteMoveNP_formATtop = getPref($data_dir, $username, "deleteMoveNP_formATtop");
   $deleteMoveNP_formATbottom = getPref($data_dir, $username, "deleteMoveNP_formATbottom");

}


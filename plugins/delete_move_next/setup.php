<?php


/* delete_move_next 2.0.2
     deletes or moves currently displayed message and displays
     next or previous message.
   
   By Ben Brillat <brillat-sqplugin@mail.brillat.net>
   Based on Delete Move Next 1.0 by Bryan Stalcup <bryan@stalcup.net>

   Copyright (C) 2001 Benjamin Brillat, see "README" file for details.
*/

function squirrelmail_plugin_init_delete_move_next() {
  global $squirrelmail_plugin_hooks;
  $squirrelmail_plugin_hooks['html_top']['delete_move_next'] = 'delete_move_next_html_t';
  $squirrelmail_plugin_hooks['read_body_bottom']['delete_move_next'] = 'delete_move_next_read_b';
  $squirrelmail_plugin_hooks['read_body_top']['delete_move_next'] = 'delete_move_next_read_t';
  $squirrelmail_plugin_hooks["options_display_inside"]["delete_move_next"] = "delete_move_next_display_inside";
  $squirrelmail_plugin_hooks["options_display_save"]["delete_move_next"] = "delete_move_next_display_save";
  $squirrelmail_plugin_hooks["loading_prefs"]["delete_move_next"] = "delete_move_next_loading_prefs";
}


function delete_move_next_html_t() {

  global $PHP_SELF;

  if(preg_match ("/read_body/i", "$PHP_SELF")){
    //Will only be here if we're in the READ_BODY file.
    global $delete_id, $move_id;

    if ($delete_id) {
      delete_move_next_delete();
    } elseif ($move_id) {
      delete_move_next_move();
    }
  }


}

function delete_move_next_read_t() {

  global $color, $where, $what, $currentArrayIndex, $passed_id;
  global $urlMailbox, $sort, $startMessage, $delete_id, $move_id;
  global $imapConnection;

  global $delete_move_next_t;
  if($delete_move_next_t == "on") {
    delete_move_next_read("top");
  }
}

function delete_move_next_read_b() {

  global $color, $where, $what, $currentArrayIndex, $passed_id;
  global $urlMailbox, $sort, $startMessage, $delete_id, $move_id;
  global $imapConnection, $delete_id, $mailbox, $auto_expunge;


  global $delete_move_next_b;
  if($delete_move_next_b != "off") {
    delete_move_next_read("bottom");
  }
}


function delete_move_next_read($currloc) {
  global $delete_move_next_formATtop, $delete_move_next_formATbottom;
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
  if(($delete_move_next_formATtop == "on") && ($currloc == "top")){
    delete_move_next_moveForm($next);
  }
  if(($delete_move_next_formATbottom != "off") && ($currloc == "bottom")){
    delete_move_next_moveForm($next);
  }
 ?> 
 
 </table><?php
}

function delete_move_next_moveForm($next) {

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
          $box2 = replace_spaces($boxes[$i]["formatted"]);
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

function delete_move_next_delete() {
  global $imapConnection, $delete_id, $mailbox, $auto_expunge;
  sqimap_messages_delete($imapConnection, $delete_id, $delete_id, $mailbox);
  if ($auto_expunge){
    sqimap_mailbox_expunge($imapConnection, $mailbox, true);
  }
}

function delete_move_next_move() {
  global $imapConnection, $move_id, $targetMailbox, $auto_expunge, $mailbox;
  // Move message
  sqimap_messages_copy($imapConnection, $move_id, $move_id, $targetMailbox);
  sqimap_messages_flag($imapConnection, $move_id, $move_id, "Deleted");
  if ($auto_expunge == true)
    sqimap_mailbox_expunge($imapConnection, $mailbox, true);
}

function delete_move_next_display_inside()
{
 global $username,$data_dir;
 global $delete_move_next_t, $delete_move_next_formATtop;
 global $delete_move_next_b, $delete_move_next_formATbottom;

 echo "<tr><td align=right valign=top>\n";
 echo "delete_move_next:</td>\n";
 echo "<td><input type=checkbox name=delete_move_next_ti";
 if($delete_move_next_t == "on") {
   echo " checked";
 }
 echo "> <- display at top ";

 echo "<input type=checkbox name=delete_move_next_formATtopi";
 if($delete_move_next_formATtop == "on") {
   echo " checked";
 }
 echo "> <- with move option";

 echo "<br>";

 echo "<input type=checkbox name=delete_move_next_bi";
 if($delete_move_next_b != "off") {
   echo " checked";
 }
 echo "> <- display at bottom";

 echo "<input type=checkbox name=delete_move_next_formATbottomi";
 if($delete_move_next_formATbottom != "off") {
   echo " checked";
 }
 echo "> <- with move option";

 echo "<br>";


 
 echo "</td></tr>\n";
}

function delete_move_next_display_save()
{

  global $username,$data_dir;
  global $delete_move_next_ti, $delete_move_next_formATtopi;
  global $delete_move_next_bi, $delete_move_next_formATbottomi;

   if(isset($delete_move_next_ti)) {
     setPref($data_dir, $username, "delete_move_next_t", 'on');
   } else {
     setPref($data_dir, $username, "delete_move_next_t", "off");
   }

   if(isset($delete_move_next_formATtopi)) {
     setPref($data_dir, $username, "delete_move_next_formATtop", 'on');
   } else {
     setPref($data_dir, $username, "delete_move_next_formATtop", "off");
   }


   if(isset($delete_move_next_bi)) {
     setPref($data_dir, $username, "delete_move_next_b", 'on');
   } else {
     setPref($data_dir, $username, "delete_move_next_b", "off");
   }

   if(isset($delete_move_next_formATbottomi)) {
     setPref($data_dir, $username, "delete_move_next_formATbottom", 'on');
   } else {
     setPref($data_dir, $username, "delete_move_next_formATbottom", "off");
   }


}


function delete_move_next_loading_prefs()
{
   global $username,$data_dir;
   global $delete_move_next_t, $delete_move_next_formATtop;
   global $delete_move_next_b, $delete_move_next_formATbottom;
   $delete_move_next_t = getPref($data_dir, $username, "delete_move_next_t");
   $delete_move_next_b = getPref($data_dir, $username, "delete_move_next_b");
   $delete_move_next_formATtop = getPref($data_dir, $username, "delete_move_next_formATtop");
   $delete_move_next_formATbottom = getPref($data_dir, $username, "delete_move_next_formATbottom");

}

?>

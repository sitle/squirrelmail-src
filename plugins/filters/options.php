<?php
   /*
    *  Message and Spam Filter Plugin 
    *  By Luke Ehresman <luke@squirrelmail.org>
    *     Tyler Akins <tyler@boas.anthro.mnsu.edu>  
    *  (c) 2000 (GNU GPL - see ../../COPYING)
    *
    *  This plugin filters your inbox into different folders based upon given
    *  criteria.  It is most useful for people who are subscibed to mailing lists
    *  to help organize their messages.  The argument stands that filtering is
    *  not the place of the client, which is why this has been made a plugin for
    *  SquirrelMail.  You may be better off using products such as Sieve or
    *  Procmail to do your filtering so it happens even when SquirrelMail isn't
    *  running.
    *
    *  If you need help with this, or see improvements that can be made, please
    *  email me directly at the address above.  I definately welcome suggestions
    *  and comments.  This plugin, as is the case with all SquirrelMail plugins,
    *  is not directly supported by the developers.  Please come to me off the
    *  mailing list if you have trouble with it.
    *
    *  Also view plugins/README.plugins for more information.
    *
    */
   chdir ("..");
   session_start();

   if (!isset($config_php))
      include ("../config/config.php");
   if (!isset($strings_php))
      include ("../functions/strings.php");
   if (!isset($page_header))
      include ("../functions/page_header.php");
   if (!isset($imap_php))
      include ("../functions/imap.php");
   
   include ("../src/load_prefs.php");

   displayPageHeader($color, "None");   

   if ($filter_submit) {
      if (!isset($theid)) $theid = 0;
      $filter_what = ereg_replace(",", " ", $filter_what);
      $filter_what = str_replace("\\\\", "\\", $filter_what);
      $filter_what = str_replace("\\\"", "\"", $filter_what);
      $filter_what = str_replace("\"", "&quot;", $filter_what);

      setPref($data_dir, $username, "filter".$theid, $filter_where.",".$filter_what.",".$filter_folder);
      $filters[$theid]["where"] = $filter_where;
      $filters[$theid]["what"] = $filter_what;
      $filters[$theid]["folder"] = $filter_folder;
   } else if ($spam_submit) {
      $spam_filters = load_spam_filters();
      setPref($data_dir, $username, 'filters_spam_folder', $filters_spam_folder_set);
      setPref($data_dir, $username, 'filters_spam_scan', $filters_spam_scan_set);
      foreach ($spam_filters as $Key => $Value)
      {
          $input = $spam_filters[$Key]['prefname'] . '_set';
          setPref($data_dir, $username, $spam_filters[$Key]['prefname'],
              $$input);
      }
   } else if ($action == "delete") {
      remove_filter($theid);
   }
   
   $filters_spam_folder = getPref($data_dir, $username, 'filters_spam_folder');
   $filters_spam_scan = getPref($data_dir, $username, 'filters_spam_scan');
   $filters = load_filters();

   ?>
      <br>
      <table width=95% align=center border=0 cellpadding=2 cellspacing=0><tr><td bgcolor="<?php echo $color[0] ?>">
         <center><b><?php echo _("Options") ?> - Message Filtering</b></center>
      </td></tr></table>
      <br><center>[<a href="options.php?action=add">New</a>] - [<a href="../../src/options.php">Done</a>]</center><br>
      <table border=0 cellpadding=3 cellspacing=0 align=center>
         <?php
            for ($i=0; $i < count($filters); $i++) {
               if ($i % 2 == 0) $clr = $color[0];
               else $clr = $color[9];

               $fdr = ($folder_prefix)?str_replace($folder_prefix, "", $filters[$i]["folder"]):$filters[$i]["folder"];

               echo "<tr><td bgcolor=$clr>\n";
               echo "<small>[<a href=\"options.php?theid=$i&action=edit\">Edit</a>] [<a href=\"options.php?theid=$i&action=delete\">Delete</a>] - </small>If <b>" .$filters[$i]["where"]. "</b> contains <b>" .$filters[$i]["what"]. "</b> then move to <b>" .$fdr. "</b>.";
               echo "</td></tr>";
            }
         ?>
      </table>
      
      <table width=80% align=center border=0 cellpadding=2 cellspacing=0">
        <tr><td>&nbsp</td></tr>
      </table>
      
      <table width=95% align=center border=0 cellpadding=2 cellspacing=0 bgcolor="<?php echo $color[0] ?>">
        <tr><th align=center>Spam Filtering</th></tr>
      </table>
      <?PHP if ($action != 'spam') { ?>
      <p align=center>[<a href="options.php?action=spam">Edit</a>]<br>
      Spam is sent to <b><?PHP 
         if ($filters_spam_folder) 
         {
            echo $filters_spam_folder;
         }
         else
         {
            echo '[<i>not set yet</i>]';
         }
      ?></b><br>Spam scan is limited to <b><?PHP
         if ($filters_spam_scan == 'new')
         {
            echo 'New Messages Only';
         }
         else
         {
            echo 'All Messages';
         }
      ?></b></p>
      
      <table border=0 cellpadding=3 cellspacing=0 align=center bgcolor="<?PHP echo $color[0] ?>">
        <?PHP
        
          $spam_filters = load_spam_filters();
          
          foreach ($spam_filters as $Key => $Value)
          {
              echo '<tr><th align=center>';
              
              if ($spam_filters[$Key]['enabled'])
              {
                  echo 'ON';
              }
              else
              {
                  echo 'OFF';
              }
              
              echo '</th><td>&nbsp;-&nbsp;</td><td>';
              
              if ($spam_filters[$Key]['link'])
              {
                  echo '<a href="';
                  echo $spam_filters[$Key]['link'];
                  echo '" target="_blank">';
              }
              
              echo $spam_filters[$Key]['name'];
              if ($spam_filters[$Key]['link'])
              {
                  echo '</a>';
              }
              echo "</td></tr>\n";
          }
          
        ?>
      </table>
   <?php
      }
      
      if ($action == "add" || $action == "edit") {
         $imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);
         $boxes = sqimap_mailbox_list($imapConnection);
         sqimap_logout($imapConnection);
         if (!isset($theid))
            $theid = count($filters);

         ?>
            <center>
            <form action="options.php" method=post>
            <br><table cellpadding=2 cellspacing=0 border=0>
               <tr>
                  <td>
                    &nbsp;
                  </td>
                  <td>
                     <select name=filter_where>
                        <?php
                           if ($filters[$theid]["where"] == "From") echo "<option value=\"From\" selected> From\n";
                           else                                     echo "<option value=\"From\"> From\n";

                           if ($filters[$theid]["where"] == "To")   echo "<option value=\"To\" selected> To\n";
                           else                                     echo "<option value=\"To\"> To\n";

                           if ($filters[$theid]["where"] == "Cc")   echo "<option value=\"Cc\" selected> Cc\n";
                           else                                     echo "<option value=\"Cc\"> Cc\n";

                           if ($filters[$theid]["where"] == "To or Cc")   echo "<option value=\"To or Cc\" selected> To or Cc\n";
                           else                                     echo "<option value=\"To or Cc\"> To or Cc\n";

                           if ($filters[$theid]["where"] == "Subject")   echo "<option value=\"Subject\" selected> Subject\n";
                           else                                     echo "<option value=\"Subject\"> Subject\n";
                        ?>
                     </select>
                  </td>
               </tr>
               <tr>
                  <td align=right>
                     Contains:
                  </td>
                  <td>
                     <input type=text size=32 name=filter_what value="<?php echo $filters[$theid]["what"]; ?>">
                  </td>
               </tr>
               <tr>
                  <td>
                     Move to:
                  </td>
                  <td>
                     <tt>
                     <select name=filter_folder>
      <?php
      for ($i = 0; $i < count($boxes); $i++) {
         if ($boxes[$i]["flags"][0] != "noselect" && $boxes[$i]["flags"][1] != "noselect" && $boxes[$i]["flags"][2] != "noselect") {
            $box = $boxes[$i]["unformatted"];
            $box2 = str_replace(' ', '&nbsp;', $boxes[$i]["formatted"]);
            if ($filters[$theid]["folder"] == $box)
               echo "         <OPTION VALUE=\"$box\" SELECTED>$box2\n";
            else
               echo "         <OPTION VALUE=\"$box\">$box2\n";
         }       
      }
      ?>
                     </tt>
                     </select>
                  </td>
               </tr>
            </table>   
            <input type=submit name=filter_submit value=Submit>
            <input type=hidden name=theid value=<?php echo $theid ?>>
            </form>
            </center>
         <?php
      }
      else if ($action == 'spam')
      {
         $imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);
         $boxes = sqimap_mailbox_list($imapConnection);
         sqimap_logout($imapConnection);
         for ($i = 0; $i < count($boxes) && $filters_spam_folder == ''; $i++) {
            if ($boxes[$i]["flags"][0] != "noselect" && $boxes[$i]["flags"][1] != "noselect" && $boxes[$i]["flags"][2] != "noselect") {
               $filters_spam_folder = $boxes[$i]['unformatted'];
            }       
         }
         
         ?><form method=post action="options.php">
         <center>
         <table width=85% cellpadding=2 cellspacing=0 border=0>
           <tr>
             <th align=right nowrap>Move spam to:</th>
             <td><select name="filters_spam_folder_set">
         <?PHP
            for ($i = 0; $i < count($boxes); $i++) {
               if ($boxes[$i]["flags"][0] != "noselect" && $boxes[$i]["flags"][1] != "noselect" && 
                   $boxes[$i]["flags"][2] != "noselect" && $boxes[$i]['unformatted'] != 'INBOX') {
                  $box = $boxes[$i]["unformatted"];
                  $box2 = str_replace(' ', '&nbsp;', $boxes[$i]["formatted"]);
                  if ($filters_spam_folder == $box)
                     echo "<OPTION VALUE=\"$box\" SELECTED>$box2</OPTION>\n";
                  else
                     echo "<OPTION VALUE=\"$box\">$box2</OPTION>\n";
               }       
            }
         ?>
               </select>
             </td>
           </tr>
           <tr><td></td><td>Moving spam directly to the trash may not be a good idea at first,
             since messages from friends and mailing lists might accidentally be marked as spam.
             Whatever folder you set this to, make sure that it gets cleaned out periodically,
             so that you don't have an excessively large mailbox hanging around.
             </td></tr>
           <tr>
             <th align=right nowrap>What to Scan:</th>
             <td><select name="filters_spam_scan_set">
               <option value=''<?PHP
                   if ($filters_spam_scan == '') echo ' SELECTED';
               ?>>All messages</option>
               <option value='new'<?PHP
                   if ($filters_spam_scan == 'new') echo ' SELECTED';
               ?>>Only unread messages</option>
             </select>
             </td>
           </tr>
           <tr>
             <td></td><td>The more messages you scan, the longer it takes.  I would suggest
             that you scan only new messages.  If you make a change to your filters, I
             would set it to scan all messages, then go view my INBOX, then come back and
             set it to scan only new messages.  That way, your new spam filters will be
             applied and you'll scan even the spam you read with the new filters.</td>
           </tr>
         <?PHP
           $spam_filters = load_spam_filters();
           
           foreach ($spam_filters as $Key => $Value)
           {
               echo "<tr><th align=right nowrap>$Key</th>\n";
               echo '<td><input type=checkbox name="';
               echo $spam_filters[$Key]['prefname'];
               echo '_set"';
               if ($spam_filters[$Key]['enabled'])
                   echo ' CHECKED';
               echo '> - ';
               if ($spam_filters[$Key]['link'])
               {
                   echo '<a href="';
                   echo $spam_filters[$Key]['link'];
                   echo '" target="_blank">';
               }
               echo $spam_filters[$Key]['name'];
               if ($spam_filters[$Key]['link'])
               {
                   echo '</a>';
               }
               echo '</td></tr><tr><td></td><td>';
               echo $spam_filters[$Key]['comment'];
               echo "</td></tr>\n";
           }
         ?>
           <tr><td colspan=2 align=center><input type=submit name="spam_submit" value="Save"></td></tr>
           </table>
           </center>
           </form>
         <?PHP
         
         sqimap_logout($imapConnection);
      }
?>

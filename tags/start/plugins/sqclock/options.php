<?php
   /*
    *  SquirrelMail Clock
    *  By Luke Ehresman <luke@squirrelmail.org>
    *  (c) 2000 (GNU GPL - see ../../COPYING)
    *
    *  This plugin puts a clock at the top of the folder listing.  This is
    *  most useful, especially if your webmail server is in a different time
    *  zone than you.  This lets you see what time it is for your server, and
    *  what all your dates are relative to.
    *
    *  If you need help with this, or see improvements that can be made, please
    *  email me directly at the address above.  I definately welcome suggestions
    *  and comments.  This plugin, as is the case with all SquirrelMail plugins,
    *  is not directly supported by the developers.  Please come to me off the
    *  mailing list if you have trouble with it.
    *
    */

   function show_options() {
      global $hour_format, $date_format;
      ?>
         <tr>
            <td align=right>Hour format:</td>
            <td>
               <select name=hourformat>
               <?php
                  if ($hour_format == 1 || !$hour_format)
                     $selected = " selected";
                  echo "<option value=1$selected>24-hour clock";
                  $selected = "";

                  if ($hour_format == 2)
                     $selected = " selected";
                  echo "<option value=2$selected>12-hour clock";
                  $selected = "";
               ?>
               </select>
            </td>
         </tr>
         <tr>
            <td align=right>Date format:</td>
            <td>
               <select name=dateformat>
               <?php
                  if ($date_format == 1)
                     $selected = " selected";
                  echo "<option value=1$selected>MM/DD/YY HH:MM";
                  $selected = "";

                  if ($date_format == 2)
                     $selected = " selected";
                  echo "<option value=2$selected>DD/MM/YY HH:MM";
                  $selected = "";

                  if ($date_format == 3 || !$date_format)
                     $selected = " selected";
                  echo "<option value=3$selected>DDD, HH:MM";
                  $selected = "";

                  if ($date_format == 4)
                     $selected = " selected";
                  echo "<option value=4$selected>HH:MM:SS";
                  $selected = "";

                  if ($date_format == 5)
                     $selected = " selected";
                  echo "<option value=5$selected>HH:MM";
                  $selected = "";

                  if ($date_format == 6)
                     $selected = " selected";
                  echo "<option value=6$selected>No Clock";
                  $selected = "";
               ?>   
               </select>
            </td>
         </tr>
      <?php
   }

   function save_options() {
      global $data_dir, $username, $dateformat, $hourformat;

      setPref($data_dir, $username, "date_format", $dateformat);
      setPref($data_dir, $username, "hour_format", $hourformat);
   }

   function load_options() {
      global $date_format, $hour_format, $username, $data_dir;

      $date_format = getPref($data_dir, $username, "date_format");
      $hour_format = getPref($data_dir, $username, "hour_format");
      if (!$date_format)
         $date_format = 3;
      if (!$hour_format)
         $hour_format = 2;

   }
?>

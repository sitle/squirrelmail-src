<?php
   /**
    **  validate.php
    **
    **  Copyright (c) 1999-2000 The SquirrelMail development team
    **  Licensed under the GNU GPL. For full terms see the file COPYING.
    **
    **  $Id$
    **/

   function data_validate (&$item, $key) {
      if (strstr ($key, "_php")) {
         echo "<br><br><center><b>Possible security breach!!</b><br>";
         echo "If you received this message on accident, please notify your administrator.";
         echo "</center>";
         exit;
      }
   }
   array_walk ($HTTP_GET_VARS, "data_validate");
   array_walk ($HTTP_POST_VARS, "data_validate");
?>

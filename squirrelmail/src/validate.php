<?php
   /**
    **  validate.php
    **
    **  Copyright (c) 1999-2000 The SquirrelMail development team
    **  Licensed under the GNU GPL. For full terms see the file COPYING.
    **
    **  $Id$
    **/

   if (defined ('validate_php')) { 
      return; 
   } else { 
      define ('validate_php', true); 
   }

   session_start();
   include ("../functions/auth.php");
   is_logged_in();
?>

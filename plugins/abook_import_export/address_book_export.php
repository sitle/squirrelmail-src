<?php
  /**
   ** address_book_export.php
   **
   **  Copyright (c) 1999-2000 The SquirrelMail development team
   **  Licensed under the GNU GPL. For full terms see the file COPYING.
   **
   ** This could be integrated into the import file but this was
   ** a lot simpler and that file was already complicated enough.
   ** This file must accompany the plugin to be used unless you
   ** make your own form.
   ** 
   ** No attempts are made to alter the field seperator's used in
   ** the native abook format which uses the "|" symbol. If you don't
   ** like this and would like to see "," used instead, send me a patch.
   ** Most programs recognize the pipe anyway.
   **
   ** Even after I put in the above people still whined about it so I
   ** changed it to output comas => , instead of pipes => | .
   **/ 
  
   chdir("..");
   if (!isset($config_php))
      include("../config/config.php");

   header("Content-disposition:Filename=$data_dir$username.abook");
   header("Content-type: application/octetstream");
   header("Pragma: no-cache");
   header("Expires:0");
   $filename="$data_dir$username.abook";
   $fp=fopen("$filename", "r");
   $content= fread($fp,filesize($filename));
   $content = str_replace("|",",",$content);
   echo $content;
   fclose($fp);
?>

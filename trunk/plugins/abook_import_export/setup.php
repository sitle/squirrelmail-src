<?php
  /**
   ** setup.php
   **
   **  Copyright (c) 1999-2000 The SquirrelMail development team
   **  Licensed under the GNU GPL. For full terms see the file COPYING.
   **
   ** Uses standard plugin format to create a couple of forms to
   ** enable import/export of CSV files to/from the address book.
   **/

   function squirrelmail_plugin_init_abook_import_export() {
      global $squirrelmail_plugin_hooks;
      $squirrelmail_plugin_hooks["addressbook_bottom"]["abook_import_export"] = "abook_import_export";
   }

   function abook_import_export() {
      global $color;
?>
<CENTER>
<TABLE BGCOLOR=<?php print $color[0] ?> WIDTH="90%" BORDER="0" CELLPADDING="1" CELLSPACING="0" ALIGN="center">
   <TR>
      <!-- ----------------- begin csv import form --------------------- -->
      <FORM ENCTYPE="multipart/form-data" ACTION="../plugins/abook_import_export/address_book_import.php" METHOD=POST>
      <INPUT TYPE="hidden" NAME="max_file_size" value="5000">
      <TD VALIGN="middle" ALIGN="right">Import CSV File:</TD>
      <TD><INPUT NAME="smusercsv" TYPE="file"></TD>
      <TD VALIGN="middle" ALIGN="left"><INPUT TYPE="submit" VALUE="Import CSV File"></TD>
      </FORM>
      <!-- ----------------- end csv import form  ---------------------- -->
      <TD>&nbsp</TD>
      <!-- ----------------- begin csv export form --------------------- -->
      <FORM ENCTYPE="multipart/form-data" ACTION="../plugins/abook_import_export/address_book_export.php" METHOD=POST>
      <TD VALIGN="middle" ALIGN="right"><INPUT TYPE="submit" VALUE="Export CSV File" READONLY></TD>
      </FORM>
      <!-- ----------------- end csv import form  ---------------------- -->
  </TR>
</TABLE>
</CENTER>
<?php
   }
?>

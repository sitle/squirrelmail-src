<?php
  /**
   ** utils.php
   **
   **  Copyright (c) 2002 The SquirrelMail development team
   **  Licensed under the GNU GPL. For full terms see the file COPYING.
   **
   ** See the README file for details.
   **  $Id$
   **/
define('SM_PATH','../../');

require_once(SM_PATH . 'include/validate.php');
require_once(SM_PATH . 'functions/page_header.php');
require_once(SM_PATH . 'plugins/mail_utilities/functions.php');

displayPageHeader($color, 'None');

if ( mail_utilities_has_menu_plugins() ) {

    mail_utilities_display_menubar($color);

    if ( mail_utilities_has_shortcuts() ) {
        global $mail_utilities_hook;

        foreach ($mail_utilities_hook['util_body'] as $function) {
            echo '<P><TABLE WIDTH=95% COLS=1 ALIGN=CENTER>' . "\n";
            if (function_exists($function)) {
                echo '<TR>' .
                     "<TD BGCOLOR=\"$color[0]\" ALIGN=CENTER><b>" .
                     _($mail_utilities_hook['util_body_title'][$function]) .
                     '</b></TD></TR>'."\n".'<TR><TD>';
                $function($color);
                echo '</TD></TR>'."\n";
            }
            echo '</TABLE></P>' . "\n";
        }
    } else {
        echo '<P>There are no Utilities defined which supply shortcuts.' .
             ' Please select a utility from the above list.';
    }

} else {
    echo '<P><CENTER>No Plugins exploiting the Utilities menu have been installed.</CENTER></P>';
}
?>
</BODY>
</HTML>

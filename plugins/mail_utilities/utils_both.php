<?php
  /**
   ** utils.php
   **
   **  Copyright (c) 2002 The SquirrelMail development team
   **  Licensed under the GNU GPL. For full terms see the file COPYING.
   **
   **  See the README file for details.
   **  $Id$
   **/

chdir('..');

require_once('../src/validate.php');
require_once('../functions/page_header.php');
require_once('../plugins/mail_utilities/functions.php');
    
displayPageHeader($color, 'None');

global $plugins;
if ( in_array('mail_utilities', $plugins) ) {
    // This page is slightly different because it shares functions with
    // the mail_utilities plugin (it is part of it, after all).
    // Here is where you would include the mail_utilities function
    // if the plugin was installed:
    //    include_once('../plugins/mail_utilities/functions.php');

    mail_utilities_display_menubar($color);
}

?>

<TABLE WIDTH=95% COLS=1 ALIGN=CENTER>
<TR>
  <?php
  echo "  <TD BGCOLOR=\"$color[0]\" ALIGN=CENTER><b>" . _("Sample plugin with Both Link and Shortcut") . '</b></TD>';
  ?>
</TR>
<TR>
  <TD>
  <P>This page has both a link in the Utilities menu, and
  a short cut that can be used to jump in. The shortcut is located on the Utilities
  page, as a jumping in place, and here, to prevent the user from having to 
  go back to select another option. The function here is trivial, but consider
  what could be done for mail_fetch for example 
  (choose the ID on the Utilities page, and then jump right to the results).

  </p>
  <br />
  </TD>
</TR>
<TR>
  <?php
  echo "  <TD BGCOLOR=\"$color[0]\" ALIGN=CENTER><b>" . _("Shortcut") . '</b></TD>';
  ?> 
</TR>
<TR>
  <TD ALIGN=CENTER>
  <?php
  mail_utilities_both_shortcut($color);
  if ( isset($chosen_one) ) {
    echo "<P>You selected: $chosen_one</p>";
  }
  ?>
  <br />
  </TD>
</TR>
<TR>
  <?php
  echo "  <TD BGCOLOR=\"$color[0]\" ALIGN=CENTER><b>" . _("Registering a Shortcut") . '</b></TD>';
  ?>
</TR>
<TR>
  <TD>
<p>As documented in the README, using a shortcut consists of the following
steps (a few more than just adding a link):
<UL>
<LI>In the plugin initialization function, check for the presence
of the mail_utilities plugin, and then add functions to draw the 
menu link (can be the same used to add an item to the menuline) and the shortcut:
<PRE>
  ...
  global $plugins;
  if ( in_array('mail_utilities', $plugins ) ) {
    include_once('../plugins/mail_utilities/functions.php');

    mail_utilities_add_menu_hook('plugin_name','plugin_name_link_function');

    mail_utilities_add_shortcut('plugin name',
                                'Shortcut Title',
                                'plugin_name_shortcut_function');
  } else {
    global $squirrelmail_plugin_hooks;
    $squirrelmail_plugin_hooks['menuline']['plugin_name'] = 'plugin_name_link';
  }
  ...
</PRE>

<LI>Use the same function to draw the menu item in either location (menu line or Utilities menu):
<PRE>
  function plugin_name_link_function() {
    displayInternalLink('plugins/plugin_name/target.php', _("Link name"), '');
    echo '&amp;nbsp;&amp;nbsp;';
  }
</PRE>

<LI>Provide a function to draw the shortcut. The heading on the Utilities
page will be drawn for you (hence the Shortcut title as a separate parameter). 
The shortcut is drawn within a table element. See the contents of the 
mail_utilities_both_shortcut function in setup.php for an example
of a shortcut function. The shortcut should be only a few lines
 at most, preferably a very short and simple form.
</UL>
  </TD>
</TR>
</TABLE>

</BODY>
</HTML>

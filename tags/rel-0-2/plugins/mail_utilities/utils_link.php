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
  echo "  <TD BGCOLOR=\"$color[0]\" ALIGN=CENTER><b>" . _("Sample plugin with Link only") . '</b></TD>';
  ?>
</TR>
<TR>
  <TD>
  <p>This is a page has only a link in the Utilities menu. <br />
  Plugins that have a substantial first page (without the ability
  to "jump in") should use this.
  </p>
  <br />
  </TD>
</TR>
<TR>
<?php
echo "  <TD BGCOLOR=\"$color[0]\" ALIGN=CENTER><b>" . _("Registering a Link") . '</b></TD>';
?>
</TR>
<TR>
  <TD>
<p>As documented in the README, using a shortcut consists of the following
steps:
<UL>
<LI>In the plugin initialization function, check for the presence
of the mail_utilities plugin, and then add function to draw the 
menu link (this can be the same function that would otherwise
add an element to the menuline):
<PRE>
  ...
  global $plugins;
  if ( in_array('mail_utilities', $plugins ) ) {

    include_once('../plugins/mail_utilities/functions.php');
    mail_utilities_add_menu_hook('plugin_name','plugin_name_link_function');

  } else {

    global $squirrelmail_plugin_hooks;
    $squirrelmail_plugin_hooks['menuline']['plugin_name'] = 'plugin_name_link';

  }
  ...
</PRE>

<LI>Define the function to draw the menu item:
<PRE>
  function plugin_name_link_function() {
    displayInternalLink('plugins/plugin_name/target.php', _("Link name"), '');
    echo '&amp;nbsp;&amp;nbsp;';
  }
</PRE>
</UL>
  </TD>
</TR>
</TABLE>
</BODY>
</HTML>

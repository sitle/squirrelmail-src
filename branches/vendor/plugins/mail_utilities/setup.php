<?php
  /**
   ** setup.php
   **
   **  Copyright (c) 2002 The SquirrelMail development team
   **  Licensed under the GNU GPL. For full terms see the file COPYING.
   **
   ** Makes one page for sticking things like: Fetch, Bug, and Mail import
   ** so we don't have too many links in the menu.
   **
   ** See the README file for details.
   ** $Id$
   **/


function squirrelmail_plugin_init_mail_utilities() {
    global $squirrelmail_plugin_hooks;
    include_once("../plugins/mail_utilities/config.php");
    include_once("../plugins/mail_utilities/functions.php");

    $squirrelmail_plugin_hooks['menuline']['Utilities'] = 'mail_utilities_link';

    if ($show_link_sample) {
        mail_utilities_add_menu_hook('link_only','mail_utilities_link_only_link');
    }
    if ($show_both_sample) {
        mail_utilities_add_menu_hook('both','mail_utilities_both_link');
        mail_utilities_add_shortcut('both',
                                    'Sample of Utility Shortcut',
                                    'mail_utilities_both_shortcut');
    }
}

function mail_utilities_link() {
    displayInternalLink('plugins/mail_utilities/utils.php', _("Utilities"), '');
    echo "&nbsp;&nbsp;";
}

function mail_utilities_link_only_link() {
    displayInternalLink('plugins/mail_utilities/utils_link.php', _("Link Only"), '');
    echo "&nbsp;&nbsp;";
}

function mail_utilities_both_link() {
    displayInternalLink('plugins/mail_utilities/utils_both.php', _("Both"), '');
    echo "&nbsp;&nbsp;";
}

function mail_utilities_both_shortcut() {
  echo '<FORM ACTION="../mail_utilities/utils_both.php" METHOD=POST TARGET=_self>' .
       '<TABLE WIDTH="70%" COLS=2 ALIGN=CENTER>' .
       '<TR><TD ALIGN=CENTER>' . _("Select One:") . '&nbsp;&nbsp;' .
           '<SELECT NAME=chosen_one SIZE=1>' .
           '<OPTION VALUE="all" SELECTED>' . _("All") .
           '<OPTION VALUE="none">' . _("None") .
           '<OPTION VALUE="half-empty">' . _("Half-Empty") .
           '<OPTION VALUE="half-full">' . _("Half-Full") .
           '</SELECT> &nbsp;&nbsp;' .
           '<INPUT TYPE=SUBMIT NAME=submit_utils_both value="' . _("Submit") . '" /></TD>' .
       '</TR></TABLE></FORM>';
}

?>

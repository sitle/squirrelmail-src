<?php
  /**
   ** functions.php
   **
   **  Copyright (c) 2002 Erin Schnabel
   **  Licensed under the GNU GPL. For full terms see the file COPYING.
   **
   **  Makes one page for sticking things like: Fetch, Bug, and Mail import
   **  so we don't have too many links in the menu.
   **
   **  See the README file for details.
   **  $Id$
   **/

function mail_utilities_setup_hook() {
    global $mail_utilities_hook;
    if ( !isset($mail_utilities_hook) ) {
      $mail_utilities_hook = array();
      $mail_utilities_hook['util_menu'] = array();
      $mail_utilities_hook['util_body'] = array();
    }
}

function mail_utilities_add_menu_hook($plugin_name, $plugin_link_function) {
    global $mail_utilities_hook;
    mail_utilities_setup_hook();

    $mail_utilities_hook['util_menu'][$plugin_name] = $plugin_link_function;
}

function mail_utilities_add_shortcut($plugin_name,
                                     $plugin_body_title,
                                     $plugin_body_function) {
    global $mail_utilities_hook;
    mail_utilities_setup_hook();

    $mail_utilities_hook['util_body'][$plugin_name] = $plugin_body_function;
    if ( count($mail_utilities_hook['util_body']) > 1 ) {
        ksort($mail_utilities_hook['util_body']);
    }
    $mail_utilities_hook['util_body_title'][$plugin_body_function] = $plugin_body_title;
}

function mail_utilities_has_menu_plugins() {
  global $mail_utilities_hook;
  if ( !isset($mail_utilities_hook) ||
       count($mail_utilities_hook['util_menu']) <= 0 ) {
    return 0;
  }
  return 1;
}

function mail_utilities_has_shortcuts() {
  global $mail_utilities_hook;
  if ( !isset($mail_utilities_hook) ||
       count($mail_utilities_hook['util_body']) <= 0 ) {
    return 0;
  }
  return 1;
}

function mail_utilities_display_menubar($color) {
    global $mail_utilities_hook;
    if ( !mail_utilities_has_menu_plugins() ) {
        return;
    }

    if ( count($mail_utilities_hook['util_menu']) > 1 ) {
        ksort($mail_utilities_hook['util_menu']);
    }

    echo '<p><TABLE BORDER=0 WIDTH="95%" CELLSPACING=0 CELLPADDING=2 ALIGN=CENTER><TR><TD>' . "\n";
    displayInternalLink('plugins/mail_utilities/utils.php', _("<b>Utilities:</b>"));
    echo '&nbsp;&nbsp; ';
    foreach ($mail_utilities_hook['util_menu'] as $function) {
        if (function_exists($function)) {
            $function($color);
        }
    }
    echo "\n</TD></TR></TABLE></p>\n";
}

?>

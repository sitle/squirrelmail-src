<?
//  Organization's logo picture (blank if none)
    $org_logo = "../images/sm_logo.jpg";

//  Organization's name
    $org_name = "SquirrelMail";

//  Webmail Title
//  This is the title that goes at the top of the browser window
    $org_title = "SquirrelMail $version";

//  The server that your imap server is on
    $imapServerAddress = "localhost";
    $imapPort = 143;

//  The domain where your email address is.
//   Example:  in "luke@usa.om.org", usa.om.org is the domain.
//             this is for all the messages sent out.  Reply address
//             is generated by $username@$domain
    $domain = "localhost";

//  Your SMTP server and port number (usually the same as the IMAP server)
    $smtpServerAddress = "localhost";
    $smtpPort = 25;

//  This is displayed right after they log in
    $motd = "You are using SquirrelMail's web-based email client.  If you run into any bugs or have suggestions, please report them to our <A HREF=\"mailto:squirrelmail-list@sourceforge.net\">mailing list</A>";

//  Themes
//     You can define your own theme and put it in this directory.  You must
//     call it as the example below.  You can name the theme whatever you
//     want.  For an example of a theme, see the ones included in the config
//     directory.
//
//     You can download themes from http://squirrelmail.sourceforge.net/index.php3?page=10
//
//  To add a new theme to the options that users can choose from, just add
//  a new number to the array at the bottom, and follow the pattern.

    // The first one HAS to be here, and is your system's default theme.
    // It can be any theme you want
    $theme[0]["PATH"] = "../config/default_theme.php";
    $theme[0]["NAME"] = "Default";

    $theme[1]["PATH"] = "../config/sandstorm_theme.php";
    $theme[1]["NAME"] = "Sand Storm";

    $theme[2]["PATH"] = "../config/deepocean_theme.php";
    $theme[2]["NAME"] = "Deep Ocean";

    $theme[3]["PATH"] = "../config/slashdot_theme.php";
    $theme[3]["NAME"] = "Slashdot";

    $theme[4]["PATH"] = "../config/purple_theme.php";
    $theme[4]["NAME"] = "Purple";

    $theme[5]["PATH"] = "../config/forest_theme.php";
    $theme[5]["NAME"] = "Forest";

    $theme[6]["PATH"] = "../config/ice_theme.php";
    $theme[6]["NAME"] = "Ice";

//  Whether or not to use a special color for special folders.  If not, special
//  folders will be the same color as the other folders
    $use_special_folder_color = true;

/* The following are related to deleting messages.
 *   $move_to_trash
 *         - if this is set to "true", when "delete" is pressed, it will attempt
 *           to move the selected messages to the folder named $trash_folder.  If
 *           it's set to "false", we won't even attempt to move the messages, just
 *           delete them.
 *   $trash_folder
 *         - This is the path to the default trash folder.  For Cyrus IMAP, it
 *           would be "INBOX.Trash", but for UW it would be "Trash".  We need the
 *           full path name here.
 *   $auto_expunge
 *         - If this is true, when a message is moved or copied, the source mailbox
 *           will get expunged, removing all messages marked "Deleted".
 */

    $default_move_to_trash = true;
    $trash_folder = "INBOX.Trash";
    $auto_expunge = true;

//  Special Folders are folders that can't be manipulated like normal user created
//  folders can.  A couple of examples would be "INBOX.Trash", "INBOX.Drafts".  We have
//  them set to Netscape's default mailboxes, but this obviously can be changed.
//  To add one, just add a new number to the array.

    $special_folders[0] = "INBOX";   // The first one has to be the inbox (whatever the name is)
    $special_folders[1] = $trash_folder;
    $special_folders[2] = "INBOX.Sent";
    $special_folders[3] = "INBOX.Drafts";
    $special_folders[4] = "INBOX.Templates";

//  Whether or not to list the special folders first  (true/false)
    $list_special_folders_first = true;

//  Are all your folders subfolders of INBOX (i.e.  cyrus IMAP server)
//  If you are not sure, set it to false.
    $default_sub_of_inbox = true;

//  Some IMAP daemons (UW) handle folders weird.  They only allow a folder to contain
//  either messages or other folders, not both at the same time.  This option controls
//  whether or not to display an option during folder creation.  The option toggles
//  which type of folder it should be.
//
//  If this option confuses you, make it "true".  You can't hurt anything if it's true,
//  but some servers will respond weird if it's false.  (Cyrus works fine whether it's
//  true OR false).
    $show_contain_subfolders_option = false;

//  Whether or not to use META tags and automatically forward after an action has
//  been completed.
    $auto_forward = true;

//  Path to the data/ directory
//    It is a possible security hole to have a writable directory under the web server's
//    root directory (ex: /home/httpd/html).  For this reason, it is possible to put
//    the data directory anywhere you would like.   The path name can be absolute or
//    relative (to the config directory).  It doesn't matter.  Here are two examples:
//
//  Absolute:
//    $data_dir = "/usr/local/squirrelmail/data/";
//
//  Relative (to the config directory):
//    $data_dir = "../data/";

    $data_dir = "../data/";
?>

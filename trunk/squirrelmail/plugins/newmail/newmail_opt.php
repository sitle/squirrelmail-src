<?php
/**
 * newmails_opt.php - options page
 *
 * Copyright (c) 1999-2004 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * Displays all options relating to new mail sounds
 *
 * @version $Id$
 * @package plugins
 * @subpackage newmail
 */

/** @ignore */
define('SM_PATH','../../');

/* SquirrelMail required files. */
require_once(SM_PATH . 'include/validate.php');
/* sqm_baseuri function */
require_once(SM_PATH . 'functions/display_messages.php');

displayPageHeader($color, 'None');

$media_enable = getPref($data_dir,$username, 'newmail_enable', 'FALSE' );
$media_popup = getPref($data_dir, $username,'newmail_popup');
$media_allbox = getPref($data_dir,$username,'newmail_allbox');
$media_recent = getPref($data_dir,$username,'newmail_recent');
$media_changetitle = getPref($data_dir,$username,'newmail_changetitle');
$media = getPref($data_dir,$username,'newmail_media', '(none)');

// Set $allowsound to false if you don't want sound files available
$allowsound = "true";

echo html_tag( 'table', '', 'center', $color[0], 'width="95%" cellpadding="1" cellspacing="0" border="0"' ) . "\n" .
        html_tag( 'tr' ) . "\n" .
            html_tag( 'td', '', 'center' ) .
                '<b>' . _("Options") . ' - ' . _("New Mail Notification") . "</b><br />\n" .
                html_tag( 'table', '', '', '', 'width="100%" cellpadding="5" cellspacing="0" border="0"' ) . "\n" .
                    html_tag( 'tr' ) . "\n" .
                        html_tag( 'td', '', 'left', $color[4] ) . "<br />\n";

echo html_tag( 'p',
        sprintf(_("The %s option will check ALL of your folders for unseen mail, not just the inbox for notification."), '&quot;'._("Check all boxes, not just INBOX").'&quot;')
     ) . "\n" .
     html_tag( 'p',
        sprintf(_("Selecting the %s option will enable the showing of a popup window when unseen mail is in your folders (requires JavaScript)."), '&quot;'._("Show popup window on new mail").'&quot;')
     ) . "\n" .
     html_tag( 'p',
        sprintf(_("Use the %s option to only check for messages that are recent. Recent messages are those that have just recently showed up and have not been \"viewed\" or checked yet. This can prevent being continuously annoyed by sounds or popups for unseen mail."), '&quot;'._("Count only messages that are RECENT").'&quot;')
     ) . "\n" .
     html_tag( 'p',
        sprintf(_("Selecting the %s option will change the title in some browsers to let you know when you have new mail (requires JavaScript, and only works in IE but you won't see errors with other browsers). This will always tell you if you have new mail, even if you have %s enabled."), '&quot;'._("Change title on supported browsers").'&quot;', '&quot;'._("Count only messages that are RECENT").'&quot;')
     ) . "\n";
if ($allowsound == "true") {
    echo html_tag( 'p',
            sprintf(_("Select %s to turn on playing a media file when unseen mail is in your folders. When enabled, you can specify the media file to play in the provided file box."), '&quot;'._("Enable Media Playing").'&quot;')
         ) . "\n" .
         html_tag( 'p',
            sprintf(_("Select from the list of %s the media file to play when new mail arrives. If no file is specified, %s, no sound will be used."), '&quot;'._("Select server file").'&quot;', '&quot;'._("(none)").'&quot;')
         ) . "\n";
}

echo '</td></tr>' .
        html_tag( 'tr' ) .
            html_tag( 'td', '', 'center', $color[4] ) . "\n" . '<hr style="width: 25%; height: 1px;" />' . "\n";

echo '<form action="'.sqm_baseuri().'src/options.php" method="post">' . "\n" .
        html_tag( 'table', '', '', '', 'width="100%" cellpadding="5" cellspacing="0" border="0"' ) . "\n";

// Option: media_allbox
echo html_tag( 'tr' ) .
        html_tag( 'td', _("Check all boxes, not just INBOX").':', 'right', '', 'nowrap' ) .
            html_tag( 'td', '', 'left' ) .
                '<input type="checkbox" ';
if ($media_allbox == 'on') {
    echo 'checked="checked" ';
}
echo 'name="media_allbox" /></td></tr>' . "\n";

// Option: media_recent
echo html_tag( 'tr' ) .
        html_tag( 'td', _("Count only messages that are RECENT").':', 'right', '', 'nowrap' ) .
            html_tag( 'td', '', 'left' ) .
                '<input type="checkbox" ';
if ($media_recent == 'on') {
    echo 'checked="checked" ';
}
echo 'name="media_recent" /></td></tr>' . "\n";

// Option: media_changetitle
echo html_tag( 'tr' ) .
        html_tag( 'td', _("Change title on supported browsers").':', 'right', '', 'nowrap' ) .
            html_tag( 'td', '', 'left' ) .
                '<input type="checkbox" ';
if ($media_changetitle == 'on') {
    echo 'checked="checked" ';
}
echo 'name="media_changetitle" />&nbsp;('._("requires JavaScript to work").')</td></tr>' . "\n";

// Option: media_popup
echo html_tag( 'tr' ) .
        html_tag( 'td', _("Show popup window on new mail").':', 'right', '', 'nowrap' ) .
            html_tag( 'td', '', 'left' ) .
                '<input type="checkbox" ';
if($media_popup == 'on') {
    echo 'checked="checked" ';
}
echo 'name="media_popup" />&nbsp;('._("requires JavaScript to work").')</td></tr>' . "\n";

if ($allowsound == "true") {
// Option: media_enable
    echo html_tag( 'tr' ) .
            html_tag( 'td', _("Enable Media Playing").':', 'right', '', 'nowrap' ) .
                html_tag( 'td', '', 'left' ) .
                    '<input type="checkbox" ';
    if ($media_enable == 'on') {
        echo 'checked="checked" ';
    }
    echo 'name="media_enable" /></td></tr>' . "\n";

// Option: media_sel
    echo html_tag( 'tr' ) .
        html_tag( 'td', _("Select server file").':', 'right', '', 'nowrap' ) .
            html_tag( 'td', '', 'left' ) .
                '<select name="media_sel">' . "\n" .
                    '<option value="(none)"';
    if ( $media == '(none)') {
        echo 'selected="selected" ';
    }
    echo '>' . _("(none)") . '</option>' .  "\n";
    // Iterate sound files for options
    $d = dir(SM_PATH . 'plugins/newmail/sounds');
    while($entry=$d->read()) {
        $fname = get_location () . '/sounds/' . $entry;
        if ($entry != '..' && $entry != '.' && $entry != 'CVS') {
            echo '<option ';
            if ($fname == $media) {
                echo 'selected="selected" ';
            }
            echo 'value="' . htmlspecialchars($fname) . '">' .
                htmlspecialchars($entry) . "</option>\n";
        }
    }
    $d->close();
    $media_output = ($media == '(none)') ? _("(none)") : substr($media, strrpos($media, '/')+1);
    echo '</select>'.
        '<input type="submit" value="' . _("Try") . '" name="test" onClick="' .
            "window.open('testsound.php?sound='+media_sel.options[media_sel.selectedIndex].value, 'TestSound'," .
            "'width=150,height=30,scrollbars=no');" .
            'return false;' .
            '" /></td></tr>' .
            html_tag( 'tr', "\n" .
                html_tag( 'td', _("Current File:"), 'right', '', 'nowrap' ) .
                    html_tag( 'td', '<input type="hidden" value="' .
                        htmlspecialchars($media) . '" name="media_default" />' .
                        htmlspecialchars($media_output) . '', 'left' )
             ) . "\n";
}
echo html_tag( 'tr', "\n" .
    html_tag( 'td', '&nbsp;' ) .
        html_tag( 'td',
            '<input type="hidden" name="optmode" value="submit" />' .
            '<input type="hidden" name="optpage" value="newmail" />' .
            '<input type="submit" value="' . _("Submit") . '" name="submit_newmail" />',
        'left' )
     ) . "\n";
?>
</table></form></td></tr></table></td></tr></table></body></html>

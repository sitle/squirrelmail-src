<?php

/**
 * folders_rename_getname.php
 *
 * Copyright (c) 1999-2002 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * Gets folder names and enables renaming
 * Called from folders.php
 *
 * $Id$
 */

require_once('../src/validate.php');
require_once('../functions/imap.php');
require_once('../functions/display_messages.php');

/* get globals we may need */

$username = $_SESSION['username'];
$key = $_COOKIE['key'];
$delimiter = $_SESSION['delimiter'];
$onetimepad = $_SESSION['onetimepad'];

$old = $_POST['old'];
    
/* end of get globals */

displayPageHeader($color, 'None');

if ($old == '') {
    plain_error_message(_("You have not selected a folder to rename. Please do so.").
        "<BR><A HREF=\"../src/folders.php\">"._("Click here to go back")."</A>.", $color);
    exit;
}


if (substr($old, strlen($old) - strlen($delimiter)) == $delimiter) {
    $isfolder = TRUE;
    $old = substr($old, 0, strlen($old) - 1);
} else {
    $isfolder = FALSE;
}

$old = imap_utf7_decode_local($old);

if (strpos($old, $delimiter)) {
    $old_name = substr($old, strrpos($old, $delimiter)+1, strlen($old));
    $old_parent = substr($old, 0, strrpos($old, $delimiter));
} else {
    $old_name = $old;
    $old_parent = '';
}

echo "<br><TABLE align=center border=0 WIDTH=\"95%\" COLS=1>".
     "<TR><TD BGCOLOR=\"$color[0]\" ALIGN=CENTER><B>".
     _("Rename a folder").
     "</B></TD></TR>".
     "<TR><TD BGCOLOR=\"$color[4]\" ALIGN=CENTER>".
     "<FORM ACTION=\"folders_rename_do.php\" METHOD=\"POST\">\n".
     _("New name:").
     "<br><B>$old_parent $delimiter </B><INPUT TYPE=TEXT SIZE=25 NAME=new_name VALUE=\"$old_name\"><BR>\n";
if ( $isfolder ) {
    echo "<INPUT TYPE=HIDDEN NAME=isfolder VALUE=\"true\">";
}
printf("<INPUT TYPE=HIDDEN NAME=orig VALUE=\"%s\">\n", urlencode($old));
printf("<INPUT TYPE=HIDDEN NAME=old_name VALUE=\"%s\">\n", urlencode($old_name));
echo "<INPUT TYPE=SUBMIT VALUE=\""._("Submit")."\">\n".
     "</FORM><BR></TD></TR>".
     "</TABLE>";

?>

<?php
/*
 * Revised Header/Navigation Menu for SquirrelMail
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * $Id$
 */
?>
<!--DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"-->
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1;" />
<meta name="Description" content="Documentation, Plugins, Downloads for SquirrelMail"/>
<meta name="Author" content="SquirrelMail Project Team"/>
<title>SquirrelMail - <?php echo htmlspecialchars(
                            $page_title == NULL ? 
                                $object->title : $page_title ); ?></title>
<link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>
<div id="pagetitle">
<div id="usermenu">
<?php
    if ( isset($foowd->user->objectid) ) { 
        // If an objectid is set, we're logged in. 
        $url = getURI(array('objectid' => $foowd->user->objectid,'classid' => USER_CLASS_ID));
        echo '<a href="', $url, '">', $foowd->user->title, '</a> ';
        echo '( <a href="', getURI(array()), '?class=foowd_user&amp;method=logout">'. _('Logout') .'</a> )';
    } else { 
        // Otherwise, we're anonymous
        $url = getURI(array());
        echo 'Anonymous User [' . $foowd->user->title . '] ';
        echo '( <a href="', $url, '?class=foowd_user&amp;method=login">'. _('Login') .'</a> ';
        echo '| <a href="', $url, '?class=foowd_user&amp;method=create">'. _('Register') .'</a> )';
        unset($url);
    }
    echo '<br />'; // echo user workspace info here
?>
</div><!-- end usermenu -->
<?php
if ( $object != NULL ) {
    $url = getURI(array('objectid' => $object->objectid,
                        'classid' => $object->classid));

    echo '<a href="',$url,'">', $object->title, '</a>';
} else {
    echo $page_title;
}
// page workspace information
?>
</div><!-- end pagetitle -->
<div id="locationmenu">
<table border="0" cellspan="0" cellspacing="0" width="100%">
<tr><td align="left" class="subtext"><nobr>
<?php
    echo '<a href="', getURI(array()), '">Home</a> ';
?>
</nobr></td><td align="right" class="subtext"><nobr>
<?php
   echo '<a href="', getURI(array('object' => 'search')), '">Search</a> | ';
   echo '<a href="', getURI(array('object' => 'sqmuseradmin')), '">Users</a> | ';
   echo '<a href="', getURI(array('object' => 'sqmindex')), '">Index</a> ';
?>
</nobr></td></tr>
</table>
</div><!-- end locationmenu -->
<div id="content">

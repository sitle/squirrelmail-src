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
<table class="pagetitle" width="100%">
<tr>
  <td rowspan="2" width="275">&nbsp;</td>
  <td class="usermenu" valign="top">
<?php
    $translations = i18nManager::getDisplayInfo($foowd);
    foreach ( $translations as $id => $lang )
    {
      echo ' <a href="', getURI(array('objectid' => $id, 
                                     'classid' => WORKSPACE_CLASS_ID,
                                     'method' => 'enter')), '">';
      if ( isset($lang['icon']) )
        echo '<img src="', $lang['icon'], '" alt="', $lang['title'], '" border="0" />';
      else 
        echo $lang['title'];
      echo '</a>';
    }
    echo '<br />';
    $lang_url = '';
    if ( $foowd->user->workspaceid != 0 ) {
        $lang = i18nManager::getDisplayInfo($foowd, $foowd->user->workspaceid);
        if ( $lang != NULL )
        {
          $lang_url .= '<a href="';
          $lang_url .= getURI(array('objectid' => $foowd->user->workspaceid, 
                                    'classid' => WORKSPACE_CLASS_ID,
                                    'method' => 'enter'));
          $lang_url .= '">';
          if ( isset($lang['icon']) && $lang['icon'] != '')
            $lang_url .=  '<img src="' . $lang['icon'] . '" alt="' . $lang['title'] . '" />';
          else 
            $lang_url .=  $lang['title'];
          $lang_url .=  '</a> | ';
        }
    }
    if ( isset($foowd->user->objectid) ) { 
        // If an objectid is set, we're logged in. 
        $url = getURI(array('objectid' => $foowd->user->objectid, 'classid' => USER_CLASS_ID));
        echo '<a href="', $url, '">', $foowd->user->title, '</a> ';
        echo '( ', $lang_url, '<a href="', getURI(array()), '?class=foowd_user&amp;method=logout">'. _("Logout") .'</a> )';
    } else { 
        // Otherwise, we're anonymous
        $url = getURI(array());
        echo 'Anonymous User [' . $foowd->user->title . '] ';
        echo '( ', $lang_url, '<a href="', $url, '?class=foowd_user&amp;method=login">'. _("Login") .'</a> ';
        echo '| <a href="', $url, '?class=foowd_user&amp;method=create">'. _("Register") .'</a> )';
        unset($url);
    }
?>
  </td>
</tr>
<tr>
  <td class="titleblock" valign="bottom">
<?php
if ( $object != NULL && $page_title == NULL ) {
    $url = getURI(array('objectid' => $object->objectid,
                        'classid' => $object->classid));

    echo '<a href="',$url,'">', $object->title, '</a>';
} else {
    echo $page_title;
}
?>
  </td>
</tr>
</table><!-- end pagetitle -->

<div id="locationmenu">
<table border="0" cellspan="0" cellspacing="0" width="100%">
<tr><td align="left" class="subtext"><nobr>
<?php
    echo '<a href="', getURI(array()), '">', _("Home"), '</a> | ';
    echo '<a href="', getURI(array()), '">', _("Docs"), '</a> | ';
    echo '<a href="', getURI(array()), '">', _("Plugins"), '</a> | ';
    echo '<a href="', getURI(array()), '">', _("Support"), '</a> | ';
    echo '<a href="', getURI(array()), '">', _("Download"), '</a> | ';
    echo '<a href="', getURI(array('object' => 'faq')), '">',_("FAQ"),'</a>';
?>
</nobr></td><td class="subtext">&nbsp;</td><td align="right" class="subtext"><nobr>
<?php
   echo '<a href="', getURI(array('object' => 'search')), '">',_("Search"),'</a> | ';
   echo '<a href="', getURI(array('object' => 'sqmtools')), '">',_("Tools"),'</a> | ';
   echo '<a href="', getURI(array('object' => 'sqmindex')), '">',_("Index"),'</a> ';
?>
</nobr></td></tr>
</table>
</div><!-- end locationmenu -->
<div id="content">

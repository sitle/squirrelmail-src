<?php
/*
 * Copyright (c) 2003-2004 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file is an addition/modification to the 
 * Framework for Object Orientated Web Development (Foowd).
 */

/** 
 * Template for basic page layout. Other templates provide the 'content'
 * nested inside this template.
 *
 * Modified by SquirrelMail Development
 * $Id$
 * 
 * @package smdoc
 * @subpackage template
 */

/**
 * Begin with definitions of common values
 */
 
  $foowd =& $t['foowd'];
  $user =& $foowd->user;

  if ( isset($t['object']) )
  {
    $object =& $t['object'];
    $className = get_class($object);
  }
  else
  {
    $object = NULL;
    $className = isset($t['className']) ? $t['className'] : 'Unknown';
  }

  $method    = isset($t['method'])    ? $t['method'] : 'Unknown';
  $title     = isset($t['title'])     ? $t['title']: 'Unknown';
  $lastUpdate = '&nbsp;';

  if ( !sqGetGlobalVar('ok', $ok, SQ_SESSION) && isset($t['success']) )
    $ok = $t['success'];
  elseif ( !sqGetGlobalVar('error', $error,  SQ_SESSION) && isset($t['failure']) )
    $error = $t['failure'];

  getStatusStrings($ok, $error);

  // Clear ok/error values from session
  unset($_SESSION['ok']);
  unset($_SESSION['error']);

  if ( isset($t['classid']) && $object != NULL &&
       $t['classid'] != USER_CLASS_ID )
  {
    $version = isset($t['version']) ? 'v. ' . $t['version'] . ', ' : '';
    if ( $t['classid'] != ERROR_CLASS_ID )
    {
      $lastUpdate = ' [ '. $version . date('Y/m/d H:i T', $object->updated) . ' ] ';
      if ( isset($t['workspaceid']) && $t['workspaceid'] != 0 )
        $lastUpdate .= ' (' . smdoc_translation::getLink($foowd, $t['workspaceid']) . ')';
    }
  }

  if ( $method != 'view' )
    $lastUpdate = $method . $lastUpdate;

//  smdoc_translation::initialize($foowd);
  $flag_links = smdoc_translation::getLink($foowd);
  $loc_url = getURI();
  $user_url = $loc_url . '?class=smdoc_user';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1;" />
<meta name="Description" content="Documentation, Plugins, Downloads for SquirrelMail"/>
<meta name="Author" content="SquirrelMail Project Team"/>
<title>SquirrelMail<?php
    if ( isset($t['title']) )
    echo ' - ', htmlentities($t['title']);
?></title>
<link rel="stylesheet" type="text/css" href="templates/style.css" media="all" />
<link rel="stylesheet" type="text/css" href="templates/printer.css" media="print" />
</head>
<body>
<!-- begin page title -->
<div id="pagetitle">
<table width="100%" cellspacing="0" cellpadding="0">
  <tr>
    <td rowspan="3" class="skip">
      <a href="#start_content"><img src="templates/images/empty.png" alt="skip to content" /></a>
      <img src="templates/images/empty.png" alt="|" />
      <a href="#end_content"><img src="templates/images/empty.png" alt="skip to edit functions" /></a>
      <?php if ($foowd->debug) { ?>
          <img src="templates/images/empty.png" alt="|" />
          <a href="#debug"><img src="templates/images/empty.png" alt="skip to debug" /></a>
       <?php } ?>
    </td>
    <td class="usermenu" valign="top">
      <!-- Start with array of translation flags -->
        <?php echo implode(' ', $flag_links); ?>
        <br />
      <!-- User Login/Language/Workspace information -->
        <?php
          $lang_url = NULL;
          $links = array();
          $user_link = NULL;
          $tools = array();

          // Create list of links
          // Start with current translation link
          if ( $user->workspaceid != 0 )
            $links[] = smdoc_translation::getLink($foowd, $user->workspaceid);

          // Define link for user (or Anon), do we have login/register or logout links?
          if ( isset($user->objectid) )
          {
            $user_link = '<a href="'.$loc_url.'?classid='.USER_CLASS_ID.'&objectid='.$user->objectid.'">'.$user->title.'</a> ';
            $links[]  = '<a href="'.$user_url.'&method=logout">'. _("Logout") .'</a>';
            $tools[]  = '<a href="sqmtools.php">'._("Tools").'</a>';
          }
          else
          {
            $user_link = _("Anonymous");
            $links[] = '<a href="'.$user_url.'&method=login">'. _("Login") .'</a>';
            $links[] = '<a href="'.$user_url.'&method=create">'. _("Register") .'</a>';
          }
          $tools[] = '<a href="'.$user_url.'&method=list">'._("Users").'</a>';

          // Print username ( methodlist ) based on above
          echo $user_link . '&nbsp;';
          print_arr($links);

          // Miscellaneous links
          echo '<br />';
          print_arr($tools, FALSE);
        ?>
    </td>
  </tr>
  <tr>
    <td valign="bottom">
      <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
          <td class="titleblock">
      <?php
          if ( isset($t['classid']) && $t['objectid'] )
          {
            if ( $t['classid'] == USER_CLASS_ID )
                echo 'User Profile: ';
            echo '<a href="'.$loc_url.'?classid='.$t['classid'].'&objectid='.$t['objectid'].'">'. $title.'</a> ';
          }
          else
            echo $title;
      ?>
          </td>
          <td class="titleupdate"><?php echo $lastUpdate; ?></td>
        </tr>
      </table>
      <table width="100%" cellspacing="0" cellpadding="0" class="locationmenu">
        <tr>
          <td align="left" class="menu_subtext">
            <?php
          echo '<a href="', $loc_url, '">',                 _("Home"), '</a> | ';
          echo '<a href="', $loc_url, '?class=smdoc_news&method=list">',  _("News"), '</a> | ';
          echo '<a href="', $loc_url, '?object=docs">',     _("Documentation"), '</a> | ';
          echo '<a href="', $loc_url, '?object=download">', _("Download"), '</a> | ';
          echo '<a href="', $loc_url, '?object=plugins">',  _("Plugins"), '</a> | ';
          echo '<a href="', $loc_url, '?object=tracker">',  _("Tracker"), '</a>';          
            ?>
          </td>
          <td class="subtext">&nbsp;</td>
          <td align="right" class="menu_subtext">
            <?php
          echo '<a href="', $loc_url, '?object=faq">',      _("Help"), '</a> | ';
          echo '<a href="sqmsearch.php">', _("Search"),'</a> | ';
          echo '<a href="sqmindex.php">',_("Index"),'</a> ';
            ?>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</div>
<!-- end page title -->
<?php if( $ok != NULL ) { ?>
  <div id="status"><span class="ok"><?php echo $ok; ?></span></div>
<?php } elseif( $error != NULL ) { ?>
  <div id="status"><span class="error"><?php echo $error; ?></span></div>
<?php } ?>
<div class="nothere"><a id="start_content" name="start_content"><img src="templates/images/empty.png" alt="------------- begin content ----------------------------------------" /></a></div>
<!-- begin content -->
<div id="content">
<?php
  if ( isset($t['body']) )
    echo $t['body'];
  elseif ( isset($t['body_template']) )
  {
    /** If body template is specified, include it. */
    include_once(TEMPLATE_PATH . $t['body_template']);
  }
  elseif ( isset($t['body_function']) )
    $t['body_function']($foowd, $className, $method, $user, $object, $t);
  else
    echo '<p>This object did not provide a BODY to the template.</p>';
?>

</div>
<!-- end content -->
<div class="nothere"><a id="end_content" name="end_content"><img src="templates/images/empty.png" alt="------------- end content ------------------------------------------" /></a></div>
<!-- begin editmenu -->
<div id="editmenu">
<?php // Assemble links at bottom of page

      $edit_arr = array();

      if ( isset($t['classid']) &&                   // classid is defined
           $t['classid'] != ERROR_CLASS_ID )         // is not error page
      {
        $methods = get_class_methods($className);

        // Base part of page URI
        $uri_arr['objectid'] = $t['objectid'];
        $uri_arr['classid'] = $t['classid'];
        if ( isset($t['version']) )
          $uri_arr['version'] = $t['version'];
        $obj_uri = getURI($uri_arr);

        // For each method the user has permission to invoke, add a link
        foreach ($methods as $methodName)
        {
          if (substr($methodName, 0, 7) == 'method_' )
          {
            $methodName = substr($methodName, 7);
            // There are certain methods we don't want to show 
            if ( $methodName == 'revert' ||
                 $methodName == 'diff' )
              continue;

            if ($foowd->hasPermission($className,$methodName,'OBJECT', $object->permissions))
              $edit_arr[] = '<a href="'.$obj_uri.'&method='.$methodName.'">'.ucfirst($methodName).'</a>';
          }
        }
      } 
    
    // Last command (always displayed) - link to recent changes    
    $edit_arr[] = '<a href="sqmchanges.php">'._("Recent Changes")."</a>";
    print_arr($edit_arr, FALSE);
  ?>
</div>
<!-- end editmenu -->
<!-- begin footer -->
<div id="copyright">
  This site is copyright &copy; 1999-2003 by the SquirrelMail Project Team.
</div>
<div id="footer">
  Powered by <a href="http://sourceforge.net/projects/foowd/">
  Framework for Object Oriented Web Development</a>
  (v <?php echo $foowd->version ?>).
</div>
<!-- end footer -->
<?php
if ($foowd->debug)
  return;
?>
</body>
</html>

<?php
// vim: syntax=php

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

/** Location Menu Items */
$template_menu = array(
    'home' => array('name' => _("Home"),     'uri' => BASE_URL),
    'dld'  => array('name' => _("Download"), 'uri' => BASE_URL . '?object=download'),
    'plug' => array('name' => _("Plugins"),  'uri' => BASE_URL . '?object=plugins'),
    'trkr' => array('name' => _("Tracker"),  'uri' => BASE_URL . '?object=tracker'),
    'docs' => array('name' => _("Documentation"), 'uri' => BASE_URL . '?object=docs'),
    'sch'  => array('name' => _("Search"),   'uri' => 'sqmsearch.php'),
);

$tools = array(
    'news' => array('name' => _("News"), 'uri' => BASE_URL . '?class=smdoc_news&method=list'),
    'chg'  => array('name' => _("Changes"), 'uri' => 'sqmchanges.php'),
    'user' => array('name' => _("Users"), 'uri' => BASE_URL . '?class=smdoc_user&method=list'),
    'idx'  => array('name' => _("Index"), 'uri' => 'sqmindex.php'),
    'help' => array('name' => _("Help"),  'uri' => BASE_URL . '?object=faq'),
);

if ( isset($t['edit_links']) )
    $edit_arr =& $t['edit_links'];

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

// Last update ------------------------------------------------------------------------
if ( isset($t['classid']) && $object != NULL &&  
     $t['classid'] != USER_CLASS_ID && 
     $t['classid'] != ERROR_CLASS_ID )
{
  $version = isset($t['version']) ? 'v' . $t['version'] . ', ' : '';
  $lastUpdate = ' [ '. $version .'Updated: '. date('d F Y', $object->updated) . ' ] ';
  if ( isset($t['workspaceid']) && $t['workspaceid'] != 0 )
    $lastUpdate .= ' (' . smdoc_translation::getLink($foowd, $t['workspaceid']) . ')';
}

if ( $method != 'view' )
  $lastUpdate = $method . $lastUpdate;

// User menu / Translation links ---------------------------------------------------
//  smdoc_translation::initialize($foowd);
$flag_links = smdoc_translation::getLink($foowd);
$loc_url = getURI();
$user_url = $loc_url . '?class=smdoc_user';
$links = array();
$user_link = NULL;

// Create list of links
// Start with current translation link
if ( $user->workspaceid != 0 )
  $links[] = smdoc_translation::getLink($foowd, $user->workspaceid);

if ( isset($user->objectid) )
{
  $user_link = '<a href="'.$loc_url.'?classid='.USER_CLASS_ID.'&objectid='.$user->objectid.'">'.$user->title.'</a> ';
  $links[]  = array('name' => _("Logout"), 'uri' => $user_url.'&method=logout');
  $template_menu[]  = array('name' => _("Tools"), 'uri' => 'sqmtools.php', 'class' => 'special');
}
else
{
  $user_link = _("Anonymous") . ' ';
  $links[] = array('name' => _("Login"), 'uri' => $user_url.'&method=login');
  $links[] = array('name' => _("Register"), 'uri' => $user_url.'&method=create');
}

// Window/Page Title -----------------------------------------------------------------------
$page_title = $title;
if ( isset($t['classid']) && $t['objectid'] )
{
  $page_title = '<a href="'.$loc_url.'?classid='.$t['classid'].'&objectid='.$t['objectid'].'">'. $title.'</a> ';

  if ( $t['classid'] == USER_CLASS_ID )
    $page_title = 'User Profile: ' . $page_title;
}
$site_title = ( isset($t['title']) ) ? ' - ' . htmlentities($t['title']) : '';

// Edit menu items --------------------------------------------------------------------------
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
        $edit_arr[] = array('name' => ucfirst($methodName), 'uri' => $obj_uri.'&method='.$methodName);
    }
  }
}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1;" />
<meta name="Description" content="Documentation, Plugins, Downloads for SquirrelMail"/>
<meta name="Author" content="SquirrelMail Project Team"/>
<title>SquirrelMail<?php echo $site_title; ?></title>
<link rel="stylesheet" type="text/css" href="templates/layout.css" media="all" />
<link rel="stylesheet" type="text/css" href="templates/style.css" media="all" />
<link title="allblue" rel="stylesheet" type="text/css" href="templates/bluestyle.css" media="all" />
<link title="allblue" rel="stylesheet" type="text/css" href="templates/bluetables.css" media="all" />
<!--[if IE]>
<link rel="stylesheet" type="text/css" href="templates/layout-ie.css" />
<link title="allblue"  rel="stylesheet" type="text/css" href="templates/bluestyle-ie.css" />
<![endif]-->
<link rel="stylesheet" type="text/css" href="templates/printer.css" media="print" />
</head>
<body>

<!-- begin left side -->
<div id="flagline"><?php echo implode(' ', $flag_links); ?></div>
<div id="sitetitle"><a href="<?php echo BASE_URL; ?>">SquirrelMail</a></div>
<div id="subsitetitle">WebMail for Nuts</div>
<div id="locationmenu">
<?php foreach ($template_menu as $mi) {
        // If we have special user tools, use a 'special' class..  
        if ( isset($mi['class']) ) { ?>
  <span class="special">
<?php   } ?>
    <a href="<?php echo $mi['uri']; ?>"><?php echo $mi['name']; ?></a> 
<?php   if ( isset($mi['class']) ) { ?>
  </span>
<?php   }
      } ?>
</div>
<!-- end left side -->

<!-- begin user menu -->
<div id="usermenu">
    <div class="float-right">
<?php echo $user_link . ': ';  
      foreach ($links as $mi) { ?>
        <a href="<?php echo $mi['uri']; ?>"><?php echo $mi['name']; ?></a>        
<?php } ?>
    </div>
<?php foreach ($tools as $mi) { ?>
  <a href="<?php echo $mi['uri']; ?>"><?php echo $mi['name']; ?></a>
<?php } ?>
</div>
<!-- end user menu -->

<!-- begin content -->
<div id="content">

<!-- end edit menu with content update -->
<div id="editmenu">
  <div id="contentupdate"><?php echo $lastUpdate; ?></div>
  &nbsp;
<?php if ( !empty($edit_arr) ) { ?>
<?php   foreach ($edit_arr as $mi) { ?>
    <a href="<?php echo $mi['uri']; ?>"><?php echo $mi['name']; ?></a>
<?php   } 
      } ?>
</div>
<!-- end edit menu -->

<!-- begin site status -->
<div id="status">
<?php if( $ok != NULL ) { ?>
    <span class="ok"><?php echo $ok; ?></span>
<?php } elseif( $error != NULL ) { ?>
    <span class="error"><?php echo $error; ?></span>
<?php } ?>
    &nbsp;
</div>
<!-- end site status -->


<h1><?php echo $page_title; ?></h1>

<?php
  // Fetch body
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

<div id="copyright">&copy; 1999-2004 by the SquirrelMail Project Team.</div>

<?php // If debugging is off, write the EOF now... otherwise, debug will
      // fill it in after writing debug info.
      if ( !$foowd->debug ) { ?>
</body>
</html>
<?php }

<?php
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
       $t['classid'] != EXTERNAL_CLASS_ID && 
       $t['classid'] != USER_CLASS_ID )
  {
    $version = isset($t['version']) ? 'v. ' . $t['version'] . ', ' : ''; 
    $lastUpdate = ' [ '. $version . _("Last Update") . ': ' 
                  . date('Y/m/d, H:i T', $object->updated) . ' ] ';
    if ( isset($t['workspaceid']) && $t['workspaceid'] != 0 )
      $lastUpdate .= ' (' . getLink($foowd, $t['workspaceid']) . ')';
  }

  if ( $method != 'view' )
    $lastUpdate = $method . $lastUpdate;
  
//  smdoc_translation::initialize($foowd);
//  $flag_links = smdoc_translation::getLink($foowd);
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
    echo ' - ', htmlspecialchars($t['title']); 
?></title>
<link rel="stylesheet" type="text/css" href="templates/style.css" />
</head>
<body>
<!-- begin page title -->
<div id="pagetitle">
<table width="100%" cellspacing="0" cellpadding="0">
  <tr>
    <td rowspan="3" class="skip">
      <a href="#start_content"><img src="templates/images/empty.png" alt="skip to content" /></a>
      <img src="templates/images/empty.png" alt="|" />
      <a href="#end_content"><img src="templates/images/empty.png" alt="skip to admin functions" /></a>
      <?php if ($foowd->debug) { ?>
          <img src="templates/images/empty.png" alt="|" />
          <a href="#debug"><img src="templates/images/empty.png" alt="skip to debug" /></a>
       <?php } ?>
    </td>
    <td class="usermenu" valign="top">
      <!-- Start with array of translation flags -->
        <?php // echo implode(' ', $flag_links); ?>
        <br />
      <!-- User Login/Language/Workspace information -->
        <?php
          $lang_url = NULL;
          if ( $user->workspaceid != 0 )
          {
//            $lang_url = foowd_translation::getLink($user->workspaceid);
            if ( $lang_url != NULL )
              $lang_url .=  ' | ';
          }

          if ( isset($user->objectid) )
          {
            // If an objectid is set, we're logged in.
            echo '<a href="'.$loc_url.'?classid='.USER_CLASS_ID.'&objectid='.$user->objectid.'">' 
                 . $user->title . '</a> ';
            echo '( '. $lang_url
                 . '<a href="', $user_url, '&method=logout">'. _("Logout") .'</a> )';
          } else {
            // Otherwise, we're anonymous
            echo _("Anonymous User"), ' [' . $user->title . '] ';
            echo '( '. $lang_url  
                 . '<a href="', $user_url, '&method=login">'. _("Login") .'</a> ';
            echo '| <a href="', $user_url, '&method=create">'. _("Register") .'</a> )';
          }
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
            echo '<a href="'.$loc_url.'?classid='.USER_CLASS_ID.'&objectid='.$t['objectid'].'">'. $title.'</a> ';
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
          echo '<a href="', $loc_url, '">',            _("Home"), '</a> | ';
          echo '<a href="', $loc_url, '?object=faq">', _("Docs"), '</a> | ';
          echo '<a href="', $loc_url, '?object=faq">', _("Plugins"), '</a> | ';
          echo '<a href="', $loc_url, '?object=faq">', _("Support"), '</a> | ';
          echo '<a href="', $loc_url, '?object=faq">', _("Download"), '</a> | ';
          echo '<a href="', $loc_url, '?object=faq">', _("FAQ"),'</a>';
            ?>
          </td>
          <td class="subtext">&nbsp;</td>
          <td align="right" class="menu_subtext">
            <?php
          echo '<a href="', $loc_url, '?object=search">', _("Search"),'</a> | ';
          echo '<a href="', $loc_url, '?object=sqmuser">', _("Users"),'</a> | ';
          echo '<a href="', $loc_url, '?object=sqmindex">',_("Index"),'</a> ';
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
<!-- begin content -->
<div class="nothere"><a id="start_content" name="start_content"><img src="templates/images/empty.png" alt="------------- begin content ----------------------------------------" /></a></div>
<div id="content">
<?php
  if ( isset($t['body']) )
    echo $t['body'];
  elseif ( isset($t['body_template']) )
    include_once(TEMPLATE_PATH . $t['body_template']);
  elseif ( isset($t['body_function']) )
    $t['body_function']($foowd, $className, $method, $user, $object, $t);
  else
    echo '<p>This object did not provide a BODY to the template.</p>';
?>
<a id="end_content" name="end_content"><img src="templates/images/empty.png" alt="------------- end content ------------------------------------------" /></a>
</div>
<div id="editmenu">
  <?php
    if ( isset($t['classid']) &&                 // classid is defined
         $t['classid'] != EXTERNAL_CLASS_ID &&   // is not external object
         $t['classid'] != ERROR_CLASS_ID )         // is not error page
    {
        $methods = get_class_methods($className);

        $notfirst = 0;
        foreach ($methods as $methodName)
        {
            if (substr($methodName, 0, 7) == 'method_' )
            {
                $methodName = substr($methodName, 7);
                if ( $methodName == 'revert' ||
                     $methodName == 'diff' ||
                     $methodName == 'xml' ||
                     $methodName == 'permissions' ||
                     $methodName == 'raw' )
                    continue;

                if ($foowd->hasPermission($className,$methodName,'object',$object))
                {
                    if ( $notfirst ) echo ' | ';
                    $notfirst = 1;
                    $uri_arr['objectid'] = $t['objectid'];
                    $uri_arr['classid'] = $t['classid'];
                    if ( isset($t['version']) )
                      $uri_arr['version'] = $t['version'];
                    $uri_arr['method']  = $methodName;
                    echo '<a href="', getURI($uri_arr), '">', ucfirst($methodName), '</a>';
                }
            }
        }
        if ( $notfirst )
            echo ' | ';
        echo '<a href="', $loc_url, '?object=sqmchanges">',_("Recent Changes"),"</a><br />\n";
    } else { // object not set OR external object
        echo '<a href="', $loc_url, '?object=sqmchanges">',_("Recent Changes"),"</a><br />\n";
    }
  ?>
</div><!-- end editmenu -->
<div id="copyright">
  This site is copyright &copy; 1999-2003 by the SquirrelMail Project Team.
</div>
<div id="footer">
  Powered by <a href="http://sourceforge.net/projects/foowd/">
  Framework for Object Oriented Web Development</a>
  (v <?php echo $foowd->version ?>).
</div>
<?php
if ($foowd->debug) { // display debug data
  $foowd->debug->display($foowd);
}
?>
</body>
</html>

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

  if ( !sqGetGlobalVar('ok', $ok, SQ_FORM) && isset($t['success']) )
    $ok = $t['success'];
  elseif ( !sqGetGlobalVar('error', $error,  SQ_FORM) &&  isset($t['failure']) )
    $error = $t['failure'];

//  smdoc_translation::initialize($foowd);
//  $flag_links = smdoc_translation::getLink($foowd);
  $loc_url = getURI(array());
  $user_url = $loc_url . '?class='.USER_CLASS_NAME;

?>
<!--DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"-->
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
<table id="pagetitle" width="100%" cellspacing="0" cellpadding="0">
  <tr>
    <td rowspan="2" width="304" class="logo">
      <a href="#content"><img src="templates/images/empty.png" alt="skip to content" border="0" /></a>
      <?php if ($foowd->debug) { ?>
          <img src="templates/images/empty.png" alt="|" border="0" />
          <a href="#debug"><img src="templates/images/empty.png" alt="skip to debug" border="0" /></a>
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
            $url = getURI(array('objectid' => $user->objectid, 'classid' => USER_CLASS_ID));
            echo '<a href="' . $url . '">' . $user->title . '</a> ';
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
    <td class="titleblock" valign="bottom">
      <?php
          if ( isset($t['classid']) && $t['objectid'] )
          {
            if ( $t['classid'] == USER_CLASS_ID )
                echo 'User Profile: ';
            $obj_url = getURI( array('objectid' => $t['objectid'], 
                                     'classid'  => $t['classid']) );
            echo '<a href="', $obj_url.'">'. $title.'</a> ';
          }
          else
            echo $title;
      ?>
    </td>
  </tr>
</table>
<!-- end page title -->
<!-- begin location menu -->
<div id="locationmenu">
  <table width="100%" cellspacing="0" cellpadding="0" border="0">
    <tr>
      <td align="left" class="subtext">
        <nobr><?php
          echo '<a href="', $loc_url, '">',            _("Home"), '</a> | ';
          echo '<a href="', $loc_url, '?object=faq">', _("Docs"), '</a> | ';
          echo '<a href="', $loc_url, '?object=faq">', _("Plugins"), '</a> | ';
          echo '<a href="', $loc_url, '?object=faq">', _("Support"), '</a> | ';
          echo '<a href="', $loc_url, '?object=faq">', _("Download"), '</a> | ';
          echo '<a href="', $loc_url, '?object=faq">', _("FAQ"),'</a>';
        ?></nobr>
      </td>
      <td class="subtext">&nbsp;</td>
      <td align="right" class="subtext">
        <nobr><?php
          echo '<a href="', $loc_url, '?object=search">', _("Search"),'</a> | ';
          echo '<a href="', $loc_url, '?object=sqmindex">',_("Index"),'</a> ';
        ?></nobr>
      </td>
    </tr>
  </table>
</div>
<!-- end location menu -->
<?php if( $ok != NULL ) { ?>
  <div id="status"><span class="ok"><?php echo $ok; ?></span></div>
<?php } elseif( $error != NULL ) { ?>
  <div id="status"><span class="error"><?php echo $error; ?></span></div>
<?php } ?>
<!-- begin content -->
<a name="content"><img src="templates/images/empty.png" alt="------------- begin content ----------------------------------------" border="0" /></a>
<div id="content">
<img src="templates/images/empty.png" alt="<?php echo $title; ?>" border="0" />
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
</div>
<a name="footer"><img src="templates/images/empty.png" alt="------------- end content ------------------------------------------" border="0" /></a>
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

                if ($user->hasPermission($className,$methodName,'object',$object))
                {
                    if ( $notfirst ) echo ' | ';
                    $notfirst = 1;
                    echo '<a href="', getURI(array('objectid' => $t['objectid'],
                                                    'classid' => $t['classid'],
                                                    'version' => $t['version'],
                                                     'method' => $methodName)), '">', 
                          ucfirst($methodName), '</a>';
                }
            }
        }
        if ( $notfirst )
            echo ' | ';
        echo '<a href="', $loc_url, '?object=sqmchanges">',_("Recent Changes"),"</a><br />\n";
        echo _("Last Update");
        if ( $t['workspaceid'] != 0 )
            echo ' (', getLink($foowd, $t['workspaceid']), ')';

        echo  ': ', date('Y/m/d, H:i T', $object->updated);
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
?><a name="debug"><img src="templates/images/empty.png" alt="------------- debug ------------------------------------------" border="0" /></a><?php
    $foowd->debug->display($foowd);
}
?>
</body>
</html>

<!--DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"-->
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1;" />
<meta name="Description" content="Documentation, Plugins, Downloads for SquirrelMail"/>
<meta name="Author" content="SquirrelMail Project Team"/>
<title>SquirrelMail - <?php echo $t['title']; ?></title>
<link rel="stylesheet" type="text/css" href="templates/style.css" />
</head>
<?php
  $user =& $foowd->user;

  $status = new smdoc_input_form('status', NULL, NULL, NULL);
  $status->getFormValue('ok', $ok);
  $status->getFormValue('error', $error);
  if ( $ok == NULL && isset($t['success']) )
    $ok = $t['success'];
  elseif ( $error == NULL &&  isset($t['failure']) )
    $error = $t['failure'];

  if ( is_numeric($ok) )
    $ok = getConst($ok . '_MSG');
  if ( is_numeric($error) )
    $error = getConst($error . '_MSG');

  smdoc_translation::initialize($foowd);
  $flag_links = smdoc_translation::getLink($foowd);
  $loc_url = getURI(array());
  $user_url = $loc_url . '?class='.USER_CLASS_NAME;
?>
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
        <?php echo implode(' ', $flag_links); ?>
        <br />
      <!-- User Login/Language/Workspace information -->
        <?php
          $lang_url = NULL;
          if ( $user->workspaceid != 0 )
          {
            $lang_url = foowd_translation::getLink($user->workspaceid);
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
          if ( isset($object) )
          {
            if ( $object->classid == USER_CLASS_ID )
                echo 'User Profile: ';
            $obj_url = getURI(array('objectid' => $object->objectid, 'classid' => $object->classid));
            echo '<a href="', $obj_url.'">'. $t['title'].'</a> ';
          }
          else
            echo $t['title'];
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
          echo '<a href="', $loc_url, '?object=search">',   _("Search"),'</a> | ';
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
<a name="content"><img src="templates/images/empty.png" alt="------------- begin ----------------------------------------" border="0" /></a>
<div id="content">
<img src="templates/images/empty.png" alt="<?php echo $t['title']; ?>" border="0" />
<?php
  if ( isset($t['body']) )
    echo $t['body'];
  elseif ( isset($t['body_function']) )
  {
    ( isset($object) )    ? $myobject =& $object   : $myobject = NULL;
    ( isset($className) ) ? $myclass =& $className : $myclass = NULL;
    $t['body_function']($foowd, $myclass, $method, $user, $myobject, $t);
  }
  else
    echo '<p>This object did not provide a BODY to the template.</p>';
?>
</div>
<a name="footer"><img src="templates/images/empty.png" alt="------------- end ------------------------------------------" border="0" /></a>
<div id="editmenu">
  <?php
    if ( isset($object) &&                               // is object
         $object->classid != EXTERNAL_CLASS_ID &&        // is not external object
         $object->title != DEFAULT_ERROR_TITLE )         // is not error page
    {
        $className = get_class($object);
        $methods = get_class_methods($className);

        $notfirst = 0;
        foreach ($methods as $methodName)
        {
            if (substr($methodName, 0, 7) == 'method_' )
            {
                $methodName = substr($methodName, 7);
                if ( $methodName == 'diff' ||
                     $methodName == 'xml' ||
                     $methodName == 'permissions' ||
                     $methodName == 'raw' )
                    continue;

                if (isset($object->permissions[$methodName]))
                    $methodPermission = $object->permissions[$methodName];
                else
                    $methodPermission = getPermission($className, $methodName, 'object');

                if ($foowd->user->inGroup($methodPermission, $object->creatorid))
                {
                    if ( $notfirst )
                      echo ' | ';
                    $notfirst = 1;
                    echo '<a href="', getURI(array('objectid' => $object->objectid,
                                                     'classid' => $object->classid,
                                                     'version' => $object->version,
                                                     'method' => $methodName)), '">', 
                          ucfirst($methodName), '</a>';
                }
            }
        }
        if ( $notfirst )
            echo ' | ';
        echo '<a href="', $loc_url, '?object=sqmchanges">',_("Recent Changes"),"</a><br />\n";
        echo _("Last Update");
        if ( $object->workspaceid != 0 )
            echo ' (', getLink($foowd, $object->workspaceid), ')';

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
  (v <?php echo VERSION ?>).
</div>
<?php
if ($foowd->debug) { // display debug data
?><a name="debug"><img src="templates/images/empty.png" alt="------------- debug ------------------------------------------" border="0" /></a><?php
    $foowd->debug->display($foowd);
}
?>
</body>
</html>

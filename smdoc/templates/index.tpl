<!--DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"-->
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1;" />
<meta name="Description" content="Documentation, Plugins, Downloads for SquirrelMail"/>
<meta name="Author" content="SquirrelMail Project Team"/>
<title>SquirrelMail - <?php echo $template['PAGE_TITLE']; ?></title>
<link rel="stylesheet" type="text/css" href="style.css" />
</head>
<?php
  $foowd =& $template['FOOWD_OBJECT'];
  $user =& $template['CURRENT_USER'];
  $object =& $template['CURRENT_OBJECT'];

  foowd_translation::initialize($foowd, TRUE);
  $flag_links = foowd_translation::getLink($foowd);
?>
<body>
<!-- begin page title -->
<table id="pagetitle" width="100%" cellspacing="0" cellpadding="0">
  <tr>
    <td rowspan="2" width="304" class="logo">&nbsp;</td>
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
                 . '<a href="', $loc_url, '?class=foowd_user&method=logout">'. _("Logout") .'</a> )';
          } else {
            // Otherwise, we're anonymous
            echo _("Anonymous User"), ' [' . $user->title . '] ';
            echo '( '. $lang_url  
                 . '<a href="', $loc_url, '?class=foowd_user&method=login">'. _("Login") .'</a> ';
            echo '| <a href="', $loc_url, '?class=foowd_user&method=create">'. _("Register") .'</a> )';
          }
        ?>
    </td>
  </tr>
  <tr>
    <td class="titleblock" valign="bottom">
      <?php
          if ( isset($user->objectid) )
            echo $template['PAGE_TITLE_URL'];
          else
            echo $template['PAGE_TITLE'];
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
        <nobr><?php $loc_url = getURI(array());
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
<?php if( isset($template['STATUS_OK']) ) { ?>
  <div id="status">
    <span class="ok"><?php echo $template['STATUS_OK']; ?></span>
  </div>
<?php } elseif( isset($template['STATUS_ERROR']) ) { ?>
  <div id="status">
    <span class="error"><?php echo $template['STATUS_ERROR']; ?></span>
  </div>
<?php } ?>
<!-- begin content -->
<div id="content">
<?php echo $template['BODY'];  ?>
</div>
<!-- end content -->
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
  Powered by <a href="http://foowd.peej.co.uk/">
  Framework for Object Oriented Web Development</a>
  (v <?php echo VERSION ?>).
</div><?php if ( isset($template['DEBUG']) ) { echo $template['DEBUG']; }  ?>
</body>
</html>

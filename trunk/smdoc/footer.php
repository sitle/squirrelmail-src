<?php
/*
 * Revised Footer/Edit Menu for SquirrelMail
 * Copyright (c) 1999-2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * $Id$
 */
?>

</div> <!-- end of content -->

<div id="editmenu">
<?php
    if ( isset($object) &&                               // is object
         $object->classid != EXTERNAL_CLASS_ID &&        // is not external object
         $object->title != DEFAULT_ERROR_TITLE ) {       // is not error page
        
        $className = get_class($object);
        $methods = get_class_methods($className);
        
        $notfirst = 0;
        foreach ($methods as $methodName) {
            if (substr($methodName, 0, 7) == 'method_' ) {
                
                $methodName = substr($methodName, 7);
                
                if ( $methodName == 'diff' || 
                     $methodName == 'xml' ||
                     $methodName == 'permissions' ||
                     $methodName == 'raw' ) 
                    continue;
                    
                if (isset($object->permissions[$methodName])) {
                    $methodPermission = $object->permissions[$methodName];
                } else {
                    $methodPermission = getPermission($className, $methodName, 'object');
                }
                if ($foowd->user->inGroup($methodPermission, $object->creatorid)) { // check user permission
                    if ( $notfirst )
                        echo ' | ';
                    $notfirst = 1;
                    echo '<a href="', getURI(array('objectid' => $object->objectid, 
                                                     'classid' => $object->classid, 
                                                     'version' => $object->version, 
                                                     'method' => $methodName)), '">', ucfirst($methodName), '</a>';
                }
            }
        }
        if ( $notfirst )
            echo ' | ';
        echo '<a href="', getURI(array('object' => 'sqmchanges')), '">',
             _("Recent Changes"), '</a>';
        echo "<br />\n";
        echo _("Last Update");
        if ( $object->workspaceid != 0 ) {
            $workspace = $foowd->fetchObject( array(
                                               'objectid' => $object->workspaceid,
                                               'classid' => WORKSPACE_CLASS_ID));
            echo ' (';
            if ( isset($workspace->language_icon) && $workspace->language_icon != '')
                echo '<img src="', $workspace->language_icon, '" alt="', $workspace->title, '" />';
            else 
                echo $workspace->title;
            echo ')';
            unset($workspace);
        }
        echo  ': ', date('Y/m/d, H:i T', $object->updated);
    } else { // object not set OR external object
        echo '<a href="', getURI(array('object' => 'sqmchanges')), '">',
             _("Recent Changes"), '</a><br />';
    }
?> 
</div>
<div id="copyright">
This site is copyright &copy; 1999-2003 by the SquirrelMail Project Team.</div>

<div id="footer">
Powered by <a href="http://foowd.peej.co.uk/">
Framework for Object Oriented Web Development</a> 
(v <?php echo VERSION ?>).
</div>
</body>
</html>

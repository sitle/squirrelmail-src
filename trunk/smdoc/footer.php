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
    if (isset($object) ) { // is object but isn't error object
        if ( $object->classid == EXTERNAL_CLASS_ID ) {
            // External resources have truncated Edit Menu (uneditable)
            echo '<a href="',  getURI(array('object' => 'recentchanges')),
                 '">Recent Changes</a><br />';
            echo 'This Page last updated on ';
            echo date( "F d Y H:i", $object->updated);
            if ( $object->version != 0 )
                echo '(v',$object->version,')';
            echo ".\n";
        } else {
            foreach (get_class_methods(get_class($object)) as $methodName) {
                if ( substr($methodName, 0, 7) == 'method_' ) {
                    $methodName = substr($methodName, 7);
                    if ( hasPermission($foowd->user, $object, $methodName) ) {
                        echo '<a href="', getURI(array('objectid' => $object->objectid, 
                                                       'classid' => $object->classid, 
                                                       'version' => $object->version, 
                                                       'method' => $methodName)), 
                         '">', ucfirst($methodName), '</a> | ';
                    }
                }
            }
            echo '<a href="',  getURI(array('object' => 'recentchanges')),
                 '">Recent Changes</a><br />';
            echo 'Last Update: ' , date( "F d, Y H:i", $object->updated);
            if ( $object->version != 0 )
                echo ' <span class="xsmall">(v ',$object->version,')</span>';
            echo ".\n";
		}
    } else { // object not set.
        echo '<a href="', getURI(array('object' => 'Recent Changes')),
             '">Recent Changes</a><br />';
        echo '<br />';
    }
?> 
</div>
<div id="copyright">
This site is copyright &copy; 1999-2003 by the SquirrelMail Project Team.<br />
<?php echo _("If you have any questions, please visit our") ?>
 <a href="<?php echo getURI(array('object' => 'FAQ')); ?>">FAQ</a>.
</div>

<div id="footer">
Powered by <a href="http://foowd.peej.co.uk/">
Framework for Object Oriented Web Development</a> 
(v <?php echo VERSION ?>).
</div>
</body>
</html>

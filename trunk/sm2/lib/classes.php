<?php
/**
 * classes.php
 *
 * Copyright (c) 2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * Classes utility functions.
 *
 *
 * $Id$
 */
 
// TODO
//include(SM_LIB.'dependencies.inc');

/* stuff for dependencies.inc */
define('SM_LIB','./');
define('SM_SRV','../service/');
define('SM_FNC','../functions/');



// static list where we store dependencies of each class
// key: classname value: required files that need to be included first
$dependencies = array(
        'Messages'     => array('Service','mailboxtree'),
        'Service'      => array('tree'),
        'Services'     => array('Tree'),
        'tree'         => array('Object', 'acl'),
        'mailboxtree'  => array('tree'),
        'imap_backend' => array('auth','parser')
        );
// static list locations so we can move around the files during devel stage very easy
$locations = array(
        'Object' => SM_LIB.'object.class.php',
        'Messages' => SM_SRV.'message.inc.php',
        'Service' => SM_LIB.'service.class.php',
        'Services' => SM_LIB.'Servives.class.php',
        'tree' => SM_LIB. 'tree.class.php',
        'mailboxtree' => SM_LIB .'mailbox/MailboxTree.class.php',
        'imap_backend' => SM_SRV. 'imap/imap_backend.class.php',
        'parser' => SM_LIB.'parser.class.php',
        'auth' => SM_LIB. 'auth.php',
        'acl' => SM_LIB . 'acl.class.php',
        // temp location for testing purposes
        'config' => SM_LIB .'config.php'
        );

// end dependecy.inc stuff

// array to keep track of already included files. Should be stored in the session
$include_once = array();

/**
 * @func      sm_include
 * @decr      include file and check dependencies
 * @param     str        $classname    The class name
 * @return    bool                     success
 * @access    public
 * @author(s) Marc Groot Koerkamp
 */
function sm_include($classname) {
    global $include_once, $dependencies, $locations;
    if (!isset($include_once[$classname])) {    // check if already included
        if (isset($dependencies[$classname])) { // check for dependencies
            foreach ($dependencies[$classname] as $name) {
                if (!isset($include_once[$name])) { // check if already included
                        sm_include($name); // call function recursive to make sure we start
                                           // at the bottom with our dependency include
                }
            }
        }
        //echo "{$locations[$classname]}<BR>";
        include($locations[$classname]);  // dependencies are included now include the file
        $include_once[$classname] = true; // keep track of included files
    }
    return true;
}

?>

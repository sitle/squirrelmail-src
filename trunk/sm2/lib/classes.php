<?php
/**
 * classes.php
 *
 * Copyright (c) 2003 Marc Groot Koerkamp 
 * Copyright (c) 2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * Classes utility functions.
 *
 * Author: Marc Groot Koerkamp (Sourceforce username: stekkel) 2003
 *
 * $Id$
 */
 
// TODO
//include(SM_LIB.'dependencies.inc');

/* stuff for dependencies.inc */

define('SM_LIB','./');
define('SM_SRV','../service/');
define('SM_FNC','../functions/');

$dependencies = array(
        'Messages'   => array('Service'),
        'Service'    => array('tree'),
        'Services'   => array('Tree'),
        'tree'       => array('Object'),
        'imap_backend' => array('auth','parser')
        );

$locations = array(
        'Object' => SM_LIB.'object.class.php',
        'Messages' => SM_SRV.'message.inc.php',
        'Service' => SM_LIB.'service.class.php',
        'Services' => SM_LIB.'Servives.class.php',
        'tree' => SM_LIB. 'tree.class.php',
        'imap_backend' => SM_SRV. 'imap/imap_backend.class.php',
        'parser' => SM_LIB.'parser.class.php',
        'auth' => SM_FNC. 'auth.php'
       );
                
$include_once = array();


/* experimental, I'm not satisfied with this */
function sm_new($classname) {
    global $include_once;
    if (!isset($include_once[$classname])) {
        sm_include($classname);
    }
    /* damn this is ugly */
    $args = func_get_args();
    switch (func_num_args())
    {
    case '1': $new =& new $classname(); break;
    case '2': $new =& new $classname($args[1]); break;
    case '3': $new =& new $classname($args[1],$args[2]); break;
    case '4': $new =& new $classname($args[1],$args[2],$args[3]); break;
    case '5': $new =& new $classname($args[1],$args[2],$args[3],$args[4]); break;
    case '6': $new =& new $classname($args[1],$args[2],$args[3],$args[4],$args[5]); break;
    case '7': $new =& new $classname($args[1],$args[2],$args[3],$args[4],$args[5],$args[6]); break;
    case '8': $new =& new $classname($args[1],$args[2],$args[3],$args[4],$args[5],$args[6],$args[7]); break;
    }    
    return $new;            
}

function sm_include($classname) {
    global $include_once, $dependencies, $locations;
    if (!isset($include_once[$classname])) {
        if (isset($dependencies[$classname])) {
            foreach ($dependencies[$classname] as $name) {
                if (!isset($include_once[$name])) {
                        sm_include($name);
                }
            }
        }
    }
    echo "include: ". $classname . " location: ". $locations[$classname]."<BR>";
    include($locations[$classname]);
    $include_once[$classname] = true;
}

?>

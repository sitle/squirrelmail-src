<?php

/**
 *  Zookeeper
 *  Copyright (c) 2001 Paul Joseph Thompson
 *  Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 *  $Id$
 **/
 
function zkload_conf($zookeeper_home) {
    /* Require a session to have already been started. */
    if (session_id() == '') {
        return (false);
    }

    /* Load the zookeeper configuration classes. */
    require_once("$zookeeper_home/lib/conf/service.php");
    return (true);
}

?>

<?php

/**
 *  Zookeeper
 *  Copyright (c) 2001 Paul Joseph Thompson
 *  Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 *  $Id$
 **/
 
/* Main zookeeper logging load function. */
function zkload_logging($zookeeper_home) {
    /* Require a session to have already been started. */
    if (session_id() == '') {
        return (false);
    }

    /* Load the zookeeper logging classes. */
    require_once("$zookeeper_home/services/logging/service.php");
    return (true);
}

?>

<?php

/**
 *  Zookeeper
 *  Copyright (c) 2001 Paul Joseph Thompson
 *  Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 *  $Id$
 **/
 
function zkload_contacts($zookeeper_home) {
    /* Require a session to have already been started. */
    if (session_id() == '') {
        return (false);
    }

    /* Load the zookeeper contacts classes. */
    require_once("$zookeeper_home/services/contacts/service.php");
    return (true);
}

?>

<?php

/**
 *  Zookeeper
 *  Copyright (c) 2001 Paul Joseph Thompson
 *  Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 *  $Id$
 **/

function zkload_auth($zookeeper_home) {

    require_once("$zookeeper_home/services/auth/service.php");
    return( TRUE );
}

?>

<?php

/**
 *  Zookeeper
 *  Copyright (c) 2001 Paul Joseph Thompson
 *  Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 *  $Id$
 **/

function zkload_auth($zookeeper_home) {
    /* Require a session to have already been started. */
    // $ret = ( session_id() <> '' );
    // if( $ret ) {
       /* Load the zookeeper authentication classes. */
       require_once("$zookeeper_home/services/auth/service.php");
// echo '<b>Requiring ' . "$zookeeper_home/lib/auth/service.php<br></b>";
    // }
    // return( $ret );
    return( TRUE );
}

?>
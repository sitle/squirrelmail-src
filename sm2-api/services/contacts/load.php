<?php

    /**
     *  Zookeeper
     *  Copyright (c) 2001 Partridge
     *  Licensed under the GNU GPL. For full terms see the file COPYING.
     *
     *  $Id$
     **/
    
    function zkload_contacts( &$zkld, $svcname ) {

        require_once( $zkld->libhome . '/' . $svcname . '/service.php' );
        return( TRUE );

    }

?>

<?php

    /**
     *  Zookeeper
     *  Copyright (c) 2001 Paul Joseph Thompson
     *  Licensed under the GNU GPL. For full terms see the file COPYING.
     *
     *  $Id$
     **/
     
    function zkload_messages($zookeeper_home) {
        /* Load the zookeeper message classes. */
        require_once("$zookeeper_home/lib/messages/service.php");
        return (true);
    }

?>

<?php

/*
 * Squirrelmail2 API
 * Copyright (c) 2001 Th Squirrelmail Foundation
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * $Id$
 */

/**
 * ZkLoader
 *
 * The ZkLoader class allows the resources provides by Zookeeper to
 * be loaded by an external application.
 */
class ZkLoader {
    var $appname;
    var $zookeeper_home;
    var $libhome;
    var $modhome;

    /**
     * Create a new ZkLoader object.
     *
     * @param string $appname name of the calling application
     * @param string $zookeeper_home home directory for zookeeper
     */
    function implementor_auth($appname, $zookeeper_home) {
        $this->appname = $appname;
        $this->zookeeper_home = $zookeeper_home;
        $this->libhome = "$zookeeper_home/lib";
        $this->modhome = "$zookeeper_home/modules";
    }

    /**
     * Check a 
     *
     * @param string $username username with which to authenticate
     * @param string $password password with which to authenticate
     * @return bool indicates correct or incorrect password
     */
    function loadService($service, $provider, $options) {
        switch ($service) {
            case 'auth':
                require_once("$this->libhome/auth/load.php");
                require_once("$this->modhome/auth/ZkAuthImp_$provider.php");
        }
    }
}

?>

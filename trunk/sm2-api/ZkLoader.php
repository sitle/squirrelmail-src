<?php

/*
 * Zookeeper: ZkLoader.php
 * Copyright (c) 2001-2002 The Zookeeper Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * $Id$
 */

/**
 * ZkLoader
 *
 * The ZkLoader class allows the services provides by Zookeeper to
 * be loaded by an application. It, in essence, serves as a dynamic
 * library loader for the Zookeeper libraries.
 */
class ZkLoader {
    var $appname;
    var $zkhome;
    var $svchome;
    var $modhome;

    /**
     * Create a new ZkLoader object.
     *
     * @param string $appname name of the calling application
     * @param string $zookeeper_home home directory for zookeeper
     */
    function ZkLoader($appname, $zkhome) {
        $this->appname = $appname;
        $this->zkhome = $zkhome;
        $this->svchome = "$zkhome/services";
        $this->modhome = "$zkhome/modules";

        /* Load the core Zookeeper constants and functions files. */
        require_once("$zkhome/ZkConstants.php");
        require_once("$zkhome/ZkFunctions.php");
    }

    /**
     * Attempt to load the requested service.
     *
     * @param string $svcname
     * @param string $options
     * @param string $modname
     *
     * @return bool/string
     */
    function &loadService($svcname, $options = array(), $modname = '') {
        $svcclass = "ZkSvc_$svcname";
        $svcfile  = "$this->svchome/$svcname/service.php";
        $result   = true;

        /**
         * Check the service name, and then load it.
         * For now, keep these two checks seperate because later we want
         * to do a nicer job of error handling here then just "false".
         */
        if (!zkCheckName($svcname)) {
            $result = false;
        } else if (!file_exists($svcfile)) {
            $result = false;
        } else {
            require_once($svcfile);

            /* Make a new service object for the given service. */
            $code_mksvc = "\$svc = new $svcclass(\$options);";
            eval($code_mksvc);

            /* If required, load a module for this service. */
            if ($modname != '') {
                $result = $this->loadModule($svc, $options, $modname);
            }
        }

        /* Check our result and return false or the new service. */
        return ($result ? $svc : false);
    }

    /**
     * Load a new module for a service.
     *
     * @param object $svc     service to which to add the new module
     * @param array  $options array of options for the module
     * @param string $modname module name
     *
     * @return bool TRUE if succesfully loaded or FALSE if not
     */
    function loadModule(&$service, $options, $modname) {
        $modclass = "ZkMod_$svcname_$modname";
        $modfile  = "$this->modhome/$svcname/$modname.php";
        $result   = true;

        /**
         * Check the module name, and then load it.
         * For now, keep these two checks seperate because later we want
         * to do a nicer job of error handling here then just "false".
         */
        if (!zkCheckName($modname)) {
            $result = false;
        } else if (!file_exists($modfile)) {
            $result = false;
        } else {
            require_once($modfile);

            /* Make a new module object for the given module. */
            $code_mkmod = "\$mod = new $modclass(\$options);";
            eval($code_mkmod);

            /* Load the newly created module into this service. */
            $svc->loadModule($mod, $options);
        }

        /* Return our result. */
        return ($result);
    }
}

?>

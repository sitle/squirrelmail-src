<?php

/*
 * Zookeeper
 * Copyright (c) 2001 Paul Joseph Thompson
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * $Id$
 */

/**
 * ZkLoader
 *
 * The ZkLoader class allows the resources provides by Zookeeper to be
 * loaded by an external application.
 */
class ZkLoader {
    var $appname;
    var $zkhome;
    var $libhome;
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
        $this->libhome = "$zkhome/lib";
        $this->modhome = "$zkhome/modules";
        
        /* Load the core Zookeeper constants and functions files. */
        require_once("$zkhome/ZkConstants.php");
        require_once("$zkhome/ZkFunctions.php");
    }

    /**
     * Attempt to load the requested service.
     *
     * @param string $username username with which to authenticate
     * @param string $password password with which to authenticate
     * @return bool indicates correct or incorrect password
     */
    function &loadService($svcname, $options, $modname = '') {
        $svcfile  = "$this->libhome/$svcname/load.php";
        $svcfunc  = "zkload_$svcname";
        $svcclass = "ZkSvc_$svcname";
        
        /* Do some checks on the service name, then load the service file. */
        if (!zkCheckName($svcname)) {
            return (false);
        } else if (!file_exists($svcfile)) {
            return (false);
        } else {
            require_once($svcfile);
            $code_preload = "\$svcfile_result = $svcfunc('$this->zkhome');";
            
            /* Run the preload code string. */
            eval($code_preload);
            
            /* Evaluate the result. */
            if (!$svcfile_result) {
                return (false);
            }
        }
        
        /* Run the service load code string. */
        $code_loadservice = "\$service = new $svcclass(\$options);";
        eval($code_loadservice);
        
        /* Check if we need to load a module for this service. */
        if ($modname != '') {
            $this->loadModule($service, $options, $modname);
        }    
        
        /* Return the newly created Zookeeper service. */
        return ($service);
    }

    /**
     * Load a new module for a service.
     *
     * @param object $service service to which to add the new module
     * @param array  $options array of options for the module
     */
    function loadModule(&$service, $options, $modname) {
        $svcname = $service->getServiceName();
        $modfile  = "$this->modhome/$svcname/$modname.php";
        $modclass = "ZkMod_$svcname" . "_$modname";
        
        /* Do some checks on the module name, then load the module file. */
        if (!zkCheckName($modname)) {
            return (false);
        } else if (!file_exists($modfile)) {
            return (false);
        } else {
            require_once($modfile);
        }

        /* Run the module load code string. */
        $code_loadmodule = "\$module = new $modclass(\$options);";
        eval($code_loadmodule);
        
        echo "<tt>";
        echo "svcname = $svcname<br>";
        echo "modname = $modname<br>";
        echo "modfile = $modfile<br>";
        echo "modclass = $modclass<br>";
        echo "</tt>";
        
        /* Load the newly created module into this service. */
        $service->loadModule($module, $options);
    }
}

?>

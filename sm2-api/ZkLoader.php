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
        
        require_once("$zkhome/ZkFunctions.php");
    }

    /**
     * Attempt to load the requested service.
     *
     * @param string $username username with which to authenticate
     * @param string $password password with which to authenticate
     * @return bool indicates correct or incorrect password
     */
    function loadService($svcname, $options, $modname = '') {
        $svcfile  = "$this->libhome/$svcname/load.php";
        $svcfunc  = "zkload_$svcname";
        $svcclass = "ZkSvc_$svcname";
        $modfile  = "$this->modhome/$svcname/$modname.php";
        $modclass = "ZkMod_$svcname" . "_$modname";
        
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
        
        /* Begin creation of the service load code string. */
        $code_loadservice = "\$service = new $svcclass(\$options";
        
        /* Check if we need to load a module for this service. */
        if ($modname != '') {
            /* Do some checks on the module name, then load the module file. */
            if (!zkCheckName($modname)) {
                return (false);
            } else if (!file_exists($modfile)) {
                return (false);
            } else {
                require_once($modfile);
            }

            /* Continue building the service and module load code strings. */
            $code_loadmodule = "\$module = new $modclass(\$options);";
            $code_loadservice .= ', $module';
        }
        
        /* Finish up the service load code string. */
        $code_loadservice .= ');';
        
        /* Run the service and module load code strings. */
        eval($code_loadmodule);
        eval($code_loadservice);
        
        /* Return the newly created Zookeeper service. */
        return ($service);
    }
}

?>

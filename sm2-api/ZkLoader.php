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
     * @param string $svcname
     * @param string $options
     * @param string $modname
     *
     * @return bool/string
     */
    function &loadService($svcname, $options, $modname = '') {
        $svcfile  = "$this->libhome/$svcname/load.php";
        $svcfunc  = "zkload_$svcname";
        $svcclass = "ZkSvc_$svcname";
        $ret = FALSE;

        /* Do some checks on the service name, then load the service file. */
        if( zkCheckName($svcname) && file_exists($svcfile) ) {
            require_once($svcfile);
            $code_preload = "\$svcfile_result = $svcfunc('$this->zkhome');";
            
            /* Run the preload code string. */
            eval($code_preload);

            /* Evaluate the result. */
            if ($svcfile_result) {
                
                /* Run the service load code string. */
                $code_loadservice = "\$service = new $svcclass(\$options);";
                eval($code_loadservice);
                
                /* Check if we need to load a module for this service. */
                if ($modname != '') {
                    $this->loadModule($service, $options, $modname);
                }
                $ret = $service;
            }
        }
        /* Return the newly created Zookeeper service. */
        return ($ret);
    }

    /**
     * Load a new module for a service.
     *
     * @param object $service service to which to add the new module
     * @param array  $options array of options for the module
     * @param string $modname module name
     *
     * @return bool TRUE if succesfully loaded or FALSE if not
     */
    function loadModule(&$service, $options, $modname) {

        $svcname = $service->getServiceName();
        $modfile  = "$this->modhome/$svcname/$modname.php";
        $modclass = "ZkMod_$svcname" . "_$modname";
        $ret = FALSE;

        /* Do some checks on the module name, then load the module file. */
        if( zkCheckName($modname) && file_exists($modfile) ) {
            require_once($modfile);
            $ret = TRUE;
            /* Run the module load code string. */
            $code_loadmodule = "\$module = new $modclass(\$options);";
            eval($code_loadmodule);

            echo '<tt>' .
                 "svcname = $svcname<br>" .
                 "modname = $modname<br>" .
                 "modfile = $modfile<br>" .
                 "modclass = $modclass<br>" .
                 '</tt>';

            /* Load the newly created module into this service. */
            $service->loadModule($module, $options);
        }
        return( $ret );
    }
}

?>
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

    var $name = 'zkLoader';
    var $ver = '$Id$';

    var $bag_reg;   // Sessionized properties from all the services

    var $appname;
    var $zkhome;
    var $libhome;
    var $modhome;

    /** CONSTRUCTOR
     * Create a new ZkLoader object.
     *
     * @param string $appname name of the calling application
     * @param string $zookeeper_home home directory for zookeeper
     */
    function ZkLoader($appname, $zkhome) {

        global $bag_reg;

        session_start();
        session_register( 'bag_reg' );

        if( !is_array( $bag_reg ) )
            $bag_reg = array( 'zkLoader' => array( 'foo' => 'one' ) );

        $this->bag_reg = $bag_reg;

        $this->appname = $appname;
        $this->zkhome = $zkhome;
        $this->libhome = "$zkhome/services";
        $this->modhome = "$zkhome/modules";

        /* Load the core Zookeeper constants and functions files. */
        require_once("$zkhome/ZkConstants.php");
        require_once("$zkhome/ZkFunctions.php");
    }

    /**
     * Fills the session bag.
     *
     */
    function Register() {

        global $bag_reg;

        $bag_reg = $this->bag_reg;

    }

    function RequireCode( $svcname, $options = array(), $modname = '' ) {

        $svcfile  = "$this->libhome/$svcname/load.php";
        $srvfile  = "$this->libhome/$svcname/service.php";
        $svcfunc  = "zkload_$svcname";
        $modfile  = "$this->modhome/$svcname/$modname.php";

        if ( zkCheckName( $svcname ) ) {
            if ( file_exists( $svcfile ) ) {
                // There is a preload code
                require_once( $svcfile );
                $code_preload = "\$ret = $svcfunc( &\$this, '$svcname' );";
                /* Run the preload code string. */
                eval( $code_preload );
            } elseif ( file_exists( $srvfile ) ) {
                // This is a service without preloading
                require_once( $srvfile );
                $ret = TRUE;
            } else {
                $ret = FALSE;
            }
            if( $ret && zkCheckName($modname) && file_exists($modfile) ) {
                require_once($modfile);
            }
        } else
            $ret = FALSE;

        return( $ret );

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
    function &loadService($svcname, $options = array(), $modname = '', $serial = '' ) {

        if( $serial == '' )
            $serial = zkSS();

        $svcclass = "ZkSvc_$svcname";
        if( $this->RequireCode($svcname, $options, $modname ) ) {
            /* Run the service load code string. */
            $code_loadservice = "\$service = new $svcclass( \$options, \$this, \$serial );";
            eval($code_loadservice);

            /* Check if we need to load a module for this service. */
            if ($modname != '') {
                $this->loadModule($service, $options, $modname);
            }
            $ret = $service;
        } else
            $ret = FALSE;
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
        $modclass = "ZkMod_$svcname" . "_$modname";

        /* Run the module load code string. */
        $code_loadmodule = "\$module = new $modclass(\$options,\$service);";
        eval($code_loadmodule);
        /* Load the newly created module into this service. */
        $service->loadModule($module, $options);

        return( TRUE );
    }
}

?>
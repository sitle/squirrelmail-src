<?php

/*
 * Squirrelmail2 API
 * Copyright (c) 2001 PM Squirrelmail Foundation
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * $Id$
 */

/**
 * ZkSvc_db
 *
 * The ZkSvc_db class manages database handling
 */
class ZkSvc_db {

    var $name = 'db';
    var $ver = '$Id$';

    var $serial;
    var $bagname;

    var $zkld;
    var $mod;

    var $host;
    var $port;
    var $socket;
    var $db;
    var $username;
    var $password;

    var $lk;

    var $info;
    var $connected;

    /** CONSTRUCTOR
     * Create a new ZkSvc_db with the given module.
     *
     * @param object $module module to use for db handler
     * @param array  $options options to pass 
     */
    function ZkSvc_db( $options, &$zkld, $serial ) {

        $this->serial = $serial;
        $this->zkld = &$zkld;

        $this->bag_name = $this->name . '_' . $this->serial;

        // Defaulted properties
        if( $options['host'] == '' )
            $this->host = 'localhost';
        else
            $this->host = $options['host'];
        
        // Simple properties
        $this->port = $options['port'];
        $this->socket = $options['socket'];
        $this->db = $options['db'];
        $this->connected = FALSE;
    }

    /**
     * Return the name of this service.
     *
     * @return string the name of this service
     */
    function getServiceName() {
        return( $this->name );
    }

    /**
     * Replace the Zookeeper authentication module loaded for this service.
     *
     * @param object $module module to load for this authentication service
     */
    function loadModule( &$mod, $options ) {
        $this->mod =&$mod;
    }
    
    function close() {
        if( $this->connected ) {
            $this->mod->close();
            $this->connected = FALSE;
        }
    }
    
    function connect() {
         
        if( !$this->connected ) {
         
            $host = $this->host;
            
            if( $this->port <> '' )
                $host .= ':' . $this->port;
    
            if( $this->socket <> '' )
                $host .= ':' . $this->socket;
        
            if ( $ret = $this->mod->connect( $host, $this->username, $this->password ) && 
                 $this->db <> '' ) {
                $this->mod->select_db( $this->db );
            }
            
            if( $ret )
                $this->connected = TRUE;
            else 
                $this->connected = FALSE;
        }
        
        return( $this->connected );
    }
    
    function select_db( $db ) {
        $this->mod->select_db( $db );
    }

    function query( $sql ){
   
        return( $this->mod->query( $sql ) );
    
    }

    function html_table( $rs, $tt ) {

        return( $this->mod->html_table( $rs, $tt ) );
    
    }

    function getRow( $rs ) {
    
        return( $this->mod->getRow( $rs ) );
        
    }

}

?>